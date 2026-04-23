@props(['closeAction' => '', 'resumeUuid' => null, 'currentLang' => null])

@php
    $lang = $currentLang ?? app()->getLocale();
@endphp

<div {{ $attributes->merge(['class' => 'no-print fixed inset-0 z-[150] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md']) }}>
    <div @click.stop class="bg-white rounded-2xl shadow-xl w-full max-w-6xl overflow-hidden relative max-h-[95vh] flex flex-col">
        
        {{-- زر الإغلاق مع سكريبت مضمون --}}
        <button type="button" 
                class="close-modal-btn absolute top-4 left-4 z-10 text-gray-400 hover:text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-full p-2 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <div class="overflow-y-auto p-6 md:p-10">
            <div class="text-center mb-10">
                <span class="bg-indigo-50 text-indigo-600 text-xs font-semibold px-4 py-1.5 rounded-full uppercase tracking-wider mb-4 inline-block">{{ __('messages.pricing_plans', [], $lang) }}</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3">{{ __('messages.choose_plan_title', [], $lang) }}</h2>
                <p class="text-gray-500 text-base max-w-2xl mx-auto">{{ __('messages.choose_plan_subtitle', [], $lang) }}</p>
            </div>
            
            {{-- تمرير أفقي للهواتف --}}
            <div class="overflow-x-auto pb-4 md:overflow-visible">
                <div class="flex flex-nowrap gap-6 md:grid md:grid-cols-2 lg:grid-cols-4 md:gap-8 w-max md:w-full">
                    @foreach(\App\Models\Plan::where('is_active', true)->get() as $plan)
                        @php
                            $months = floor($plan->duration_in_days / 30);
                            $durationText = $months > 0 ? "{$months} " . __('messages.months', [], $lang) : "{$plan->duration_in_days} " . __('messages.days', [], $lang);
                            if ($plan->duration_in_days >= 365) $durationText = __('messages.full_year', [], $lang);
                            elseif ($plan->duration_in_days >= 90) $durationText = floor($plan->duration_in_days / 30) . " " . __('messages.months', [], $lang);
                        @endphp
                        <div class="relative flex flex-col bg-white border border-gray-200 rounded-2xl p-6 w-72 md:w-auto transition-all duration-200 hover:border-indigo-200 hover:shadow-md {{ $plan->is_popular ? 'border-indigo-300 ring-1 ring-indigo-300 scale-105 z-10' : '' }}">
                            
                            @if($plan->is_popular)
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs font-bold px-4 py-1 rounded-full shadow-sm whitespace-nowrap">
                                    ✨ {{ __('messages.most_popular', [], $lang) }}
                                </div>
                            @endif

                            <div class="mb-5">
                                <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $plan->name }}</h3>
                                <p class="text-gray-500 text-sm leading-relaxed">{{ $plan->description }}</p>
                            </div>

                            <div class="flex items-baseline gap-1 mb-3">
                                <span class="text-4xl font-extrabold text-gray-900">{{ (int)$plan->price }}</span>
                                <span class="text-gray-500 font-medium text-sm">{{ __('messages.currency', [], $lang) }}</span>
                            </div>
                            <div class="text-xs text-indigo-600 font-medium mb-5 bg-indigo-50 inline-block px-3 py-1 rounded-full self-start">
                                🕒 {{ $durationText }}
                            </div>

                            <ul class="space-y-3 mb-8 flex-1 text-sm">
                                <li class="flex items-center gap-2 text-gray-700">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $plan->cv_limit }} {{ __('messages.cv_count', [], $lang) }}</span>
                                </li>
                                <li class="flex items-center gap-2 text-gray-700">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $plan->ai_credits }} {{ __('messages.ai_credits', [], $lang) }}</span>
                                </li>
                                <li class="flex items-center gap-2 {{ $plan->remove_watermark ? 'text-gray-700' : 'text-gray-400' }}">
                                    @if($plan->remove_watermark)
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span>{{ __('messages.no_watermark', [], $lang) }}</span>
                                    @else
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        <span>{{ __('messages.with_watermark', [], $lang) }}</span>
                                    @endif
                                </li>
                                @if($plan->has_cover_letter)
                                <li class="flex items-center gap-2 text-gray-700">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $plan->cv_limit }} {{ __('messages.cover_letter_item', [], $lang) }}</span>
                                </li>
                                @endif
                                @if($plan->priority_support)
                                <li class="flex items-center gap-2 text-gray-700">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ __('messages.priority_support', [], $lang) }}</span>
                                </li>
                                @endif
                            </ul>

                            <a href="{{ route('payment.checkout', $plan->slug) }}" class="w-full text-center py-2.5 rounded-xl font-bold transition-all duration-200 {{ $plan->is_popular ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-900 text-white hover:bg-gray-800' }}">
                                {{ __('messages.btn_upgrade', [], $lang) }}
                            </a>

                            <button type="button" onclick="document.getElementById('manual-payment-{{ $plan->id }}').classList.remove('hidden')" class="mt-2 w-full text-center py-2 rounded-xl font-medium border border-gray-200 text-gray-600 hover:border-indigo-300 hover:text-indigo-600 transition-all duration-200 text-sm">
                                {{ __('messages.manual_payment', [], $lang) }}
                            </button>

                            <div id="manual-payment-{{ $plan->id }}" class="hidden mt-4 bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <form action="{{ route('payment.manual') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <p class="font-bold text-gray-800">{{ __('messages.payment_instructions', [], $lang) }}</p>
                                        <p>{{ __('messages.transfer_to_number', [], $lang) }}</p>
                                        <p class="text-base font-mono font-bold text-indigo-600 text-center">{{ __('messages.unified_transfer_number', [], $lang) ?? '26121732' }}</p>
                                        <p class="text-[11px] text-gray-400">{{ __('messages.upload_receipt', [], $lang) }}</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('messages.screenshot_label', [], $lang) }}</label>
                                        <input type="file" name="screenshot" accept="image/*" required 
                                               class="w-full text-sm border border-gray-200 rounded-lg p-1.5 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200">
                                    </div>
                                    
                                    <div class="flex gap-2 pt-1">
                                        <button type="submit" class="flex-1 bg-indigo-600 text-white py-1.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                                            {{ __('messages.submit_request', [], $lang) }}
                                        </button>
                                        <button type="button" onclick="document.getElementById('manual-payment-{{ $plan->id }}').classList.add('hidden')" 
                                                class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">
                                            {{ __('messages.cancel', [], $lang) }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- سكريبت مخصص لإغلاق المودال --}}
<script>
    (function() {
        // البحث عن زر الإغلاق داخل هذا المودال
        const modalRoot = document.currentScript?.parentElement;
        if (!modalRoot) return;
        
        const closeBtn = modalRoot.querySelector('.close-modal-btn');
        if (!closeBtn) return;
        
        // دالة إغلاق: البحث عن الأب الذي يحتوي على class="fixed inset-0"
        const modalContainer = modalRoot.closest('.fixed.inset-0');
        if (!modalContainer) return;
        
        closeBtn.addEventListener('click', function() {
            modalContainer.style.display = 'none';
            document.body.style.overflow = '';
            // إذا كانت هناك دالة خارجية (مثلاً closePlansModal) فاستدعها
            if (typeof window.closePlansModal === 'function') {
                window.closePlansModal();
            }
        });
    })();
</script>