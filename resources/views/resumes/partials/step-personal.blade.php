@props(['resume' => null, 'isEdit' => false])

<div x-show="step === 1" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">01. المعلومات الشخصية</h2>
        
        @if($isEdit && $resume)
            @if($resume->is_name_locked)
                <div class="bg-yellow-50 border-r-4 border-yellow-500 p-4 mb-6 text-yellow-800 text-sm font-bold">
                    🔒 تم الوصول للحد الأقصى لتغيير الاسم. هوية هذه السيرة مقفلة لحمايتها.
                </div>
            @else
                <div class="bg-blue-50 text-blue-800 p-3 mb-6 text-sm">
                    تبقى لديك {{ $resume->name_changes_left }} محاولات لتغيير الاسم.
                    @if($resume->name_changes_left == 1)
                        <span class="font-bold text-red-600 block mt-1">⚠️ تحذير: هذا آخر تغيير مسموح به.</span>
                    @endif
                </div>
            @endif
        @endif

        <div class="mb-8 p-6 border border-blue-200 text-center transition-all"
                                     :class="aiCredits <= 0 ? 'ai-locked-section' : 'bg-blue-50'">
                                    <template x-if="aiCredits <= 0">
                                        <p class="text-red-600 font-bold text-xs mb-2">🔒 الرصيد غير كافٍ لاستخدام الذكاء الاصطناعي</p>
                                    </template>
                                    <h3 class="text-lg font-bold text-blue-800 mb-2">✨ وفر وقتك!</h3>
                                    <button type="button" class="sharp-btn-primary !bg-blue-600" 
                                            @click="$refs.cvFileInput.click()" :disabled="isUploading || aiCredits <= 0">
                                        <span x-show="!isUploading">📄 ارفع سيرة PDF (ذكاء اصطناعي)</span>
                                        <span x-show="isUploading">⏳ جاري المعالجة...</span>
                                    </button>
                                    <input type="file" x-ref="cvFileInput" accept=".pdf" class="hidden" @change="uploadAndParseCV($event)">
                                </div>

                                <div class="mb-8">
                                    <label class="sharp-label">الصورة الشخصية</label>
                                    <div class="flex items-center gap-6">
                                        <div class="w-32 h-32 bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden" 
                                             :style="shape === 'circle' ? 'border-radius: 50% !important' : ''">
                                            <template x-if="croppedPhotoData">
                                                <img :src="croppedPhotoData" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!croppedPhotoData">
                                                <span class="text-4xl text-gray-400">👤</span>
                                            </template>
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="sharp-btn-primary !px-4 !py-2 !text-sm cursor-pointer">
                                                تحميل صورة <input type="file" class="hidden" accept="image/*" @change="initFile">
                                            </label>
                                            <button type="button" @click="shape = shape === 'circle' ? 'square' : 'circle'" class="text-xs font-bold text-blue-600 underline">تغيير الإطار</button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="cropped_photo_base64" :value="croppedPhotoData">
                                </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <label class="sharp-label">الاسم الكامل *</label>
                <input type="text" name="full_name" x-model="full_name" 
                       class="sharp-input" required
                       @if($isEdit && $resume && $resume->is_name_locked) readonly style="background-color:#f3f4f6;" @endif
                       @if($isEdit && $resume && !$resume->is_name_locked && $resume->name_changes_left == 1)
                           @change="showNameWarning()"
                       @endif
                >
            </div>
            <div class="col-span-full md:col-span-1">
                                        <label class="sharp-label">رقم الهاتف *</label>
                                        <input type="tel" name="phone" x-model="phone" class="sharp-input" required>
                                    </div>
                                    <div>
                                        <label class="sharp-label">المسمى الوظيفي</label>
                                        <input type="text" name="job_title" x-model="job_title" class="sharp-input">
                                    </div>
                                    <div>
                                        <label class="sharp-label">البريد الإلكتروني</label>
                                        <input type="email" name="email" x-model="email" class="sharp-input">
                                    </div>
                                    <div class="col-span-full">
                                        <label class="sharp-label">العنوان</label>
                                        <input type="text" name="address" x-model="address" class="sharp-input">
                                    </div>
                                </div>
                            </div>
                        </div>