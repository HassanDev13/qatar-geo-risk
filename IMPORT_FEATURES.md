# Import Features Documentation

## Overview

This document describes the new import features added to the system for feeding the database with terms. The system now supports two additional import methods alongside the existing OCR and PDF text extraction:

1. **CSV File Import** - Import terms from CSV files
2. **Web Scraping** - Extract terms from websites automatically

## CSV Import Features

### File Format Requirements

CSV files must have the following structure:

**Required Columns:**
- `term_en` - English term (required)
- `term_ar` - Arabic term (required)

**Optional Columns:**
- `resource_page_id` - ID of the resource page (optional)
- `confidence_level` - Confidence level (0.0 to 1.0, optional)
- `x`, `y`, `width`, `height` - Coordinates (optional)
- `status` - Term status (unverified, accepted, rejected, optional)
- `rejection_reason` - Reason for rejection (optional)
- `corrections` - Correction notes (optional)
- `source_url` - Source URL (optional)
- `source_type` - Source type (optional)

### CSV Template

A template CSV file can be downloaded from the import page. The template includes example data with all optional columns.

### Import Options

1. **Skip Duplicate Terms**: When enabled, the system will skip terms that already exist in the database (based on English or Arabic term match).
2. **Auto Save to Database**: When enabled, imported terms are automatically saved to the database.

### Import Methods

#### Method 1: Import Page
Navigate to **Admin → Import → Import Terms** to access the full import wizard with step-by-step guidance.

#### Method 2: Quick Import from Terms Table
Click the **"Import CSV"** button in the Terms table header for quick imports without leaving the terms list.

## Web Scraping Features

### Supported Extraction Methods

The web scraper supports multiple extraction methods:

1. **Auto Detect** - Automatically tries all methods to find terms
2. **Extract from Tables** - Extracts terms from HTML tables (ideal for glossaries)
3. **Extract from Lists** - Extracts terms from ordered/unordered/definition lists
4. **Extract from Glossary Sections** - Looks for glossary-specific HTML structures
5. **Extract Keywords** - Extracts capitalized terms and looks for nearby Arabic translations

### URL Configuration

- Enter the full URL of the webpage containing terms
- Use the **"Test URL"** button to verify accessibility before scraping
- The scraper uses a standard browser user-agent to avoid blocking

### Scraping Configuration

#### Custom Selectors
- **Table Selector**: Custom CSS selector for tables (e.g., `table.glossary`, `#terms-table`)
- **List Selector**: Custom CSS selector for lists (e.g., `ul.terms`, `#glossary-list`)

#### Options
- **Skip Duplicate Terms**: Skip terms that already exist in the database
- **Auto Save to Database**: Automatically save scraped terms to database

### How It Works

1. **Fetch Content**: The scraper downloads the webpage HTML content
2. **Parse HTML**: Uses Symfony DomCrawler to parse and navigate the HTML
3. **Extract Terms**: Applies the selected extraction method to find term pairs
4. **Validate & Save**: Validates extracted terms and saves them to database

## Usage Examples

### Example 1: Importing from CSV

```bash
# Sample CSV content
term_en,term_ar,confidence_level,status
Artificial Intelligence,الذكاء الاصطناعي,0.9,unverified
Machine Learning,التعلم الآلي,0.8,unverified
```

### Example 2: Web Scraping a Glossary Page

1. Navigate to **Import Terms** page
2. Select **Web Scraping** as import method
3. Enter URL: `https://example.com/glossary`
4. Select **Auto Detect** extraction method
5. Click **Start Import**

### Example 3: Quick CSV Import

1. Go to **Terms** list page
2. Click **Import CSV** button in table header
3. Select CSV file
4. Configure options
5. Click **Import**

## Error Handling

### CSV Import Errors

The system provides detailed error reporting:
- Row-by-row error messages
- Validation errors (missing required columns, invalid data)
- Duplicate detection warnings
- Import summary with success/failure counts

### Web Scraping Errors

- URL accessibility errors
- HTML parsing errors
- No terms found warnings
- Network timeout handling

## Database Schema Updates

Two new fields have been added to the `terms` table:

1. `source_url` (string, nullable) - URL where the term was sourced from
2. `source_type` (string, nullable) - Type of source (csv_import, web_scrape, pdf_ocr, etc.)

## Best Practices

### For CSV Imports
1. Always use the template for the correct column structure
2. Validate data before importing (no empty required fields)
3. Use consistent formatting for optional fields
4. Consider enabling "Skip Duplicates" for large imports

### For Web Scraping
1. Test URLs before full scraping
2. Use specific selectors for better accuracy
3. Start with "Auto Detect" and refine if needed
4. Review scraped terms before bulk imports

## Troubleshooting

### Common Issues

1. **CSV Import Fails**
   - Check file encoding (UTF-8 recommended)
   - Verify required columns exist
   - Ensure file size is under 10MB

2. **Web Scraping Returns No Terms**
   - Test URL accessibility first
   - Try different extraction methods
   - Check if page requires JavaScript (scraper doesn't execute JS)
   - Verify the page contains English-Arabic term pairs

3. **Permission Errors**
   - Ensure storage directory is writable
   - Check network permissions for web scraping

## Security Considerations

1. **File Uploads**: CSV files are validated for type and size
2. **URL Validation**: Web scraping URLs are validated before fetching
3. **Content Sanitization**: All imported data is sanitized and validated
4. **Rate Limiting**: Consider implementing rate limiting for web scraping

## Performance Tips

1. **Large CSV Files**: Process in chunks for files with thousands of rows
2. **Web Scraping**: Use specific selectors for faster extraction
3. **Database**: Index frequently searched columns (term_en, term_ar)

## API Integration

The import features can be extended with API endpoints for:
- Programmatic CSV imports
- Scheduled web scraping
- Third-party integration

## Support

For issues with import features:
1. Check the error messages in the import summary
2. Review the application logs
3. Verify file formats and URL accessibility
4. Contact system administrator for persistent issues