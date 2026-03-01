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
        
        .cover {
            text-align: center;
            padding: 80px 40px;
            page-break-after: always;
        }
        
        .cover h1 {
            font-size: 36px;
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: 700;
        }
        
        .cover .main-title {
            font-size: 42px;
            color: #3498db;
            font-weight: 900;
            margin-bottom: 15px;
        }
        
        .cover .subtitle {
            font-size: 18px;
            color: #7f8c8d;
            margin-bottom: 60px;
            font-weight: 400;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .stat-box {
            padding: 20px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #3498db;
            display: block;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .meta-info {
            margin-top: 60px;
            font-size: 14px;
            color: #95a5a6;
        }
    </style>
</head>
<body>
    <div class="cover">
        <h1 class="main-title">المعجم الجامع</h1>
        <h1 style="direction: ltr; font-size: 28px; color: #7f8c8d; font-weight: 500;">Comprehensive Dictionary</h1>
        <div class="subtitle">معجم شامل للمصطلحات التقنية الإنجليزية والعربية</div>
        
        <div class="stats">
            <div class="stat-box">
                <span class="stat-number">{{ number_format($statistics['total_terms']) }}</span>
                <span class="stat-label">إجمالي المصطلحات</span>
            </div>
            <div class="stat-box">
                <span class="stat-number">{{ number_format($statistics['unique_english_terms']) }}</span>
                <span class="stat-label">مصطلحات إنجليزية</span>
            </div>
            <div class="stat-box">
                <span class="stat-number">{{ number_format($statistics['unique_arabic_terms']) }}</span>
                <span class="stat-label">ترجمات عربية</span>
            </div>
            <div class="stat-box">
                <span class="stat-number">{{ $statistics['avg_confidence'] }}%</span>
                <span class="stat-label">متوسط الثقة</span>
            </div>
        </div>
        
        <div class="meta-info">
            <p>{{ $statistics['generation_date'] }}</p>
        </div>
    </div>
</body>
</html>
