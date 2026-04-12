<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function checkout($slug)
    {
        // 1. العثور على الباقة أو إظهار خطأ 404
        $plan = Plan::where('slug', $slug)->firstOrFail();
        
        // 2. إنشاء مرجع فريد للعملية (سنستخدمه للتعرف على الدفع لاحقاً)
        $reference = 'msl_' . Str::random(10);

        // 3. حفظ الطلب في قاعدة البيانات بحالة "Pending" (قيد الانتظار)
        // هذا يضمن أننا لن نفقد أثر العملية أبداً
        $payment = Payment::create([
            'user_id'               => auth()->id(),
            'plan_id'               => $plan->id,
            'transaction_reference' => $reference,
            'amount'                => $plan->price,
            'status'                => 'pending',
        ]);

        // 4. طلب الدفع من Moosyl عبر API
        try {
            $response = Http::withHeaders([
                'Authorization' => env('MOOSYL_SECRET_KEY'),
                'Content-Type'  => 'application/json',
            ])->post('https://api.moosyl.com/payment-request', [
                'amount'        => $plan->price,
                'transactionId' => $reference, // نرسل المرجع الذي حفظناه في قاعدتنا
            ]);

            if ($response->successful()) {
                $moosylId = $response->json('transactionId');
                $publicKey = env('MOOSYL_PUBLISHABLE_KEY');

                // 5. التوجيه لصفحة الدفع في Moosyl
                return redirect("https://checkout.moosyl.com/{$moosylId}?pk={$publicKey}");
            }
            
            return back()->with('error', 'فشل الاتصال بموسيل: ' . $response->body());

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ تقني: ' . $e->getMessage());
        }
    }

    public function handleWebhook(Request $request)
{
    // التأكد من أن الإشعار يخص نجاح الدفع
    if ($request->header('x-webhook-event') === 'payment-created') {
        $data = $request->input('data');
        $reference = $data['id']; // المرجع الذي أرسلناه سابقاً

        // البحث عن الدفعة وتحديث حالتها
        $payment = Payment::where('transaction_reference', $reference)->first();

        if ($payment && $payment->status === 'pending') {
            $payment->update(['status' => 'completed']);
            
            // هنا تضع الكود الخاص بتفعيل ميزات الباقة للمستخدم
            // مثال: $payment->user->activatePlan($payment->plan);
        }
    }

    return response()->json(['status' => 'success']);
}
}