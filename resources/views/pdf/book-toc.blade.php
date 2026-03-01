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
        
        .toc-container {
            page-break-after: always;
            padding: 40px;
        }
        
        .toc-title {
            font-size: 28px;
            font-weight: 700;
            color: #3498db;
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 3px solid #3498db;
            padding-bottom: 15px;
        }
        
        .toc-section {
            margin-bottom: 30px;
        }
        
        .toc-section-title {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            background: #ecf0f1;
            padding: 10px 15px;
            border-radius: 5px;
        }
        
        .toc-list {
            list-style: none;
            padding: 0;
        }
        
        .toc-item {
            padding: 8px 15px;
            margin-bottom: 5px;
            border-bottom: 1px dashed #ecf0f1;
            display: flex;
            justify-content: space-between;
        }
        
        .toc-item:hover {
            background: #f8f9fa;
        }
        
        .toc-letter {
            font-weight: 600;
            color: #3498db;
        }
        
        .toc-count {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .index-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .index-letter {
            background: #3498db;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            font-size: 24px;
            font-weight: 700;
        }
        
        .index-letter:hover {
            background: #2980b9;
        }
        
        .letter-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .letter-header {
            background: #3498db;
            color: white;
            padding: 8px 15px;
            font-size: 20px;
            font-weight: 700;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .terms-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .term-entry {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 15px;
            background: white;
            border-radius: 4px;
            font-size: 13px;
            border-left: 3px solid #3498db;
            margin-bottom: 3px;
            transition: all 0.2s;
        }
        
        .term-entry:hover {
            background: #ecf0f1;
            transform: translateX(-3px);
        }
        
        .term-name {
            font-weight: 500;
            color: #2c3e50;
            direction: ltr;
            text-align: left;
            flex: 1;
        }
        
        .term-page {
            color: #3498db;
            font-size: 12px;
            font-weight: 600;
            min-width: 40px;
            text-align: left;
            padding-left: 10px;
        }
        
        .terms-grid-compact {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .term-entry-compact {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 10px;
            background: white;
            border-radius: 3px;
            font-size: 11px;
            border-left: 2px solid #3498db;
        }
        
        .term-name-compact {
            font-weight: 500;
            color: #2c3e50;
            direction: ltr;
            text-align: left;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .term-page-compact {
            color: #3498db;
            font-size: 10px;
            font-weight: 600;
            min-width: 25px;
            text-align: right;
            padding-left: 8px;
        }
    </style>
</head>
<body>
    <div class="toc-container">
        <h1 class="toc-title">📑 الفهرس - Table of Contents</h1>
        
        <div class="toc-section">
            <div class="toc-section-title">فهرس المصطلحات - Terms Index (A-Z)</div>
            
            @php
                $pageNumber = 4;
                $allTerms = [];
                foreach($alphabeticalGroups as $letter => $termsInLetter) {
                    foreach($termsInLetter as $englishTerm => $termData) {
                        $allTerms[] = [
                            'term' => $termData['english_term'],
                            'page' => $pageNumber
                        ];
                    }
                    $pageNumber++;
                }
            @endphp
            
            <div class="terms-grid-compact">
                @foreach($allTerms as $termInfo)
                    <div class="term-entry-compact">
                        <span class="term-name-compact">{{ $termInfo['term'] }}</span>
                        <span class="term-page-compact">{{ $termInfo['page'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>
