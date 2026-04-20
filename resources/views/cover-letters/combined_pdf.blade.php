<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: 'xbriyaz';
            src: url('fonts/xbriyaz.ttf');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'xbriyaz', sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #1a1a1a;
            margin: 0;
            padding: 20px;
            direction: rtl;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 5px;
        }
        .cover-letter {
            margin-bottom: 40px;
            page-break-after: avoid;
        }
        .resume {
            page-break-before: always;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 22px;
            color: #2563eb;
        }
        .photo {
            text-align: center;
            margin-bottom: 15px;
        }
        .photo img {
            max-width: 120px;
            border-radius: 50%;
            border: 1px solid #ccc;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .job-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>

    {{-- خطاب التغطية --}}
    <div class="cover-letter">
        <div class="header">
            <h1>خطاب تغطية</h1>
            <div class="job-title">{{ $coverLetter->target_job_title }}</div>
            @if($coverLetter->company_name)
                <div>إلى: {{ $coverLetter->company_name }}</div>
            @endif
        </div>
        <div class="content">
            {!! nl2br(e($coverLetter->content)) !!}
        </div>
        <div class="date-info" style="margin-top:20px; text-align:left; font-size:11px; color:#6b7280;">
            تم الإنشاء: {{ $coverLetter->created_at->format('Y-m-d H:i') }}
        </div>
    </div>

    {{-- السيرة الذاتية --}}
    <div class="resume">
        <div class="header">
            <h1>السيرة الذاتية</h1>
        </div>

        @if($resume->personalDetail)
            <div class="info-row"><strong>الاسم:</strong> {{ $resume->personalDetail->full_name }}</div>
            <div class="info-row"><strong>المسمى الوظيفي:</strong> {{ $resume->personalDetail->job_title }}</div>
            <div class="info-row"><strong>البريد الإلكتروني:</strong> {{ $resume->personalDetail->email }}</div>
            <div class="info-row"><strong>الهاتف:</strong> {{ $resume->personalDetail->phone }}</div>
            <div class="info-row"><strong>العنوان:</strong> {{ $resume->personalDetail->address }}</div>
            @if($resume->personalDetail->summary)
                <div class="section-title">الملخص المهني</div>
                <div>{{ $resume->personalDetail->summary }}</div>
            @endif
        @endif

        @if($resume->experiences->count())
            <div class="section-title">الخبرات العملية</div>
            @foreach($resume->experiences as $exp)
                <div><strong>{{ $exp->position }}</strong> - {{ $exp->company }} ({{ $exp->start_date }} - {{ $exp->end_date ?? 'حالياً' }})</div>
                <div>{{ $exp->description }}</div>
                <br>
            @endforeach
        @endif

        @if($resume->educations->count())
            <div class="section-title">المؤهلات الدراسية</div>
            @foreach($resume->educations as $edu)
                <div><strong>{{ $edu->degree }}</strong> في {{ $edu->field_of_study }} - {{ $edu->institution }} ({{ $edu->graduation_year }})</div>
            @endforeach
        @endif

        @if($resume->skills->count())
            <div class="section-title">المهارات</div>
            <div>{{ implode(' • ', $resume->skills->pluck('name')->toArray()) }}</div>
        @endif

        @if($resume->languages->count())
            <div class="section-title">اللغات</div>
            @foreach($resume->languages as $lang)
                <div>{{ $lang->name }} ({{ $lang->proficiency }})</div>
            @endforeach
        @endif
    </div>

    <div class="footer" style="margin-top: 40px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af; padding-top: 10px;">
        تم إنشاء هذه المستندات بواسطة منصة Cvmaker
    </div>
</body>
</html>