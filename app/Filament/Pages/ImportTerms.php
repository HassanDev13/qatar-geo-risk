<?php

namespace App\Filament\Pages;

use App\Actions\ImportCsvAction;
use App\Actions\ScrapeWebAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use UnitEnum;

class ImportTerms extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = "heroicon-o-arrow-up-tray";

    protected string $view = "filament.pages.import-terms";

    protected static string|UnitEnum|null $navigationGroup = "Import";

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        //
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Wizard::make([
                    \Filament\Forms\Components\Wizard\Step::make(
                        "Import Method",
                    )
                        ->description("Choose how to import terms")
                        ->schema([
                            Select::make("import_method")
                                ->label("Import Method")
                                ->options([
                                    "csv" => "CSV File Import",
                                    "web" => "Web Scraping",
                                ])
                                ->required()
                                ->live()
                                ->afterStateUpdated(
                                    fn($state) => $this->dispatch(
                                        "import-method-changed",
                                        method: $state,
                                    ),
                                ),
                        ]),

                    \Filament\Forms\Components\Wizard\Step::make("CSV Import")
                        ->description("Upload and configure CSV import")
                        ->schema([
                            \Filament\Forms\Components\Section::make(
                                "CSV File Upload",
                            )
                                ->description(
                                    "Upload a CSV file containing terms",
                                )
                                ->schema([
                                    FileUpload::make("csv_file")
                                        ->label("CSV File")
                                        ->acceptedFileTypes([
                                            "text/csv",
                                            "text/plain",
                                        ])
                                        ->maxSize(10240) // 10MB
                                        ->required()
                                        ->helperText(
                                            "Upload a CSV file with columns: term_en, term_ar (required), and optional columns: resource_page_id, confidence_level, x, y, width, height, status, rejection_reason, corrections",
                                        )
                                        ->downloadable()
                                        ->helperText(
                                            "Upload a CSV file with columns: term_en, term_ar (required), and optional columns: resource_page_id, confidence_level, x, y, width, height, status, rejection_reason, corrections. You can download a template CSV file using the button below.",
                                        ),
                                ]),

                            \Filament\Forms\Components\Section::make(
                                "Import Options",
                            )->schema([
                                Toggle::make("skip_duplicates")
                                    ->label("Skip Duplicate Terms")
                                    ->default(true)
                                    ->helperText(
                                        "Skip terms that already exist in the database",
                                    ),

                                Toggle::make("auto_save")
                                    ->label("Auto Save to Database")
                                    ->default(true)
                                    ->helperText(
                                        "Automatically save imported terms to database",
                                    ),
                            ]),

                            \Filament\Forms\Components\Actions::make([
                                \Filament\Forms\Components\Actions\Action::make(
                                    "downloadTemplate",
                                )
                                    ->label("Download CSV Template")
                                    ->color("gray")
                                    ->action(function () {
                                        $csvAction = new ImportCsvAction();
                                        $csvContent = $csvAction->getTemplate();

                                        return response()->streamDownload(
                                            function () use ($csvContent) {
                                                echo $csvContent;
                                            },
                                            "terms_template.csv",
                                            [
                                                "Content-Type" => "text/csv",
                                            ],
                                        );
                                    }),
                            ]),
                        ])
                        ->visible(fn($get) => $get("import_method") === "csv"),

                    \Filament\Forms\Components\Wizard\Step::make("Web Scraping")
                        ->description("Configure web scraping settings")
                        ->schema([
                            \Filament\Forms\Components\Section::make(
                                "URL Configuration",
                            )
                                ->description("Enter the URL to scrape")
                                ->schema([
                                    TextInput::make("url")
                                        ->label("Website URL")
                                        ->required()
                                        ->url()
                                        ->placeholder(
                                            "https://example.com/glossary",
                                        )
                                        ->helperText(
                                            "Enter the URL of the webpage containing terms",
                                        ),

                                    Action::make("testUrl")
                                        ->label("Test URL")
                                        ->color("gray")
                                        ->action(function ($state, $set) {
                                            $url = $this->data["url"] ?? "";
                                            if (empty($url)) {
                                                Notification::make()
                                                    ->title("URL Required")
                                                    ->body(
                                                        "Please enter a URL first",
                                                    )
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }

                                            try {
                                                $scrapeAction = new ScrapeWebAction();
                                                $result = $scrapeAction->testUrl(
                                                    $url,
                                                );

                                                if ($result["accessible"]) {
                                                    Notification::make()
                                                        ->title(
                                                            "URL Accessible",
                                                        )
                                                        ->body(
                                                            "Successfully connected to {$url}. Status: {$result["status_code"]}",
                                                        )
                                                        ->success()
                                                        ->send();
                                                } else {
                                                    Notification::make()
                                                        ->title(
                                                            "URL Not Accessible",
                                                        )
                                                        ->body(
                                                            "Failed to access {$url}. Error: {$result["error"]}",
                                                        )
                                                        ->danger()
                                                        ->send();
                                                }
                                            } catch (\Exception $e) {
                                                Notification::make()
                                                    ->title("Test Failed")
                                                    ->body($e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),
                                ]),

                            \Filament\Forms\Components\Section::make(
                                "Scraping Configuration",
                            )->schema([
                                Select::make("extraction_method")
                                    ->label("Extraction Method")
                                    ->options([
                                        "auto" => "Auto Detect",
                                        "table" => "Extract from Tables",
                                        "list" => "Extract from Lists",
                                        "glossary" =>
                                            "Extract from Glossary Sections",
                                        "keywords" => "Extract Keywords",
                                    ])
                                    ->default("auto")
                                    ->helperText(
                                        "Choose how to extract terms from the webpage",
                                    ),

                                TextInput::make("table_selector")
                                    ->label("Custom Table Selector")
                                    ->placeholder("table.glossary-table")
                                    ->helperText(
                                        "CSS selector for tables (e.g., table.glossary, #terms-table)",
                                    )
                                    ->visible(
                                        fn($get) => $get(
                                            "extraction_method",
                                        ) === "table",
                                    ),

                                TextInput::make("list_selector")
                                    ->label("Custom List Selector")
                                    ->placeholder("ul.terms-list")
                                    ->helperText(
                                        "CSS selector for lists (e.g., ul.terms, #glossary-list)",
                                    )
                                    ->visible(
                                        fn($get) => $get(
                                            "extraction_method",
                                        ) === "list",
                                    ),

                                Toggle::make("skip_duplicates")
                                    ->label("Skip Duplicate Terms")
                                    ->default(true)
                                    ->helperText(
                                        "Skip terms that already exist in the database",
                                    ),

                                Toggle::make("auto_save")
                                    ->label("Auto Save to Database")
                                    ->default(true)
                                    ->helperText(
                                        "Automatically save scraped terms to database",
                                    ),
                            ]),
                        ])
                        ->visible(fn($get) => $get("import_method") === "web"),

                    \Filament\Forms\Components\Wizard\Step::make(
                        "Review & Import",
                    )
                        ->description("Review and start the import process")
                        ->schema([
                            \Filament\Forms\Components\Section::make(
                                "Import Summary",
                            )
                                ->description("Review your import settings")
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make(
                                        "method_summary",
                                    )
                                        ->label("Import Method")
                                        ->state(
                                            fn($get) => match (
                                                $get("import_method")
                                            ) {
                                                "csv" => "CSV File Import",
                                                "web" => "Web Scraping from: " .
                                                    ($get("url") ?? "N/A"),
                                                default => "Not selected",
                                            },
                                        ),

                                    \Filament\Infolists\Components\TextEntry::make(
                                        "options_summary",
                                    )
                                        ->label("Options")
                                        ->state(function ($get) {
                                            $options = [];
                                            if (
                                                $get("skip_duplicates") ?? true
                                            ) {
                                                $options[] = "Skip duplicates";
                                            }
                                            if ($get("auto_save") ?? true) {
                                                $options[] = "Auto save";
                                            }
                                            return implode(", ", $options) ?:
                                                "No options selected";
                                        }),
                                ]),
                        ]),
                ])
                    ->submitAction(
                        Action::make("import")
                            ->label("Start Import")
                            ->action(fn() => $this->import()),
                    )
                    ->cancelAction(
                        Action::make("cancel")
                            ->label("Cancel")
                            ->color("gray")
                            ->action(
                                fn() => redirect()->route(
                                    "filament.admin.pages.dashboard",
                                ),
                            ),
                    ),
            ])
            ->statePath("data");
    }

    public function import(): void
    {
        $data = $this->form->getState();
        $method = $data["import_method"] ?? null;

        try {
            if ($method === "csv") {
                $this->importCsv($data);
            } elseif ($method === "web") {
                $this->scrapeWeb($data);
            } else {
                throw new \Exception("No import method selected");
            }
        } catch (\Exception $e) {
            Log::error("Import failed", [
                "method" => $method,
                "error" => $e->getMessage(),
                "data" => $data,
            ]);

            Notification::make()
                ->title("Import Failed")
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function importCsv(array $data): void
    {
        $file = $data["csv_file"] ?? null;

        if (!$file || !($file instanceof UploadedFile)) {
            throw new \Exception("No CSV file uploaded");
        }

        $options = [
            "skip_duplicates" => $data["skip_duplicates"] ?? true,
            "auto_save" => $data["auto_save"] ?? true,
        ];

        $csvAction = new ImportCsvAction();
        $result = $csvAction->execute($file, $options);

        if ($result["has_errors"]) {
            $errorMessage = "Import completed with errors:\n";
            foreach ($result["errors"] as $error) {
                $errorMessage .=
                    "Row {$error["row"]}: " .
                    implode(", ", $error["errors"]) .
                    "\n";
            }

            Notification::make()
                ->title("Import Completed with Errors")
                ->body(
                    "Imported: {$result["imported"]}, Skipped: {$result["skipped"]}, Total: {$result["total_rows"]}",
                )
                ->danger()
                ->send();

            // Show detailed errors in notification
            if (count($result["errors"]) <= 5) {
                Notification::make()
                    ->title("Import Errors")
                    ->body($errorMessage)
                    ->danger()
                    ->send();
            }
        } else {
            Notification::make()
                ->title("Import Successful")
                ->body(
                    "Successfully imported {$result["imported"]} terms. Skipped {$result["skipped"]} duplicates/errors.",
                )
                ->success()
                ->send();
        }
    }

    private function scrapeWeb(array $data): void
    {
        $url = $data["url"] ?? "";

        if (empty($url)) {
            throw new \Exception("No URL provided");
        }

        $options = [
            "extraction_method" => $data["extraction_method"] ?? "auto",
            "table_selector" => $data["table_selector"] ?? null,
            "list_selector" => $data["list_selector"] ?? null,
            "skip_duplicates" => $data["skip_duplicates"] ?? true,
            "auto_save" => $data["auto_save"] ?? true,
        ];

        $scrapeAction = new ScrapeWebAction();
        $result = $scrapeAction->execute($url, $options);

        Notification::make()
            ->title("Web Scraping Completed")
            ->body(
                "Found {$result["total_terms_found"]} terms, processed {$result["terms_processed"]} terms.",
            )
            ->success()
            ->send();

        // Show preview of scraped terms
        if (!empty($result["terms"])) {
            $preview = "Scraped terms preview:\n";
            $count = min(5, count($result["terms"]));

            for ($i = 0; $i < $count; $i++) {
                $term = $result["terms"][$i];
                $preview .= "• {$term["term_en"]} -> {$term["term_ar"]}\n";
            }

            if (count($result["terms"]) > 5) {
                $preview .=
                    "... and " . (count($result["terms"]) - 5) . " more";
            }

            Notification::make()
                ->title("Scraped Terms Preview")
                ->body($preview)
                ->info()
                ->send();
        }
    }

    public static function getNavigationLabel(): string
    {
        return "Import Terms";
    }

    public function getTitle(): string
    {
        return "Import Terms";
    }

    public function getHeading(): string
    {
        return "Import Terms";
    }

    public function getSubheading(): string
    {
        return "Import terms from CSV files or scrape from websites";
    }
}
