<?php

namespace App\Actions;

use App\Models\Term;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportCsvAction
{
    /**
     * Execute CSV import for terms
     *
     * @param UploadedFile $file
     * @param array $options
     * @return array
     * @throws ValidationException
     */
    public function execute(UploadedFile $file, array $options = []): array
    {
        Log::info('ImportCsvAction started', [
            'file' => $file->getClientOriginalName(),
            'options' => $options,
        ]);

        // Validate the file
        $validator = Validator::make(['file' => $file], [
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Read CSV file
        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setHeaderOffset(0); // Use the first row as header

        $headers = $csv->getHeader();
        Log::info('CSV headers detected', ['headers' => $headers]);

        // Validate required columns
        $requiredColumns = ['term_en', 'term_ar'];
        $missingColumns = array_diff($requiredColumns, $headers);

        if (!empty($missingColumns)) {
            throw new ValidationException(
                Validator::make([], [])->errors()->add(
                    'file',
                    sprintf('Missing required columns: %s', implode(', ', $missingColumns))
                )
            );
        }

        // Process CSV rows
        $records = Statement::create()->process($csv);
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($records as $index => $record) {
            try {
                // Clean and validate row data
                $rowNumber = $index + 2; // +2 because header is row 1 and index is 0-based

                $termEn = trim($record['term_en'] ?? '');
                $termAr = trim($record['term_ar'] ?? '');

                // Skip empty rows
                if (empty($termEn) && empty($termAr)) {
                    $skippedCount++;
                    continue;
                }

                // Validate row data
                $rowValidator = Validator::make([
                    'term_en' => $termEn,
                    'term_ar' => $termAr,
                ], [
                    'term_en' => 'required|string|max:255',
                    'term_ar' => 'required|string|max:255',
                ]);

                if ($rowValidator->fails()) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => $rowValidator->errors()->all(),
                        'data' => $record,
                    ];
                    $skippedCount++;
                    continue;
                }

                // Check for duplicates if option is set
                $skipDuplicates = $options['skip_duplicates'] ?? true;
                if ($skipDuplicates) {
                    $existingTerm = Term::where('term_en', $termEn)
                        ->orWhere('term_ar', $termAr)
                        ->first();

                    if ($existingTerm) {
                        $skippedCount++;
                        continue;
                    }
                }

                // Create term
                $termData = [
                    'term_en' => $termEn,
                    'term_ar' => $termAr,
                    'status' => 'unverified',
                    'resource_page_id' => $options['resource_page_id'] ?? null,
                    'confidence_level' => $options['confidence_level'] ?? 0.8,
                ];

                // Add optional fields if present in CSV
                $optionalFields = [
                    'resource_page_id',
                    'confidence_level',
                    'x',
                    'y',
                    'width',
                    'height',
                    'rejection_reason',
                    'corrections',
                ];

                foreach ($optionalFields as $field) {
                    if (isset($record[$field]) && !empty(trim($record[$field]))) {
                        $termData[$field] = trim($record[$field]);
                    }
                }

                Term::create($termData);
                $importedCount++;

            } catch (\Exception $e) {
                Log::error('Error importing CSV row', [
                    'row' => $index + 2,
                    'error' => $e->getMessage(),
                    'data' => $record,
                ]);

                $errors[] = [
                    'row' => $index + 2,
                    'errors' => [$e->getMessage()],
                    'data' => $record,
                ];
                $skippedCount++;
            }
        }

        $result = [
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'total_rows' => count($records),
            'errors' => $errors,
            'has_errors' => !empty($errors),
        ];

        Log::info('ImportCsvAction completed', $result);

        return $result;
    }

    /**
     * Get CSV template for download
     *
     * @return string
     */
    public function getTemplate(): string
    {
        $headers = [
            'term_en',
            'term_ar',
            'resource_page_id',
            'confidence_level',
            'x',
            'y',
            'width',
            'height',
            'status',
            'rejection_reason',
            'corrections',
        ];

        $exampleData = [
            [
                'Artificial Intelligence',
                'الذكاء الاصطناعي',
                '1',
                '0.9',
                '100',
                '200',
                '300',
                '50',
                'unverified',
                '',
                '',
            ],
            [
                'Machine Learning',
                'التعلم الآلي',
                '1',
                '0.8',
                '150',
                '250',
                '350',
                '60',
                'accepted',
                '',
                'Minor spelling correction',
            ],
        ];

        $csv = implode(',', $headers) . "\n";

        foreach ($exampleData as $row) {
            $csv .= implode(',', array_map(function ($value) {
                // Escape commas and quotes
                if (strpos($value, ',') !== false || strpos($value, '"') !== false) {
                    $value = '"' . str_replace('"', '""', $value) . '"';
                }
                return $value;
            }, $row)) . "\n";
        }

        return $csv;
    }
}
