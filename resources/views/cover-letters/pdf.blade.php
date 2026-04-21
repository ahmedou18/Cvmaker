<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face { font-family: 'xbriyaz'; src: url('fonts/xbriyaz.ttf'); font-weight: normal; font-style: normal; }
        body { font-family: 'xbriyaz', sans-serif; font-size: 13px; line-height: 1.8; color: #1a1a1a; margin: 0; padding: 20px; direction: rtl; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #2563eb; }
        .header h1 { font-size: 22px; color: #2563eb; margin: 0 0 5px 0; }
        .header .job-title { font-size: 15px; color: #4b5563; margin: 0; }
        .header .company { font-size: 14px; color: #6b7280; margin: 5px 0 0 0; }
        .content { text-align: justify; white-space: pre-line; margin-top: 20px; }
        .footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af; }
        .date-info { text-align: left; font-size: 11px; color: #6b7280; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('messages.cover_letter_title') }}</h1>
        <p class="job-title">{{ $coverLetter->target_job_title }}</p>
        @if($coverLetter->company_name)
            <p class="company">{{ __('messages.company', ['name' => $coverLetter->company_name]) }}</p>
        @endif
    </div>
    <div class="content">
        {!! nl2br(e($coverLetter->content)) !!}
    </div>
    <div class="date-info">
        {{ __('messages.created_at', ['date' => $coverLetter->created_at->format('Y-m-d H:i')]) }}
    </div>
    <div class="footer">
        <p>{{ __('messages.cover_letter_footer') }}</p>
    </div>
</body>
</html>