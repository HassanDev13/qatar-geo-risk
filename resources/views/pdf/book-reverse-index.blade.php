<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap');
        
        body {
            font-family: 'Tajawal', 'dejavu sans', sans-serif;
            direction: rtl;
            text-align: right;
            color: #2c3e50;
            line-height: 1.8;
            margin: 0;
            padding: 20px;
        }
        
        .reverse-index-container {
            page-break-before: always;
            padding: 40px;
        }
        
        .reverse-title {
            font-size: 28px;
            font-weight: 700;
            color: #3498db;
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 3px solid #3498db;
            padding-bottom: 15px;
        }
        
        .reverse-subtitle {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 40px;
            font-size: 16px;
        }
        
        .arabic-letter-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        
        .arabic-letter-header {
            background: #3498db;
            color: white;
            padding: 12px 20px;
            font-size: 24px;
            font-weight: 700;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .reverse-term-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        
        .reverse-term-item {
            padding: 10px 15px;
            margin-bottom: 8px;
            background: white;
            border-radius: 5px;
            border-right: 4px solid #3498db;
        }
        
        .arabic-term-main {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .english-equivalents {
            font-size: 14px;
            color: #7f8c8d;
            direction: ltr;
            text-align: left;
            padding-right: 20px;
        }
        
        .english-term {
            display: inline-block;
            background: #ecf0f1;
            padding: 3px 8px;
            border-radius: 3px;
            margin-left: 5px;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="reverse-index-container">
        <h1 class="reverse-title">🔄 الفهرس العكسي</h1>
        <div class="reverse-subtitle">عربي → إنجليزي | Arabic → English</div>
        
        @foreach($reverseIndex as $arabicLetter => $terms)
            <div class="arabic-letter-section">
                <div class="arabic-letter-header">{{ $arabicLetter }}</div>
                <div class="reverse-term-list">
                    @foreach($terms as $arabicTerm => $englishTerms)
                        <div class="reverse-term-item">
                            <div class="arabic-term-main">{{ $arabicTerm }}</div>
                            <div class="english-equivalents">
                                @foreach($englishTerms as $engTerm)
                                    <span class="english-term">{{ $engTerm }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
