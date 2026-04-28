@php
    $user = $resume->user;
    $removeWatermark = $user->plan && $user->plan->remove_watermark;
    $templateView = $resume->template->view_path ?? 'templates.green-classic';
    $hideActions = true;

    $photoAbsoluteUrl = null;
    if ($resume->personalDetail && $resume->personalDetail->photo_path) {
        $path = $resume->personalDetail->photo_path;
        if (Storage::disk('public')->exists($path)) {
            $photoAbsoluteUrl = asset('storage/' . $path);
        }
    }
@endphp

<!DOCTYPE html>
<html lang="{{ $resume->resume_language }}" dir="{{ $resume->resume_language == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $resume->personalDetail->full_name ?? 'السيرة الذاتية' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- نفس الخط الاحترافي لضمان التوافق في PDF --}}
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Noto+Sans+Arabic:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            background: white; 
            margin: 0; 
            padding: 20px;
            font-family: 'Tajawal', 'Noto Sans Arabic', 'Cairo', sans-serif;
            line-height: 1.5;
        }
        @media print { 
            body { padding: 0; } 
        }
    </style>
</head>
<body>
    @if(!$removeWatermark)
        <div class="fixed inset-0 flex items-center justify-center pointer-events-none z-50">
            <div class="text-9xl font-black text-gray-200 rotate-45 opacity-60">DEMO</div>
        </div>
    @endif

    @include($templateView, [
        'resume' => $resume,
        'hideActions' => true,
        'photoAbsoluteUrl' => $photoAbsoluteUrl
    ])
</body>
</html>