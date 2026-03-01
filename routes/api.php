<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RiskPointController;

Route::get('/risk-points', [RiskPointController::class, 'index']);
Route::post('/risk-points', [RiskPointController::class, 'store']);

// Parse Google Maps links for DangerZone
Route::post('/parse-location', function (\Illuminate\Http\Request $request) {
    $url = $request->input('url');
    if (!$url) return response()->json(['error' => 'No URL provided'], 400);

    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        // 1. Check for @lat,lng
        if (preg_match('/@([-0-9\.]+),([-0-9\.]+)/', $finalUrl, $matches)) {
            return response()->json(['lat' => (float) $matches[1], 'lng' => (float) $matches[2]]);
        }
        
        // 2. Check for ll=lat,lng
        if (preg_match('/ll=([-0-9\.]+),([-0-9\.]+)/', $finalUrl, $matches)) {
            return response()->json(['lat' => (float) $matches[1], 'lng' => (float) $matches[2]]);
        }
        
        // 3. Check for place/name/lat,lng
        if (preg_match('/place\/[^\/]+\/([-0-9\.]+),([-0-9\.]+)/', $finalUrl, $matches)) {
            return response()->json(['lat' => (float) $matches[1], 'lng' => (float) $matches[2]]);
        }
        
        // 4. Check for query parameters like ?q=lat,lng or q=lat%2Clng
        if (preg_match('/q=([-0-9\.]+)(?:,|%2C)([-0-9\.]+)/', $finalUrl, $matches)) {
            return response()->json(['lat' => (float) $matches[1], 'lng' => (float) $matches[2]]);
        }

        // 5. Check for 3d=lat!4d=lng parameters
        if (preg_match('/3d([-0-9\.]+)!(?:4d|4m[-0-9\!]+4d)([-0-9\.]+)/', $finalUrl, $matches) || preg_match('/3d([-0-9\.]+)&.*4d([-0-9\.]+)/', $finalUrl, $matches)) {
            return response()->json(['lat' => (float) $matches[1], 'lng' => (float) $matches[2]]);
        }
        
        // 6. Check for simple coordinates in URL path like /maps/search/lat,lng
        if (preg_match('/search\/([-0-9\.]+),([-0-9\.]+)/', $finalUrl, $matches)) {
            return response()->json(['lat' => (float) $matches[1], 'lng' => (float) $matches[2]]);
        }

        return response()->json(['error' => 'Could not extract coordinates', 'finalUrl' => $finalUrl], 404);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
