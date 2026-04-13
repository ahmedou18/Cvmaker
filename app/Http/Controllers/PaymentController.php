<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Handle checkout - redirects to payment method selection
     */
    public function checkout(Request $request, $slug)
    {
        Log::info('Checkout hit', ['slug' => $slug, 'payload' => $request->all()]);
        $request->validate(['plan_id' => 'required|exists:plans,id']);
        $plan = Plan::where('slug', $slug)->firstOrFail();

        try {
            // Show payment method selection view (Online via Moosyl or Manual)
            return view('payments.select_method', compact('plan'));
        } catch (\Exception $e) {
            Log::error('Checkout error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Process online payment via Moosyl API
     */
    public function processOnlinePayment(Request $request, $slug)
    {
        $request->validate(['plan_id' => 'required|exists:plans,id']);
        $plan = Plan::where('slug', $slug)->firstOrFail();

        // 1. Create unique transaction reference
        $reference = 'msl_' . Str::random(10);

        // 2. Save payment request in database as "Pending"
        $payment = Payment::create([
            'user_id'               => auth()->id(),
            'plan_id'               => $plan->id,
            'transaction_reference' => $reference,
            'amount'                => $plan->price,
            'status'                => 'pending',
            'payment_method'        => 'moosyl',
        ]);

        // 3. Request payment from Moosyl via API
        try {
            $response = Http::withHeaders([
                'Authorization' => env('MOOSYL_SECRET_KEY'),
                'Content-Type'  => 'application/json',
            ])->post('https://api.moosyl.com/payment-request', [
                'amount'        => $plan->price,
                'transactionId' => $reference,
            ]);

            if ($response->successful()) {
                $moosylId = $response->json('transactionId');
                $publicKey = env('MOOSYL_PUBLISHABLE_KEY');

                // 4. Redirect to Moosyl checkout page
                return redirect("https://checkout.moosyl.com/{$moosylId}?pk={$publicKey}");
            }

            Log::error('Moosyl API error: ' . $response->body());
            return back()->with('error', 'فشل الاتصال بموسيل: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Moosyl API exception: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ تقني: ' . $e->getMessage());
        }
    }

    /**
     * Handle Moosyl webhook notifications
     */
    public function handleWebhook(Request $request)
    {
        // Verify the notification is for successful payment
        if ($request->header('x-webhook-event') === 'payment-created') {
            $data = $request->input('data');
            $reference = $data['id']; // Reference we sent earlier

            // Find the payment and update its status
            $payment = Payment::where('transaction_reference', $reference)->first();

            if ($payment && $payment->status === 'pending') {
                $payment->update([
                    'status' => 'completed',
                    'moosyl_transaction_id' => $reference
                ]);

                // Activate plan features for the user here
                // Example: $payment->user->activatePlan($payment->plan);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle manual payment (bank transfer with screenshot upload)
     */
    public function manualPayment(Request $request)
    {
        $request->validate([
        'plan_id' => 'required|exists:plans,id',
      'payment_method' => 'required|in:bankily,masrivi,click,bimbank', // تحديد الخيارات بدقة
    'screenshot' => 'required|image|max:2048',
        ], [
            'plan_id.required' => 'يرجى اختيار الباقة',
            'plan_id.exists' => 'الباقة المحددة غير صالحة',
            'payment_method.required' => 'يرجى اختيار طريقة الدفع',
            'screenshot.required' => 'يرجى رفع صورة التحويل',
            'screenshot.image' => 'يجب أن يكون الملف صورة',
            'screenshot.max' => 'يجب ألا تتجاوز الصورة 2 ميجابايت',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $path = $request->file('screenshot')->store('payments', 'public');

        Payment::create([
            'user_id'               => auth()->id(),
            'plan_id'               => $plan->id,
            'transaction_reference' => 'manual_' . Str::random(10),
            'amount'                => $plan->price,
            'payment_method'        => $request->payment_method,
            'screenshot_path'       => $path,
            'status'                => 'pending_manual',
        ]);

        return redirect()->route('dashboard')->with('success', 'تم رفع الطلب بنجاح، بانتظار المراجعة.');
    }
}
