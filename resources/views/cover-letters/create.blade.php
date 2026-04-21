<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-right" dir="rtl">
            {{ __('messages.cover_letter_create_title') }}
        </h2>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">{{ __('messages.cover_letter_subtitle') }}</h3>

                    <form action="{{ route('cover-letters.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <h4 class="font-bold text-blue-800 mb-3">{{ __('messages.resume_source') }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="resume_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.select_existing_resume') }}</label>
                                    <select id="resume_id" name="resume_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">-- {{ __('messages.select_existing_resume') }} --</option>
                                        @foreach($resumes as $resume)
                                            <option value="{{ $resume->id }}" {{ old('resume_id') == $resume->id ? 'selected' : '' }}>{{ $resume->title }} ({{ $resume->updated_at->diffForHumans() }})</option>
                                        @endforeach
                                    </select>
                                    @error('resume_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="uploaded_file" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.or_upload_file') }}</label>
                                    <input type="file" id="uploaded_file" name="uploaded_file" accept=".pdf,.doc,.docx" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.max_file_size') }}</p>
                                    @error('uploaded_file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="target_job_title" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.target_job_title') }} <span class="text-red-500">*</span></label>
                            <input type="text" id="target_job_title" name="target_job_title" value="{{ old('target_job_title') }}" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('messages.target_job_title_placeholder') }}">
                            @error('target_job_title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-6">
                            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.company_name') }}</label>
                            <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('messages.company_name_placeholder') }}">
                            @error('company_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-6">
                            <label for="job_description" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.job_description_or_url') }}</label>
                            <textarea id="job_description" name="job_description" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('messages.job_description_placeholder') }}">{{ old('job_description') }}</textarea>
                            <div class="mt-2">
                                <label for="job_description_url" class="block text-sm text-gray-500 mb-1">{{ __('messages.or_enter_url') }}</label>
                                <input type="url" id="job_description_url" name="job_description_url" value="{{ old('job_description_url') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="https://...">
                            </div>
                            @error('job_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            @error('job_description_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-6">
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.language') }} <span class="text-red-500">*</span></label>
                            <select id="language" name="language" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="ar" {{ old('language') == 'ar' ? 'selected' : '' }}>العربية</option>
                                <option value="en" {{ old('language') == 'en' ? 'selected' : '' }}>English</option>
                                <option value="fr" {{ old('language') == 'fr' ? 'selected' : '' }}>Français</option>
                            </select>
                            @error('language') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <a href="{{ url()->previous() }}" class="px-5 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">{{ __('messages.cancel') }}</a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition">{{ __('messages.generate_cover_letter') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>