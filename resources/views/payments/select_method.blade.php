<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.payment_confirm_title', [], app()->getLocale()) }} - CVmaker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #3b82f6, #8b5cf6); }
        .method-card input:checked + div { border-color: #3b82f6; background-color: #eff6ff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1); }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex items-center justify-center px-4 py-12">

    <div class="w-full max-w-2xl">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-blue-600 mb-6 transition group">
            <svg class="w-5 h-5 rtl:rotate-180 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="font-bold">{{ __('messages.back_to_plans', [], app()->getLocale()) }}</span>
        </a>

        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
            <div class="gradient-bg text-white text-center py-10 px-6">
                <p class="text-blue-100 text-sm font-bold mb-2 uppercase tracking-widest">{{ __('messages.activate_subscription', [], app()->getLocale()) }}</p>
                <h2 class="text-3xl font-extrabold mb-4">{{ $plan->name }}</h2>
                <div class="inline-block bg-white/20 backdrop-blur-md rounded-2xl px-6 py-3">
                    <span class="text-4xl font-black">{{ number_format($plan->price, 0) }}</span>
                    <span class="text-lg font-bold opacity-90">{{ __('messages.currency', [], app()->getLocale()) }}</span>
                </div>
            </div>

            <div class="p-8">
                <div class="bg-blue-50 rounded-2xl p-6 mb-8 border border-blue-100 text-center">
                    <p class="text-blue-800 font-bold mb-2 text-lg">{{ __('messages.unified_transfer_number', [], app()->getLocale()) }}</p>
                    <div class="flex items-center justify-center gap-3">
                        <span class="text-4xl font-black text-blue-600 tracking-tighter">26121732</span>
                        <div class="bg-blue-600 text-white text-[10px] px-2 py-1 rounded-md uppercase font-bold">{{ __('messages.unified', [], app()->getLocale()) }}</div>
                    </div>
                    <p class="text-blue-600/70 text-sm mt-2 font-semibold">{{ __('messages.send_amount_then_upload', [], app()->getLocale()) }}</p>
                </div>

                <form action="{{ route('payment.manual') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-4 text-center">{{ __('messages.choose_payment_service', [], app()->getLocale()) }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="method-card cursor-pointer">
                                <input type="radio" name="payment_method" value="bankily" class="hidden" required checked>
                                <div class="border-2 border-gray-100 rounded-xl p-4 text-center transition-all hover:border-blue-200">
                                    <span class="block font-bold text-gray-800">Bankily</span>
                                </div>
                            </label>
                            <label class="method-card cursor-pointer">
                                <input type="radio" name="payment_method" value="masrivi" class="hidden">
                                <div class="border-2 border-gray-100 rounded-xl p-4 text-center transition-all hover:border-blue-200">
                                    <span class="block font-bold text-gray-800">Masrivi</span>
                                </div>
                            </label>
                            <label class="method-card cursor-pointer">
                                <input type="radio" name="payment_method" value="click" class="hidden">
                                <div class="border-2 border-gray-100 rounded-xl p-4 text-center transition-all hover:border-blue-200">
                                    <span class="block font-bold text-gray-800">Click</span>
                                </div>
                            </label>
                            <label class="method-card cursor-pointer">
                                <input type="radio" name="payment_method" value="bimbank" class="hidden">
                                <div class="border-2 border-gray-100 rounded-xl p-4 text-center transition-all hover:border-blue-200">
                                    <span class="block font-bold text-gray-800">BIM Bank</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4">
                        <label class="block text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide">{{ __('messages.screenshot_label', [], app()->getLocale()) }}</label>
                        <div class="relative group">
                            <input type="file" name="screenshot" id="screenshot-input" accept="image/*" required
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                   onchange="updateFileName(this)">
                            <div class="border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center group-hover:border-blue-400 transition-colors bg-gray-50">
                                <div class="bg-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm">
                                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-gray-600" id="file-name">{{ __('messages.click_to_upload', [], app()->getLocale()) }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ __('messages.upload_max_size', [], app()->getLocale()) }}</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full gradient-bg text-white font-black py-4 rounded-2xl shadow-lg shadow-blue-200 hover:scale-[1.01] transition-transform text-lg mt-4">
                        {{ __('messages.submit_for_activation', [], app()->getLocale()) }}
                    </button>
                </form>
            </div>
        </div>
        
        <p class="text-center text-gray-400 text-xs mt-8">
            {{ __('messages.activation_time_note', [], app()->getLocale()) }}
        </p>
    </div>

    <script>
        function updateFileName(input) {
            const label = document.getElementById('file-name');
            if (input.files && input.files[0]) {
                label.innerHTML = `<span class="text-green-600 font-bold">✓ {{ __('messages.file_selected', [], app()->getLocale()) }}: ${input.files[0].name}</span>`;
            }
        }
    </script>
</body>
</html>