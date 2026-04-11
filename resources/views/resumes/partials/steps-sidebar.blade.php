<aside class="w-full lg:w-1/4">
    <div class="sticky top-8">
        <div class="mb-6 px-2">
            <h1 class="text-2xl font-black text-gray-900 italic">
                {{ $title ?? 'بناء السيرة' }}
            </h1>
            <div class="flex justify-between items-center mt-2">
                <p class="text-sm text-blue-600 font-bold">
                    الخطوة <span x-text="step"></span> من <span x-text="maxStep"></span>
                </p>
                <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-1 font-bold">
                    الرصيد: <span x-text="aiCredits"></span> ✨
                </span>
            </div>
        </div>
        <nav class="flex flex-col shadow-sm">
            <template x-for="(label, index) in stepLabels" :key="index">
                <div @click="step = index + 1" class="step-link" :class="{'active': step === index + 1}">
                    <span class="step-number" x-text="String(index + 1).padStart(2, '0')"></span>
                    <span x-text="label"></span>
                </div>
            </template>
        </nav>
    </div>
</aside>