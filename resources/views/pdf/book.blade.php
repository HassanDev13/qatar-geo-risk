<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            margin: 15mm;
        }
        
        body {
            font-family: 'dejavu sans', sans-serif;
            direction: rtl;
            text-align: right;
            color: #2c3e50;
            line-height: 1.6;
        }
        
        /* Cover Page */
        .cover {
            text-align: center;
            padding: 60px 20px;
            border: 3px solid #3498db;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            page-break-after: always;
            margin-bottom: 30px;
        }
        
        .cover h1 {
            font-size: 36px;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .cover .subtitle {
            font-size: 18px;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 40px;
            text-align: center;
        }
        
        .stat-box {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* Table of Contents */
        .toc {
            page-break-after: always;
            padding: 20px;
        }
        
        .toc h2 {
            color: #3498db;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .toc-item {
            padding: 8px 0;
            border-bottom: 1px dotted #bdc3c7;
        }
        
        /* Main Content */
        .alphabet-section {
            page-break-before: always;
            margin-bottom: 30px;
        }
        
        .alphabet-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
            direction: ltr;
        }
        
        .term-group {
            margin-bottom: 25px;
            padding: 20px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }
        
        .term-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        .term-en {
            font-weight: bold;
            font-size: 22px;
            color: #2c3e50;
            direction: ltr;
            text-align: left;
        }
        
        .term-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .meta-badge {
            background: #ecf0f1;
            padding: 4px 10px;
            border-radius: 12px;
            white-space: nowrap;
        }
        
        .meta-badge.primary {
            background: #3498db;
            color: white;
        }
        
        .arabic-translations {
            margin-top: 15px;
        }
        
        .arabic-item {
            margin-bottom: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-right: 4px solid #3498db;
            border-radius: 5px;
        }
        
        .arabic-term {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .arabic-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 8px;
            font-size: 11px;
        }
        
        .detail-item {
            background: white;
            padding: 6px 10px;
            border-radius: 4px;
            text-align: center;
        }
        
        .detail-label {
            color: #7f8c8d;
            display: block;
            margin-bottom: 3px;
        }
        
        .detail-value {
            font-weight: bold;
            color: #2c3e50;
            font-size: 13px;
        }
        
        .detail-value.good {
            color: #27ae60;
        }
        
        .detail-value.warning {
            color: #f39c12;
        }
        
        .detail-value.bad {
            color: #e74c3c;
        }
        
        .sources-list {
            margin-top: 10px;
            padding: 8px;
            background: #fff3cd;
            border-radius: 4px;
            font-size: 11px;
        }
        
        .sources-list strong {
            color: #856404;
        }
        
        .source-tag {
            display: inline-block;
            background: #ffc107;
            color: #000;
            padding: 2px 8px;
            border-radius: 3px;
            margin: 2px;
            font-size: 10px;
        }
        
        .status-indicators {
            margin-top: 8px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .status-badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-badge.accepted {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-badge.rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Footer */
        .page-footer {
            position: fixed;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #ecf0f1;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Cover Page -->
    <div class="cover">
        <h1>📚 قاموس المصطلحات التخصصي</h1>
        <h1 style="direction: ltr;">Specialized Terms Dictionary</h1>
        <div class="subtitle">معجم شامل للمصطلحات الإنجليزية والعربية</div>
        
        <div class="stats-grid">
            <div class="stat-box">
                <span class="stat-number">{{ number_format($statistics['total_terms']) }}</span>
                <span class="stat-label">إجمالي المصطلحات</span>
            </div>
            <div class="stat-box">
                <span class="stat-number">{{ number_format($statistics['unique_english_terms']) }}</span>
                <span class="stat-label">مصطلحات إنجليزية فريدة</span>
            </div>
            <div class="stat-box">
                <span class="stat-number">{{ number_format($statistics['unique_arabic_terms']) }}</span>
                <span class="stat-label">ترجمات عربية فريدة</span>
            </div>
            <div class="stat-box">
                <span class="stat-number">{{ $statistics['avg_confidence'] }}%</span>
                <span class="stat-label">متوسط الثقة</span>
            </div>
        </div>
        
        <div style="margin-top: 50px; font-size: 14px;">
            <p>تاريخ الإنشاء: {{ $statistics['generation_date'] }}</p>
            <p>النمط: {{ $statistics['mode'] === 'test' ? 'تجريبي' : 'إنتاجي' }}</p>
        </div>
    </div>

    <!-- Main Content -->
    @php
        $currentLetter = '';
        $alphabeticalGroups = $groupedTerms->groupBy(function($item, $key) {
            return strtoupper(substr($key, 0, 1));
        });
    @endphp

    @foreach($alphabeticalGroups as $letter => $termsInLetter)
        <div class="alphabet-section">
            <div class="alphabet-header">{{ $letter }}</div>
            
            @foreach($termsInLetter as $englishKey => $termData)
                <div class="term-group">
                    <div class="term-header">
                        <div class="term-en">{{ $termData['english_term'] }}</div>
                        <div class="term-meta">
                            <span class="meta-badge primary">{{ $termData['total_occurrences'] }} مرة</span>
                            <span class="meta-badge">{{ $termData['unique_arabic_translations'] }} ترجمة</span>
                        </div>
                    </div>
                    
                    <div class="arabic-translations">
                        @foreach($termData['arabic_terms'] as $index => $arabicData)
                            <div class="arabic-item">
                                <div class="arabic-term">
                                    {{ $index + 1 }}. {{ $arabicData['term'] }}
                                </div>
                                
                                <div class="arabic-details">
                                    <div class="detail-item">
                                        <span class="detail-label">التكرار</span>
                                        <span class="detail-value primary">{{ $arabicData['count'] }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">مستوى الثقة</span>
                                        <span class="detail-value {{ $arabicData['avg_confidence'] >= 80 ? 'good' : ($arabicData['avg_confidence'] >= 60 ? 'warning' : 'bad') }}">
                                            {{ $arabicData['avg_confidence'] }}%
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">التقييمات</span>
                                        <span class="detail-value">
                                            <span style="color: #27ae60;">👍 {{ $arabicData['total_positive_feedback'] }}</span>
                                            <span style="color: #e74c3c;">👎 {{ $arabicData['total_negative_feedback'] }}</span>
                                        </span>
                                    </div>
                                </div>
                                
                                @if(!empty($arabicData['sources']))
                                    <div class="sources-list">
                                        <strong>المصادر:</strong>
                                        @foreach($arabicData['sources'] as $source)
                                            <span class="source-tag">{{ $source }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                
                                @if(!empty($arabicData['status_breakdown']))
                                    <div class="status-indicators">
                                        @foreach($arabicData['status_breakdown'] as $status => $count)
                                            <span class="status-badge {{ $status }}">
                                                {{ $status }}: {{ $count }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="page-footer">
        Generated by Term Extractor System | {{ $statistics['generation_date'] }}
    </div>
</body>
</html>
