<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>سيرة ذاتية - {{ $resume->personalDetail->full_name ?? '' }}</title>
    <style>
        body {
            font-family: 'xbriyaz', sans-serif;
            direction: rtl;
            text-align: right;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
        }
        
        /* تنسيقات الهيدر */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header-text {
            vertical-align: top;
        }
        .header-text h1 {
            color: #2c3e50;
            margin: 0 0 5px 0;
            font-size: 28px;
        }
        .header-text h3 {
            color: #7f8c8d;
            margin: 0 0 10px 0;
            font-weight: normal;
            font-size: 18px;
        }
        .contact-info {
            font-size: 13px;
            color: #555;
            line-height: 1.8;
        }
        
        /* باقي التنسيقات */
        .section-title {
            color: #2c3e50;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .item {
            margin-bottom: 15px;
        }
        .item-title {
            font-weight: bold;
            font-size: 15px;
        }
        .item-subtitle {
            color: #7f8c8d;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .skills-container {
            margin-top: 10px;
        }
        .skill-tag {
            background-color: #ecf0f1;
            padding: 5px 12px;
            border-radius: 4px;
            display: inline-block;
            margin-left: 5px;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        {{-- إضافة العلامة المائية (نص خلفي) إذا كانت الخطة لا تسمح بإزالتها --}}
        @if(!$removeWatermark)
            @page {
                margin: 1cm;
            }
            body::after {
                content: "Cvmaker - نموذج تجريبي";
                position: fixed;
                top: 50%;
                left: 0;
                right: 0;
                bottom: auto;
                text-align: center;
                font-size: 40px;
                font-weight: bold;
                color: rgba(0,0,0,0.07);
                transform: rotate(-45deg);
                pointer-events: none;
                z-index: -1;
                white-space: pre;
                letter-spacing: 4px;
            }
        @endif
    </style>
</head>
<body>

    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            @if(!empty($resume->personalDetail->photo_path))
                <td width="120" style="vertical-align: top;">
                    <img src="{{ public_path($resume->personalDetail->photo_path) }}" 
                         alt="الصورة الشخصية" 
                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                </td>
            @endif
            
            <td class="header-text" style="{{ !empty($resume->personalDetail->photo_path) ? 'padding-right: 15px;' : '' }}">
                <h1>{{ $resume->personalDetail->full_name ?? 'الاسم غير متوفر' }}</h1>
                <h3>{{ $resume->personalDetail->job_title ?? '' }}</h3>
                <div class="contact-info">
                    {{ $resume->personalDetail->email ?? '' }} 
                    @if($resume->personalDetail->phone) | {{ $resume->personalDetail->phone }} @endif
                    @if($resume->personalDetail->address) | {{ $resume->personalDetail->address }} @endif
                </div>
            </td>
        </tr>
    </table>

    @if(!empty($resume->personalDetail->summary))
        <h2 class="section-title">الملخص الشخصي</h2>
        <p style="text-align: justify;">{{ $resume->personalDetail->summary }}</p>
    @endif

    @if($resume->experiences->count() > 0)
        <h2 class="section-title">الخبرات العملية</h2>
        @foreach($resume->experiences as $exp)
            <div class="item">
                <div class="item-title">{{ $exp->position }} - {{ $exp->company }}</div>
                <div class="item-subtitle">
                    من: {{ \Carbon\Carbon::parse($exp->start_date)->format('Y-m-d') }} 
                    @if($exp->end_date) 
                        | إلى: {{ \Carbon\Carbon::parse($exp->end_date)->format('Y-m-d') }} 
                    @else 
                        | إلى: الآن 
                    @endif
                </div>
                @if($exp->description)
                    <p style="text-align: justify; margin-top: 5px;">{{ $exp->description }}</p>
                @endif
            </div>
        @endforeach
    @endif

    @if($resume->educations->count() > 0)
        <h2 class="section-title">المؤهلات العلمية</h2>
        @foreach($resume->educations as $edu)
            <div class="item">
                <div class="item-title">{{ $edu->degree }} {{ $edu->field_of_study ? 'في ' . $edu->field_of_study : '' }}</div>
                <div class="item-subtitle">
                    {{ $edu->institution }} 
                    @if($edu->graduation_year) | سنة التخرج: {{ $edu->graduation_year }} @endif
                </div>
            </div>
        @endforeach
    @endif

    @if($resume->skills->count() > 0)
        <h2 class="section-title">المهارات</h2>
        <div class="skills-container">
            @foreach($resume->skills as $skill)
                <span class="skill-tag">{{ $skill->name }}</span>
            @endforeach
        </div>
    @endif

    @if($resume->languages->count() > 0)
        <h2 class="section-title">اللغات</h2>
        <div class="skills-container">
            @foreach($resume->languages as $lang)
                <span class="skill-tag">{{ $lang->name }} ({{ $lang->proficiency }})</span>
            @endforeach
        </div>
    @endif

</body>
</html>