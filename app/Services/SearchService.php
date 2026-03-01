<?php

namespace App\Services;

use App\Models\Term;
use App\Models\Resource;

class SearchService
{
    /**
     * Search for terms and return grouped results.
     */
    public function searchTerms(string $search, bool $exactMatch = false, bool $smartMode = false): array
    {
        if (empty($search)) {
            return [];
        }

        $result = $this->performSearch($search, $exactMatch, $smartMode);
        
        // If no results and it's a composition search of multiple words
        if (empty($result) && str_contains(trim($search), ' ')) {
            $words = preg_split('/\s+/', trim($search));
            $compositionResults = [];
            
            foreach ($words as $word) {
                if (mb_strlen($word) > 2) {
                    $wordRes = $this->performSearch($word, $exactMatch, $smartMode);
                    if (!empty($wordRes)) {
                        $compositionResults[$word] = $wordRes;
                    }
                }
            }
            
            if (!empty($compositionResults)) {
                return [
                    'is_composition_fallback' => true,
                    'original_query' => $search,
                    'message' => 'The exact full phrase was not found in the database. These are the results for each individual word.',
                    'word_results' => $compositionResults
                ];
            }
        }

        return $result;
    }

    private function performSearch(string $search, bool $exactMatch, bool $smartMode): array
    {

        // Get all terms matching the search (case-insensitive)
        $query = Term::query()
            ->with(['resourcePage.resource'])
            ->where(function ($q) use ($search, $exactMatch) {
                if ($exactMatch) {
                    $q->whereRaw('LOWER(term_en) = ?', [strtolower($search)])
                      ->orWhereRaw('LOWER(term_ar) = ?', [strtolower($search)]);
                } else {
                    $q->whereRaw('LOWER(term_en) LIKE ?', ['%' . strtolower($search) . '%'])
                      ->orWhereRaw('LOWER(term_ar) LIKE ?', ['%' . strtolower($search) . '%']);
                }
            });

        // Increase limit for smart mode to gather enough candidates across resources
        if ($smartMode) {
            $query->limit(1000);
        } else {
            // Default strict/small search
            $query->limit(50); 
        }

        $matchingTerms = $query->get();

        // Group terms by normalized (lowercase) values
        $groupedData = [];
        
        foreach ($matchingTerms as $term) {
            $normalizedEn = strtolower($term->term_en ?? '');
            $normalizedAr = strtolower($term->term_ar ?? '');
            
            // Create a unique key for grouping (only by normalized English term)
            $groupKey = $normalizedEn;
            
            if (!isset($groupedData[$groupKey])) {
                $groupedData[$groupKey] = [
                    'normalized_term_en' => $normalizedEn,
                    'normalized_term_ar' => $normalizedAr, // First Arabic term found
                    'display_term_en' => $term->term_en,
                    'display_term_ar' => $term->term_ar,
                    'variations' => [],
                    'total_count' => 0,
                    'resource_count' => 0,
                    'resources' => [],
                    'term_ids' => [],
                ];
            }
            
            // Add variation if not already present
            $variationKey = $term->term_en . '|' . $term->term_ar;
            if (!isset($groupedData[$groupKey]['variations'][$variationKey])) {
                $groupedData[$groupKey]['variations'][$variationKey] = [
                    'term_en' => $term->term_en,
                    'term_ar' => $term->term_ar,
                    'count' => 0,
                ];
            }
            $groupedData[$groupKey]['variations'][$variationKey]['count']++;
            
            // Track total count
            $groupedData[$groupKey]['total_count']++;
            $groupedData[$groupKey]['term_ids'][] = $term->id;
            
            // Group by resource
            if ($term->resourcePage && $term->resourcePage->resource) {
                $resourceId = $term->resourcePage->resource->id;
                $resourceName = $term->resourcePage->resource->name;
                
                if (!isset($groupedData[$groupKey]['resources'][$resourceId])) {
                    $groupedData[$groupKey]['resources'][$resourceId] = [
                        'resource_id' => $resourceId,
                        'resource_name' => $resourceName,
                        'count' => 0,
                        'term_ids' => [],
                        'arabic_term_details' => [],
                    ];
                }
                
                $groupedData[$groupKey]['resources'][$resourceId]['count']++;
                $groupedData[$groupKey]['resources'][$resourceId]['term_ids'][] = $term->id;
                
                // Track Arabic term with its specific term ID and page numbers
                if ($term->term_ar) {
                    $arTermKey = $term->term_ar;
                    if (!isset($groupedData[$groupKey]['resources'][$resourceId]['arabic_term_details'][$arTermKey])) {
                        $groupedData[$groupKey]['resources'][$resourceId]['arabic_term_details'][$arTermKey] = [
                            'arabic_term' => $term->term_ar,
                            'count' => 0,
                            'term_ids' => [],
                            'pages' => [],
                            'confidence_sum' => 0,
                        ];
                    }
                    $groupedData[$groupKey]['resources'][$resourceId]['arabic_term_details'][$arTermKey]['count']++;
                    $groupedData[$groupKey]['resources'][$resourceId]['arabic_term_details'][$arTermKey]['term_ids'][] = $term->id;
                    
                    // Track page numbers
                    $pageNumber = $term->resourcePage->page_number ?? 0;
                    if (!in_array($pageNumber, $groupedData[$groupKey]['resources'][$resourceId]['arabic_term_details'][$arTermKey]['pages'])) {
                        $groupedData[$groupKey]['resources'][$resourceId]['arabic_term_details'][$arTermKey]['pages'][] = $pageNumber;
                    }
                    
                    // Sum confidence for averaging
                    $groupedData[$groupKey]['resources'][$resourceId]['arabic_term_details'][$arTermKey]['confidence_sum'] += $term->confidence_level ?? 0;
                }
            }
        }
        
        // Convert to array and calculate resource counts
        $result = [];
        foreach ($groupedData as $group) {
            $group['variations'] = array_values($group['variations']);
            
            // Convert arabic_term_details to array and calculate statistics for each resource
            foreach ($group['resources'] as $resourceId => &$resource) {
                $arabicTermDetails = [];
                $totalCountInResource = $resource['count'];

                // SMART MODE: Limit to random 5 Arabic terms if requested
                $sourceDetails = $resource['arabic_term_details'];
                if ($smartMode && count($sourceDetails) > 5) {
                    $keys = array_keys($sourceDetails);
                    shuffle($keys);
                    $randomKeys = array_slice($keys, 0, 5);
                    $filteredDetails = [];
                    foreach ($randomKeys as $key) {
                        $filteredDetails[$key] = $sourceDetails[$key];
                    }
                    $sourceDetails = $filteredDetails;
                }
                
                foreach ($sourceDetails as $arDetail) {
                    // Calculate average confidence
                    $avgConfidence = $arDetail['count'] > 0 
                        ? round($arDetail['confidence_sum'] / $arDetail['count'], 1) 
                        : 0;
                    
                    // Sort pages
                    sort($arDetail['pages']);
                    
                    // Limit pages to save tokens
                    $pageCount = count($arDetail['pages']);
                    $displayPages = array_slice($arDetail['pages'], 0, 5); // Reduce page limit for compact prompt
                    if ($pageCount > 5) {
                        $displayPages[] = '...';
                    }
                    
                    $arabicTermDetails[] = [
                        'arabic_term' => $arDetail['arabic_term'],
                        'count' => $arDetail['count'],
                        // 'pages' => $displayPages, // Optional: hide pages in smart mode to save space? Keep for now but reduced
                        'pages' => $displayPages,
                        'confidence' => $avgConfidence,
                    ];
                }
                
                // Sort by count descending (unless randomized above, but sorting helps readability)
                usort($arabicTermDetails, function($a, $b) {
                    return $b['count'] - $a['count'];
                });
                
                // Mark most common
                if (count($arabicTermDetails) > 1 && $arabicTermDetails[0]['count'] > $arabicTermDetails[1]['count']) {
                    $arabicTermDetails[0]['is_most_common'] = true;
                }
                
                $resource['arabic_term_details'] = $arabicTermDetails;
            }
            unset($resource);

            // Calculate global stats for this term (Aggregation across all resources)
            $globalStats = [];
            foreach ($group['resources'] as $res) {
                if (!isset($res['arabic_term_details'])) continue;
                foreach ($res['arabic_term_details'] as $detail) {
                    $term = $detail['arabic_term'];
                    if (!isset($globalStats[$term])) {
                        $globalStats[$term] = [
                            'term' => $term,
                            'total_count' => 0,
                            'resource_count' => 0,
                            'resources' => []
                        ];
                    }
                    $globalStats[$term]['total_count'] += $detail['count'];
                    $globalStats[$term]['resource_count']++;
                    $globalStats[$term]['resources'][] = $res['resource_name'];
                }
            }
            
            // Sort by total count desc
            usort($globalStats, function($a, $b) {
                return $b['total_count'] - $a['total_count'];
            });
            
            $group['global_stats'] = array_values($globalStats);
            
            // Now convert resources to indexed array
            $group['resources'] = array_values($group['resources']);
            $group['resource_count'] = count($group['resources']);
            
            // Sort resources by count descending
            usort($group['resources'], function($a, $b) {
                return $b['count'] - $a['count'];
            });
            
            $result[] = $group;
        }
        
        // Sort by total count descending
        usort($result, function($a, $b) {
            return $b['total_count'] - $a['total_count'];
        });

        // SAFETY LIMIT: Keep only the top 10 most relevant term groups to avoid token overflow
        // The model doesn't need 50+ definitions of "term". 10 is plenty.
        $result = array_slice($result, 0, 10);

        // Further optimization: Limit resources per term and details inside
        foreach ($result as &$group) {
            // Keep top 5 resources max
            $group['resources'] = array_slice($group['resources'], 0, 5);
            
            foreach ($group['resources'] as &$res) {
                // Keep top 3 arabic variations per resource
                if (isset($res['arabic_term_details'])) {
                    $res['arabic_term_details'] = array_slice($res['arabic_term_details'], 0, 3);
                    
                    // Minimize page numbers in the output
                    foreach ($res['arabic_term_details'] as &$det) {
                       $det['pages'] = array_slice($det['pages'], 0, 3);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * List all resources.
     */
    public function listResources(): array
    {
        return Resource::select(['id', 'name', 'status', 'verification_status', 'created_at'])
            ->withCount('pages')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}
