@php
    $user = $resume->user;
    $removeWatermark = $user->plan && $user->plan->remove_watermark;
    $templateView = $resume->template->view_path ?? 'templates.green-classic';
    $hideActions = true;

    // إنشاء رابط مطلق للصورة باستخدام Storage::url (يدعم local و S3)
    $photoAbsoluteUrl = null;
    if ($resume->personalDetail && $resume->personalDetail->photo_path) {
        // تأكد من وجود الملف قبل إنشاء الرابط
        if (Storage::disk('public')->exists($resume->personalDetail->photo_path)) {
            // استخدم Storage::url للحصول على رابط عام (مطلق)
            $photoAbsoluteUrl = Storage::disk('public')->url($resume->personalDetail->photo_path);
        } else {
            // تسجيل خطأ فقط للمطور (لن يظهر للمستخدم)
            error_log("Photo file not found: " . $resume->personalDetail->photo_path);
        }
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
        'photoAbsoluteUrl' => $photoAbsoluteUrl
    ])

    <!-- Debug info (لن تظهر في PDF النهائي، ولكن يمكنك رؤيتها في معاينة المتصفح) -->
    @if(!$hideActions && !$photoAbsoluteUrl)
        <div style="background:#f8d7da; padding:10px; margin-top:20px; font-size:12px; color:#721c24;">
            ⚠️ الصورة غير موجودة: لم يتم العثور على ملف الصورة في المسار المخزن.
        </div>
    @endif
</body>
</html>