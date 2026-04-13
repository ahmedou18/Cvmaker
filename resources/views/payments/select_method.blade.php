<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.choose_payment_method') ?? 'Choose Payment Method' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .gradient-text {
            background: linear-gradient(to left, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        }
        .option-card {
            transition: all 0.3s ease;
        }
        .option-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
        }
        .option-card.selected {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex items-center justify-center px-4 py-12">

    <div class="w-full max-w-2xl">
        <!-- Back Button -->
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-blue-600 mb-6 transition">
            <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="font-semibold">{{ __('messages.back') ?? 'Back' }}</span>
        </a>

        <!-- Main Card -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <!-- Plan Header -->
            <div class="gradient-bg text-white text-center py-8 px-6">
                <h2 class="text-3xl font-extrabold mb-2">{{ $plan->name }}</h2>
                <div class="text-5xl font-extrabold">
                    {{ rtrim(rtrim(number_format($plan->price, 2), '0'), '.') }}
                    <span class="text-xl font-medium opacity-80">{{ __('messages.currency') ?? 'DZD' }}</span>
                </div>
            </div>

            <!-- Payment Options -->
            <div class="p-8">
                <h3 class="text-xl font-bold text-center mb-6 text-gray-900">
                    {{ __('messages.choose_payment_method') ?? 'اختر طريقة الدفع' }}
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Option 1: Online Payment (Moosyl) -->
                    <div class="option-card bg-white rounded-2xl border-2 border-gray-200 p-6 text-center cursor-pointer"
                         onclick="document.getElementById('online-form').submit();">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl gradient-bg flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">
                            {{ __('messages.online_payment') ?? 'الدفع الإلكتروني' }}
                        </h4>
                        <p class="text-sm text-gray-500 mb-4">
                            {{ __('messages.online_payment_desc') ?? 'بطاقة بنكية / Moosyl' }}
                        </p>
                        <form id="online-form" action="{{ route('payment.online', $plan->slug) }}" method="POST" class="hidden">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        </form>
                        <button class="gradient-bg text-white font-bold py-3 px-6 rounded-xl w-full hover:opacity-90 transition">
                            {{ __('messages.pay_now') ?? 'ادفع الآن' }}
                        </button>
                    </div>

                    <!-- Option 2: Manual Transfer -->
                    <div class="option-card bg-white rounded-2xl border-2 border-gray-200 p-6 text-center cursor-pointer"
                         onclick="document.getElementById('manual-section').classList.toggle('hidden'); this.classList.toggle('selected');">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">
                            {{ __('messages.manual_transfer') ?? 'تحويل يدوي' }}
                        </h4>
                        <p class="text-sm text-gray-500 mb-4">
                            {{ __('messages.manual_transfer_desc') ?? 'BaridiMob / Bankily / BIM' }}
                        </p>
                        <button class="bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold py-3 px-6 rounded-xl w-full hover:opacity-90 transition">
                            {{ __('messages.choose_manual') ?? 'اختر هذا الخيار' }}
                        </button>
                    </div>
                </div>

                <!-- Manual Payment Form (hidden by default) -->
                <div id="manual-section" class="hidden mt-8 pt-6 border-t border-gray-200">
                    <h4 class="text-lg font-bold text-gray-900 mb-4 text-center">
                        {{ __('messages.manual_payment_details') ?? 'تفاصيل الدفع اليدوي' }}
                    </h4>

                    <!-- Payment Methods Info -->
                    <div class="bg-blue-50 rounded-xl p-4 mb-6 text-sm text-gray-700 space-y-2">
                        <p class="font-bold">{{ __('messages.send_to') ?? 'أرسل المبلغ إلى أحد الحسابات التالية:' }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="bg-white rounded-lg p-3 text-center">
                                <span class="font-bold text-blue-600">BaridiMob</span>
                                <p class="text-gray-500 mt-1">007999990000000000</p>
                            </div>
                            <div class="bg-white rounded-lg p-3 text-center">
                                <span class="font-bold text-orange-600">Bankily</span>
                                <p class="text-gray-500 mt-1">007999990000000000</p>
                            </div>
                            <div class="bg-white rounded-lg p-3 text-center">
                                <span class="font-bold text-purple-600">BIM Bank</span>
                                <p class="text-gray-500 mt-1">007999990000000000</p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form action="{{ route('payment.manual') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                        <!-- Payment Method Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('messages.payment_method_used') ?? 'طريقة الدفع المستخدمة' }}
                            </label>
                            <select name="payment_method" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800">
                                <option value="baridimob">BaridiMob</option>
                                <option value="bankily">Bankily</option>
                                <option value="bim">BIM Bank</option>
                            </select>
                        </div>

                        <!-- Screenshot Upload -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('messages.upload_screenshot') ?? 'صورة التحويل' }}
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-500 transition cursor-pointer"
                                 onclick="document.getElementById('screenshot-input').click();">
                                <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm text-gray-500" id="upload-text">
                                    {{ __('messages.click_to_upload') ?? 'اضغط لرفع صورة التحويل' }}
                                </p>
                                <input type="file" id="screenshot-input" name="screenshot" accept="image/*" required
                                       class="hidden" onchange="handleFileSelect(this);">
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit"
                                class="gradient-bg text-white font-bold py-3 px-6 rounded-xl w-full hover:opacity-90 transition text-lg">
                            {{ __('messages.submit_manual') ?? 'إرسال الطلب' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                document.getElementById('upload-text').innerHTML =
                    '<span class="text-green-600 font-semibold">✓</span> ' + fileName;
            }
        }
    </script>
</body>
</html>
