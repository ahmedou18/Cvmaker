@php
    $user = $resume->user;
    $removeWatermark = $user->plan && $user->plan->remove_watermark;
    $templateView = $resume->template->view_path ?? 'templates.green-classic';
    $hideActions = true; // مهم جداً لإخفاء الأزرار والمودال

    // إنشاء رابط مطلق للصورة الشخصية (لـ Puppeteer)
    $photoAbsoluteUrl = null;
    if ($resume->personalDetail && $resume->personalDetail->photo_path) {
        $photoAbsoluteUrl = url('storage/' . $resume->personalDetail->photo_path);
    }
@endphp

<!DOCTYPE html>
<html lang="{{ $resume->resume_language }}" dir="{{ $resume->resume_language == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resume->personalDetail->full_name ?? 'السيرة الذاتية' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: white; margin: 0; padding: 20px; }
        .no-print, .no-print * { display: none !important; }
        @media print { body { padding: 0; } }
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
        'photoAbsoluteUrl' => $photoAbsoluteUrl   // تمرير الرابط المطلق إلى القالب
    ])
</body>
</html>