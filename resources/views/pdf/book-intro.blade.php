<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            color: #2c3e50;
            line-height: 1.8;
            margin: 0;
            padding: 20px;
        }
        
        .intro-container {
            page-break-after: always;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .intro-title {
            font-size: 32px;
            font-weight: 700;
            color: #3498db;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 3px solid #3498db;
            padding-bottom: 15px;
        }
        
        .intro-section {
            margin-bottom: 30px;
        }
        
        .intro-section h2 {
            font-size: 22px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            border-right: 4px solid #3498db;
            padding-right: 15px;
        }
        
        .intro-section p {
            font-size: 16px;
            line-height: 1.9;
            color: #34495e;
            margin-bottom: 12px;
            text-align: justify;
        }
        
        .intro-section ul {
            list-style: none;
            padding-right: 20px;
        }
        
        .intro-section li {
            padding: 8px 0;
            padding-right: 25px;
            position: relative;
        }
        
        .intro-section li:before {
            content: "✓";
            position: absolute;
            right: 0;
            color: #3498db;
            font-weight: bold;
        }
        
        .highlight-box {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            border-right: 4px solid #3498db;
            margin: 20px 0;
        }
        
        .highlight-box p {
            margin: 0;
            font-size: 15px;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="intro-container">
        <h1 class="intro-title">📖 مقدمة - Introduction</h1>
        
        <div class="intro-section">
            <h2>عن هذا المعجم</h2>
            <p>
                المعجم الجامع هو معجم شامل للمصطلحات التقنية الإنجليزية والعربية، تم إنشاؤه باستخدام تقنيات الذكاء الاصطناعي المتقدمة لاستخراج وتحليل المصطلحات من مصادر متعددة. يهدف هذا المعجم إلى توفير مرجع موثوق وشامل للمترجمين والباحثين والمتخصصين في المجال التقني.
            </p>
        </div>
        
        <div class="intro-section">
            <h2>منهجية العمل</h2>
            <p>
                تم بناء هذا المعجم من خلال:
            </p>
            <ul>
                <li>استخراج المصطلحات تلقائياً من مصادر تقنية متنوعة باستخدام الذكاء الاصطناعي</li>
                <li>تحليل وتصنيف المصطلحات حسب مستوى الثقة والاتساق</li>
                <li>تجميع الترجمات المتعددة لكل مصطلح مع إحصائيات الاستخدام</li>
                <li>توثيق المصادر وأرقام الصفحات لكل مصطلح</li>
                <li>حساب نسب التكرار ومستويات الجودة لكل ترجمة</li>
            </ul>
        </div>
        
        <div class="intro-section">
            <h2>مميزات المعجم</h2>
            <ul>
                <li>ترجمات متعددة لكل مصطلح مع تحديد الأكثر شيوعاً</li>
                <li>نسب التكرار والاستخدام لكل ترجمة</li>
                <li>مستويات الثقة والجودة (1-5 نجوم)</li>
                <li>تقييم الاتساق (عالي، متوسط، منخفض)</li>
                <li>توثيق المصادر وأرقام الصفحات</li>
                <li>فهرس عكسي (عربي → إنجليزي)</li>
                <li>إحصائيات شاملة عن المصطلحات</li>
            </ul>
        </div>
        
        <div class="highlight-box">
            <p>
                <strong>ملاحظة:</strong> تم إنشاء هذا المعجم في {{ $statistics['generation_date'] }} ويحتوي على {{ number_format($statistics['unique_english_terms']) }} مصطلح إنجليزي بمتوسط ثقة {{ $statistics['avg_confidence'] }}%.
            </p>
        </div>
        
        <div class="intro-section">
            <h2>المصادر المستخدمة</h2>
            <p>تم استخراج المصطلحات من المصادر التالية:</p>
            <ul>
                @foreach($resources as $resource)
                    <li>{{ $resource->name }}</li>
                @endforeach
            </ul>
        </div>
        
        <div class="intro-section">
            <h2>كيفية الاستخدام</h2>
            <p>
                استخدم الفهرس الأبجدي للبحث عن المصطلح الإنجليزي. كل مصطلح يحتوي على:
            </p>
            <ul>
                <li>الترجمات العربية المتعددة</li>
                <li>نسبة الاستخدام لكل ترجمة</li>
                <li>مستوى الثقة والجودة</li>
                <li>المصادر وأرقام الصفحات</li>
            </ul>
        </div>
    </div>
</body>
</html>
