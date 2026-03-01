<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiskPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RiskPointController extends Controller
{
    public function index()
    {
        // Return only approved risk points for the map
        $points = RiskPoint::where('status', 'approved')->get();
        return response()->json($points);
    }

    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'description' => 'required|string|max:1000',
            'image' => 'nullable|image|max:5120', // Max 5MB
        ]);

        // Parse location from URL
        $coords = $this->parseCoordinates($request->input('url'));
        if (!$coords) {
            return response()->json(['error' => 'Could not extract coordinates from the provided URL.'], 400);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('risk_points', 'public');
        }

        $riskPoint = RiskPoint::create([
            'name' => 'User Submitted Zone',
            'lat' => $coords['lat'],
            'lng' => $coords['lng'],
            'radius' => 5000, // Default 5km radius for user submissions
            'status' => 'pending',
            'description' => $request->input('description'),
            'image' => $imagePath,
            'submitted_by_ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Risk zone submitted successfully and is pending approval.',
            'data' => $riskPoint
        ], 201);
    }

    private function parseCoordinates($url)
    {
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

            if (preg_match('/@([-0-9\.]+),([-0-9\.]+)/', $finalUrl, $matches)) {
                return ['lat' => (float) $matches[1], 'lng' => (float) $matches[2]];
            }
            if (preg_match('/ll=([-0-9\.]+),([-0-9\.]+)/', $finalUrl, $matches)) {
                return ['lat' => (float) $matches[1], 'lng' => (float) $matches[2]];
            }
            if (preg_match('/place\/[^\/]+\/([-0-9\.]+),([-0-9\.]+)/', $finalUrl, $matches)) {
                return ['lat' => (float) $matches[1], 'lng' => (float) $matches[2]];
            }
            if (preg_match('/q=([-0-9\.]+)(?:,|%2C)([-0-9\.]+)/', $finalUrl, $matches)) {
                return ['lat' => (float) $matches[1], 'lng' => (float) $matches[2]];
            }
            if (preg_match('/3d([-0-9\.]+)!(?:4d|4m[-0-9\!]+4d)([-0-9\.]+)/', $finalUrl, $matches) || preg_match('/3d([-0-9\.]+)&.*4d([-0-9\.]+)/', $finalUrl, $matches)) {
                return ['lat' => (float) $matches[1], 'lng' => (float) $matches[2]];
            }
            if (preg_match('/search\/([-0-9\.]+),([-0-9\.]+)/', $finalUrl, $matches)) {
                return ['lat' => (float) $matches[1], 'lng' => (float) $matches[2]];
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
