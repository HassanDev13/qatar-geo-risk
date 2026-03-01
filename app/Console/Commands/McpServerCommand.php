<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SearchService;

class McpServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcp:server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the Model Context Protocol server';

    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        parent::__construct();
        $this->searchService = $searchService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $stdin = fopen('php://stdin', 'r');
        
        while (!feof($stdin)) {
            $line = fgets($stdin);
            if (!$line) continue;

            $request = json_decode($line, true);
            if (!$request || !isset($request['jsonrpc'])) continue;

            $response = $this->handleRequest($request);
            
            if ($response) {
                // MCP format: Each message is a line of JSON
                $this->output->write(json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
            }
        }
    }

    private function handleRequest(array $request)
    {
        $id = $request['id'] ?? null;
        $method = $request['method'] ?? null;

        if (!$method) return null;

        try {
            switch ($method) {
                case 'initialize':
                    return [
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'result' => [
                            'protocolVersion' => '1.0',
                            'capabilities' => [
                                'tools' => [
                                    'listChanged' => false
                                ]
                            ],
                            'serverInfo' => [
                                'name' => 'laravel-mcp-server',
                                'version' => '1.0.0'
                            ]
                        ]
                    ];
                
                case 'notifications/initialized':
                    return null; // Notification, no response needed

                case 'tools/list':
                    return [
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'result' => [
                            'tools' => [
                                [
                                    'name' => 'search_terms',
                                    'description' => 'Search for terms in the database. Returns grouped terms with resource information.',
                                    'inputSchema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'query' => [
                                                'type' => 'string',
                                                'description' => 'The search query for terms (English or Arabic)'
                                            ],
                                            'exact_match' => [
                                                'type' => 'boolean',
                                                'description' => 'Set to true for exact match only, false for partial match (default)',
                                            ],
                                            'smart_mode' => [
                                                'type' => 'boolean',
                                                'description' => 'Enable smart mode to get random/diverse sampling of terms per resource instead of all matches.',
                                            ]
                                        ],
                                        'required' => ['query']
                                    ]
                                ],
                                [
                                    'name' => 'list_resources',
                                    'description' => 'List all available resources with their status and page counts.',
                                    'inputSchema' => [
                                        'type' => 'object',
                                        'properties' => (object)[],
                                    ]
                                ]
                            ]
                        ]
                    ];

                case 'tools/call':
                    $params = $request['params'] ?? [];
                    $name = $params['name'] ?? '';
                    $args = $params['arguments'] ?? [];

                    $result = match($name) {
                        'search_terms' => $this->toolSearchTerms($args),
                        'list_resources' => $this->toolListResources(),
                        default => throw new \Exception("Unknown tool: $name")
                    };

                    return [
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'result' => [
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                ]
                            ]
                        ]
                    ];
                
                default:
                    return null; // Ignore unknown methods
            }
        } catch (\Exception $e) {
            // Log error to stderr to avoid breaking protocol
            file_put_contents('php://stderr', "[Error] " . $e->getMessage() . "\n");
            
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32000,
                    'message' => $e->getMessage()
                ]
            ];
        }
    }

    private function toolSearchTerms(array $args)
    {
        $search = $args['query'] ?? '';
        $exactMatch = $args['exact_match'] ?? false;
        $smartMode = $args['smart_mode'] ?? false;
        
        // Force loose match if smart mode is on
        $exactMatch = $smartMode ? false : $exactMatch;
        
        return $this->searchService->searchTerms($search, $exactMatch, $smartMode);
    }

    private function toolListResources()
    {
        return $this->searchService->listResources();
    }
}
