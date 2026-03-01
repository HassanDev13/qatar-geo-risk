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
        
        .conclusion-container {
            page-break-before: always;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .conclusion-title {
            font-size: 32px;
            font-weight: 700;
            color: #3498db;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 3px solid #3498db;
            padding-bottom: 15px;
        }
        
        .conclusion-section {
            margin-bottom: 25px;
        }
        
        .conclusion-section h2 {
            font-size: 22px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            border-right: 4px solid #3498db;
            padding-right: 15px;
        }
        
        .conclusion-section p {
            font-size: 16px;
            line-height: 1.9;
            color: #34495e;
            margin-bottom: 12px;
            text-align: justify;
        }
        
        .highlight-box {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            border-right: 4px solid #3498db;
            margin: 20px 0;
            text-align: center;
        }
        
        .highlight-box p {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-item {
            background: white;
            padding: 15px;
            border-radius: 5px;
            border-right: 3px solid #3498db;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #3498db;
            display: block;
        }
        
        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="conclusion-container">
        <h1 class="conclusion-title">📝 خاتمة - Conclusion</h1>
        
        <div class="conclusion-section">
            <h2>إنجاز المعجم</h2>
            <p>
                بفضل الله تعالى، تم إنجاز هذا المعجم الشامل للمصطلحات التقنية الإنجليزية والعربية باستخدام أحدث تقنيات الذكاء الاصطناعي. يمثل هذا العمل خطوة مهمة نحو توحيد المصطلحات التقنية وتسهيل عملية الترجمة والبحث العلمي في المجال التقني.
            </p>
        </div>
        
        <div class="conclusion-section">
            <h2>الإحصائيات النهائية</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number">{{ number_format($statistics['unique_english_terms']) }}</span>
                    <span class="stat-label">مصطلح إنجليزي</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ number_format($statistics['unique_arabic_terms']) }}</span>
                    <span class="stat-label">ترجمة عربية</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $statistics['avg_confidence'] }}%</span>
                    <span class="stat-label">متوسط الثقة</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ count($resources) }}</span>
                    <span class="stat-label">مصدر مستخدم</span>
                </div>
            </div>
        </div>
        
        <div class="conclusion-section">
            <h2>التطوير المستقبلي</h2>
            <p>
                نأمل أن يكون هذا المعجم نقطة انطلاق لمشاريع أكبر في مجال توحيد المصطلحات التقنية العربية. يمكن تطوير هذا العمل مستقبلاً من خلال:
            </p>
            <p>
                • إضافة مصادر جديدة وتحديث المصطلحات بشكل دوري<br>
                • تحسين دقة الترجمات باستخدام نماذج ذكاء اصطناعي أكثر تطوراً<br>
                • إضافة أمثلة استخدام وسياقات للمصطلحات<br>
                • توسيع المعجم ليشمل مجالات تقنية متخصصة أخرى
            </p>
        </div>
        
        <div class="highlight-box">
            <p>
                "المعرفة تنمو بالمشاركة، ونأمل أن يكون هذا المعجم مرجعاً مفيداً للباحثين والمترجمين والمتخصصين"
            </p>
        </div>
        
        <div class="conclusion-section" style="text-align: center; margin-top: 40px;">
            <p style="font-size: 14px; color: #7f8c8d;">
                تم إنشاء هذا المعجم في {{ $statistics['generation_date'] }}<br>
                باستخدام تقنيات الذكاء الاصطناعي المتقدمة
            </p>
        </div>
    </div>
</body>
</html>
