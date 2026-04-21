<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-right" dir="rtl">
            {{ __('messages.cover_letter_title') }}
        </h2>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-t-lg border-b">
                <div class="p-6 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ __('messages.cover_letter_for', ['job_title' => $coverLetter->target_job_title]) }}</h3>
                        @if($coverLetter->company_name)
                            <p class="text-gray-500 mt-1">{{ __('messages.company', ['name' => $coverLetter->company_name]) }}</p>
                        @endif
                        <p class="text-sm text-gray-400 mt-1">{{ __('messages.created_at', ['date' => $coverLetter->created_at->format('Y-m-d H:i')]) }}</p>
                    </div>
                    <div class="flex gap-3 flex-wrap">
                        <button type="button" id="editCoverLetterBtn" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg font-bold hover:bg-yellow-700 transition">✏️ {{ __('messages.edit_cover_letter') }}</button>
                        <button type="button" id="saveCoverLetterBtn" style="display:none;" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 transition">{{ __('messages.save_changes') }}</button>
                        <a href="{{ route('cover-letters.download', $coverLetter->id) }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 transition">{{ __('messages.download_pdf') }}</a>
                        <a href="{{ route('cover-letters.combined-download', $coverLetter->id) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg font-bold hover:bg-purple-700 transition">{{ __('messages.combined_download') }}</a>
                        <a href="{{ route('cover-letters.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition">{{ __('messages.create_new') }}</a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-b-lg">
                <div class="p-8">
                    <div id="displayContent" class="prose prose-lg max-w-none text-gray-800 whitespace-pre-line" dir="auto">
                        {!! nl2br(e($coverLetter->content)) !!}
                    </div>
                    <textarea id="editContent" style="display:none;" rows="20" class="w-full border border-gray-300 rounded-lg p-4 font-sans text-gray-800">{{ $coverLetter->content }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <script>
        const editBtn = document.getElementById('editCoverLetterBtn');
        const saveBtn = document.getElementById('saveCoverLetterBtn');
        const displayDiv = document.getElementById('displayContent');
        const editTextarea = document.getElementById('editContent');
        const coverLetterId = {{ $coverLetter->id }};
        const updateUrl = "{{ route('cover-letters.update', $coverLetter->id) }}";

        editBtn.addEventListener('click', function() {
            displayDiv.style.display = 'none';
            editTextarea.style.display = 'block';
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-flex';
        });

        saveBtn.addEventListener('click', async function() {
            const newContent = editTextarea.value;
            try {
                const response = await fetch(updateUrl, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ content: newContent })
                });
                const data = await response.json();
                if (data.success) {
                    displayDiv.innerHTML = newContent.replace(/\n/g, '<br>');
                    displayDiv.style.display = 'block';
                    editTextarea.style.display = 'none';
                    editBtn.style.display = 'inline-flex';
                    saveBtn.style.display = 'none';
                    alert('{{ __("messages.saved_success") ?? "تم حفظ التعديلات بنجاح" }}');
                } else {
                    alert('{{ __("messages.save_error") ?? "حدث خطأ أثناء الحفظ" }}');
                }
            } catch (error) {
                alert('{{ __("messages.save_error") ?? "حدث خطأ تقني" }}');
            }
        });
    </script>
</x-app-layout>