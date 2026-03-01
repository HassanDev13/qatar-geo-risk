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
        
        .letter-header {
            background: #3498db;
            color: white;
            padding: 12px 20px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 30px 0 20px 0;
            direction: ltr;
            page-break-before: always;
        }
        
        .term-entry {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ecf0f1;
            border-radius: 5px;
            page-break-inside: avoid;
        }
        
        .english-term {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            direction: ltr;
            text-align: left;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .term-count {
            font-size: 12px;
            color: #7f8c8d;
            margin-left: 10px;
        }
        
        .arabic-list {
            margin-top: 10px;
        }
        
        .arabic-item {
            padding: 8px 12px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-right: 3px solid #3498db;
            border-radius: 3px;
        }
        
        .arabic-term {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .arabic-meta {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 4px;
        }
        
        .confidence {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            margin-left: 8px;
        }
        
        .confidence.high {
            background: #d4edda;
            color: #155724;
        }
        
        .confidence.medium {
            background: #fff3cd;
            color: #856404;
        }
        
        .confidence.low {
            background: #f8d7da;
            color: #721c24;
        }
        
        .most-common {
            border-left: 4px solid #f39c12;
            background: #fff9e6;
        }
        
        .badge-most-common {
            display: inline-block;
            background: #f39c12;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            margin-right: 8px;
            font-weight: bold;
        }
        
        .meta-row {
            margin-bottom: 4px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            font-size: 11px;
            color: #34495e;
        }
        
        .meta-item strong {
            color: #2c3e50;
        }
        
        .consistency-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .consistency-عالي {
            background: #d4edda;
            color: #155724;
        }
        
        .consistency-متوسط {
            background: #fff3cd;
            color: #856404;
        }
        
        .consistency-منخفض {
            background: #f8d7da;
            color: #721c24;
        }
        
        .sources {
            font-size: 11px;
            color: #34495e;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px dashed #ecf0f1;
        }
        
        .sources strong {
            color: #2c3e50;
        }
        
        .source-item {
            display: inline;
        }
    </style>
</head>
<body>
    <div class="letter-header">{{ $letter }}</div>
    
    @foreach($termsInLetter as $englishKey => $termData)
        <div class="term-entry">
            <div class="english-term">
                {{ $termData['english_term'] }}
                <span class="term-count">({{ $termData['total_count'] }} مرة)</span>
            </div>
            
            <div class="arabic-list">
                @foreach($termData['arabic_terms'] as $index => $arabicData)
                    <div class="arabic-item {{ $arabicData['is_most_common'] ?? false ? 'most-common' : '' }}">
                        <div class="arabic-term">
                            {{ $index + 1 }}. {{ $arabicData['term'] }}
                            @if($arabicData['is_most_common'] ?? false)
                                <span class="badge-most-common">⭐ الأكثر شيوعاً</span>
                            @endif
                        </div>
                        <div class="arabic-meta">
                            <div class="meta-row">
                                <span class="meta-item">
                                    <strong>التكرار:</strong> {{ $arabicData['count'] }}
                                </span>
                                <span class="meta-item">
                                    <strong>النسبة:</strong> {{ $arabicData['frequency'] ?? 0 }}%
                                </span>
                                <span class="confidence {{ $arabicData['confidence'] >= 8 ? 'high' : ($arabicData['confidence'] >= 6 ? 'medium' : 'low') }}">
                                    ثقة: {{ $arabicData['confidence'] }}/10
                                </span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-item">
                                    <strong>الجودة:</strong> 
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= ($arabicData['stars'] ?? 0))
                                            <span style="color:#f39c12;">★</span>
                                        @else
                                            <span style="color:#bdc3c7;">☆</span>
                                        @endif
                                    @endfor
                                    ({{ $arabicData['stars'] ?? 0 }}/5)
                                </span>
                                <span class="meta-item">
                                    <strong>الاتساق:</strong> 
                                    <span class="consistency-badge consistency-{{ $arabicData['consistency'] ?? 'منخفض' }}">
                                        {{ $arabicData['consistency'] ?? 'منخفض' }}
                                    </span>
                                </span>
                            </div>
                        </div>
                        @if(isset($arabicData['sources']) && count($arabicData['sources']) > 0)
                            <div class="sources">
                                <strong>المصادر:</strong>
                                @foreach($arabicData['sources'] as $sourceIndex => $source)
                                    <span class="source-item">
                                        {{ $source['resource_name'] }} (صفحات: {{ $source['pages'] }})@if($sourceIndex < count($arabicData['sources']) - 1), @endif
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</body>
</html>
