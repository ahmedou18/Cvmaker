<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.payment_confirm_title', [], app()->getLocale()) }} - CVmaker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f9fafb;
        }
        /* تصميم بسيط ونظيف */
        .payment-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.2s ease;
        }
        .payment-card:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        }
        .method-card input:checked + div {
            border-color: #3b82f6;
            background-color: #eff6ff;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .upload-area {
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .upload-area:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }
        .btn-submit {
            background-color: #1f2937;
            transition: background-color 0.2s ease;
        }
        .btn-submit:hover {
            background-color: #111827;
        }
        .back-link {
            transition: color 0.2s ease;
        }
    </style>
</head>
<body class="text-gray-800 min-h-screen flex items-center justify-center px-4 py-12">

    <div class="w-full max-w-2xl">
        {{-- رابط العودة --}}
        <a href="{{ url()->previous() }}" class="back-link inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 mb-6 transition group">
            <svg class="w-5 h-5 rtl:rotate-180 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="font-medium text-sm">{{ __('messages.back_to_plans', [], app()->getLocale()) }}</span>
        </a>

        {{-- بطاقة الدفع --}}
        <div class="payment-card overflow-hidden">
            {{-- رأس البطاقة (معلومات الخطة) --}}
            <div class="bg-white px-8 py-8 border-b border-gray-100 text-center">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ __('messages.activate_subscription', [], app()->getLocale()) }}</p>
                <h2 class="text-2xl font-bold text-gray-900">{{ $plan->name }}</h2>
                <div class="mt-4 inline-flex items-baseline gap-1 bg-gray-50 px-5 py-2 rounded-full">
                    <span class="text-3xl font-black text-gray-900">{{ number_format($plan->price, 0) }}</span>
                    <span class="text-sm font-semibold text-gray-500">{{ __('messages.currency', [], app()->getLocale()) }}</span>
                </div>
            </div>

            {{-- محتوى الدفع --}}
            <div class="p-8 pt-6">
                {{-- معلومات التحويل الموحد --}}
                <div class="bg-gray-50 rounded-xl p-5 mb-8 text-center border border-gray-100">
                    <p class="text-gray-700 font-bold mb-2 text-base">{{ __('messages.unified_transfer_number', [], app()->getLocale()) }}</p>
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-3xl font-black text-gray-800 tracking-tighter">26121732</span>
                        <span class="bg-gray-200 text-gray-600 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ __('messages.unified', [], app()->getLocale()) }}</span>
                    </div>
                    <p class="text-gray-500 text-xs mt-2">{{ __('messages.send_amount_then_upload', [], app()->getLocale()) }}</p>
                </div>

                <form action="{{ route('payment.manual') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                    {{-- اختيار خدمة الدفع --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 text-center">{{ __('messages.choose_payment_service', [], app()->getLocale()) }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="method-card cursor-pointer">
                                <input type="radio" name="payment_method" value="bankily" class="hidden" required checked>
                                <div class="border border-gray-200 rounded-xl p-3 text-center transition-all hover:border-gray-300">
                                    <span class="block font-semibold text-gray-700 text-sm">Bankily</span>
                                </div>
                            </label>
                            <label class="method-card cursor-pointer">
                                <input type="radio" name="payment_method" value="click" class="hidden">
                                <div class="border border-gray-200 rounded-xl p-3 text-center transition-all hover:border-gray-300">
                                    <span class="block font-semibold text-gray-700 text-sm">Click</span>
                                </div>
                            </label>
                            <label class="method-card cursor-pointer">
                                <input type="radio" name="payment_method" value="bimbank" class="hidden">
                                <div class="border border-gray-200 rounded-xl p-3 text-center transition-all hover:border-gray-300">
                                    <span class="block font-semibold text-gray-700 text-sm">BIM Bank</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- رفع صورة التحويل --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">{{ __('messages.screenshot_label', [], app()->getLocale()) }}</label>
                        <div class="relative group">
                            <input type="file" name="screenshot" id="screenshot-input" accept="image/*" required
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                   onchange="updateFileName(this)">
                            <div class="upload-area border-2 border-dashed border-gray-200 rounded-xl p-6 text-center bg-white transition-all">
                                <div class="bg-gray-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600" id="file-name">{{ __('messages.click_to_upload', [], app()->getLocale()) }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ __('messages.upload_max_size', [], app()->getLocale()) }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- زر الإرسال --}}
                    <button type="submit" class="btn-submit w-full text-white font-bold py-3 rounded-xl text-base transition mt-4">
                        {{ __('messages.submit_for_activation', [], app()->getLocale()) }}
                    </button>
                </form>
            </div>
        </div>

        {{-- ملاحظة زمن التفعيل --}}
        <p class="text-center text-gray-400 text-xs mt-6">
            {{ __('messages.activation_time_note', [], app()->getLocale()) }}
        </p>
    </div>

    <script>
        function updateFileName(input) {
            const label = document.getElementById('file-name');
            if (input.files && input.files[0]) {
                label.innerHTML = `<span class="text-green-600 font-medium">✓ {{ __('messages.file_selected', [], app()->getLocale()) }}: ${input.files[0].name}</span>`;
            } else {
                label.innerHTML = `{{ __('messages.click_to_upload', [], app()->getLocale()) }}`;
            }
        }
    </script>
</body>
</html>