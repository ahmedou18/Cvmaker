@props(['closeAction' => '', 'resumeUuid' => null, 'currentLang' => null])

@php
    $lang = $currentLang ?? app()->getLocale();
@endphp

<div {{ $attributes->merge(['class' => 'no-print fixed inset-0 z-[150] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md']) }}>
    <div @click.stop class="bg-white rounded-[2rem] shadow-2xl w-full max-w-6xl overflow-hidden relative max-h-[95vh] flex flex-col">
        
        {{-- زر الإغلاق مع onclick صحيح --}}
        <button type="button" onclick="{!! $closeAction !!}" class="absolute top-6 left-6 z-10 text-gray-400 hover:text-red-500 bg-gray-100 hover:bg-red-50 rounded-full p-2 transition-all duration-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <div class="overflow-y-auto p-8 md:p-12">
            <div class="text-center mb-12">
                <span class="bg-blue-50 text-blue-600 text-xs font-black px-4 py-1.5 rounded-full uppercase tracking-wider mb-4 inline-block">{{ __('messages.pricing_plans', [], $lang) }}</span>
                <h2 class="text-4xl font-black text-gray-900 mb-4">{{ __('messages.choose_plan_title', [], $lang) }}</h2>
                <p class="text-gray-500 max-w-2xl mx-auto font-medium">{{ __('messages.choose_plan_subtitle', [], $lang) }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach(\App\Models\Plan::where('is_active', true)->get() as $plan)
                    @php
                        $months = floor($plan->duration_in_days / 30);
                        $durationText = $months > 0 ? "{$months} " . __('messages.months', [], $lang) : "{$plan->duration_in_days} " . __('messages.days', [], $lang);
                        if ($plan->duration_in_days >= 365) $durationText = __('messages.full_year', [], $lang);
                        elseif ($plan->duration_in_days >= 90) $durationText = floor($plan->duration_in_days / 30) . " " . __('messages.months', [], $lang);
                    @endphp
                    <div class="relative group flex flex-col bg-white border-2 {{ $plan->is_popular ? 'border-blue-600 shadow-2xl scale-105 z-10' : 'border-gray-100 hover:border-blue-200 shadow-sm' }} rounded-3xl p-8 transition-all duration-500">
                        
                        @if($plan->is_popular)
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-xs font-black px-6 py-1.5 rounded-full shadow-lg whitespace-nowrap">
                                ✨ {{ __('messages.most_popular', [], $lang) }}
                            </div>
                        @endif

                        <div class="mb-8">
                            <h3 class="text-xl font-black text-gray-900 mb-2">{{ $plan->name }}</h3>
                            <p class="text-gray-500 text-sm leading-relaxed min-h-[40px]">{{ $plan->description }}</p>
                        </div>

                        <div class="flex items-baseline gap-1 mb-4">
                            <span class="text-5xl font-black text-gray-900">{{ (int)$plan->price }}</span>
                            <span class="text-gray-400 font-bold">{{ __('messages.currency', [], $lang) }}</span>
                        </div>
                        <div class="text-sm text-blue-600 font-bold mb-6 bg-blue-50 inline-block px-3 py-1 rounded-full">
                            🕒 {{ $durationText }}
                        </div>

                        <ul class="space-y-4 mb-10 flex-1">
                            <li class="flex items-center gap-3 text-gray-700 font-semibold text-sm">
                                <div class="bg-green-100 p-1 rounded-full"><svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span>{{ $plan->cv_limit }} {{ __('messages.cv_count', [], $lang) }}</span>
                            </li>
                            <li class="flex items-center gap-3 text-gray-700 font-semibold text-sm">
                                <div class="bg-green-100 p-1 rounded-full"><svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span>{{ $plan->ai_credits }} {{ __('messages.ai_credits', [], $lang) }}</span>
                            </li>
                            <li class="flex items-center gap-3 {{ $plan->remove_watermark ? 'text-gray-700' : 'text-gray-400' }} font-semibold text-sm">
                                <div class="{{ $plan->remove_watermark ? 'bg-green-100' : 'bg-gray-100' }} p-1 rounded-full">
                                    <svg class="w-3.5 h-3.5 {{ $plan->remove_watermark ? 'text-green-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="{{ $plan->remove_watermark ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"></path>
                                    </svg>
                                </div>
                                <span>{{ $plan->remove_watermark ? __('messages.no_watermark', [], $lang) : __('messages.with_watermark', [], $lang) }}</span>
                            </li>
                            @if($plan->has_cover_letter)
                            <li class="flex items-center gap-3 text-gray-700 font-semibold text-sm">
                                <div class="bg-green-100 p-1 rounded-full"><svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span>{{ $plan->cv_limit }} {{ __('messages.cover_letter_item', [], $lang) }}</span>
                            </li>
                            @endif
                            @if($plan->priority_support)
                            <li class="flex items-center gap-3 text-gray-700 font-semibold text-sm">
                                <div class="bg-green-100 p-1 rounded-full"><svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span>{{ __('messages.priority_support', [], $lang) }}</span>
                            </li>
                            @endif
                        </ul>

                        <a href="{{ route('payment.checkout', $plan->slug) }}" class="w-full text-center py-4 rounded-2xl font-black transition-all duration-300 {{ $plan->is_popular ? 'bg-blue-600 text-white hover:bg-blue-700 shadow-xl shadow-blue-200' : 'bg-gray-900 text-white hover:bg-gray-800' }}">
                            {{ __('messages.btn_upgrade', [], $lang) }}
                        </a>

                        <button type="button" onclick="document.getElementById('manual-payment-{{ $plan->id }}').classList.remove('hidden')" class="mt-3 w-full text-center py-3 rounded-2xl font-bold border-2 border-gray-200 text-gray-700 hover:border-blue-600 hover:text-blue-600 transition-all duration-300">
                            {{ __('messages.manual_payment', [], $lang) }}
                        </button>

                        <div id="manual-payment-{{ $plan->id }}" class="hidden mt-4 bg-slate-50 rounded-xl p-4 border border-slate-200">
                            <form action="{{ route('payment.manual') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                
                                <div class="text-sm text-gray-600 bg-white rounded-lg p-3 border border-gray-200">
                                    <p class="font-bold text-gray-900 mb-2">{{ __('messages.payment_instructions', [], $lang) }}</p>
                                    <p>{{ __('messages.transfer_to_number', [], $lang) }}</p>
                                    <p class="text-lg font-black text-blue-600 dir-ltr text-center my-2">26121732</p>
                                    <p class="text-xs text-gray-500 mt-2">{{ __('messages.upload_receipt', [], $lang) }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('messages.screenshot_label', [], $lang) }}</label>
                                    <input type="file" name="screenshot" accept="image/*" required 
                                           class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-600 focus:ring-2 focus:ring-blue-200 transition-all text-sm">
                                </div>
                                
                                <div class="flex gap-2">
                                    <button type="submit" class="flex-1 bg-green-600 text-white py-2.5 rounded-lg font-bold hover:bg-green-700 transition-all">
                                        {{ __('messages.submit_request', [], $lang) }}
                                    </button>
                                    <button type="button" onclick="document.getElementById('manual-payment-{{ $plan->id }}').classList.add('hidden')" 
                                            class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition-all">
                                        {{ __('messages.cancel', [], $lang) }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($resumeUuid)
                <div class="mt-12 bg-slate-50 rounded-2xl p-6 flex flex-col md:flex-row items-center justify-between gap-4 border border-slate-100">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-600 text-white p-3 rounded-xl shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        </div>
                        <div class="text-right">
                            <h4 class="font-black text-gray-900">{{ __('messages.already_subscribed', [], $lang) }}</h4>
                            <p class="text-sm text-gray-500 font-medium">{{ __('messages.download_resume_ready', [], $lang) }}</p>
                        </div>
                    </div>
                    
                    <button type="button" onclick="{!! $closeAction !!}; setTimeout(() => window.print(), 300);" class="bg-white text-gray-900 border-2 border-gray-900 px-8 py-3 rounded-xl hover:bg-gray-900 hover:text-white transition-all font-bold cursor-pointer">
                        {{ __('messages.download_resume', [], $lang) }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>