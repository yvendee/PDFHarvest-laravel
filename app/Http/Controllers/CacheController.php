<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheController extends Controller
{
    // Method to create a cache key with session ID
    public function createCache(Request $request, $sessionId, $total)
    {
        $cacheKey = "processing:$sessionId";

        // Check if the cache key already exists
        $existingProgress = Cache::get($cacheKey);

        if ($existingProgress) {
            Log::info("Cache key already exists for session: $sessionId");
            return response()->json(['message' => 'Cache key already exists'], 400);
        }

        // Create and store the cache key with initial progress
        $initialProgress = ['current' => 0, 'total' => $total];
        Cache::put($cacheKey, $initialProgress, now()->addMinutes(10));

        Log::info("Cache key created for session: $sessionId with total files: $total");

        return response()->json(['message' => 'Cache key created', 'data' => $initialProgress]);
    }

    public function incrementCache(Request $request, $sessionId)
    {

        Log::info("Cache key increment for : $sessionId");
        $cacheKey = "processing:$sessionId";
    
        // Retrieve the current progress
        $progress = Cache::get($cacheKey);

        if (!$progress) {
            return response()->json(['message' => 'No processing found for this session'], 404);
        }


        // Check if 'current' is less than 'total' before incrementing
        if ($progress['current'] < $progress['total']) {
            $progress['current'] += 1;


            // Update the cache with the new progress
            Cache::put($cacheKey, $progress, now()->addMinutes(10));

            // Log the progress increment
            Log::info("Progress incremented: {$progress['current']} of {$progress['total']} for cache key: $cacheKey");

            // return response()->json($progress);
            return response()->json([
                'message' => 'Progess incremented.',
                'progress' => $progress
            ], 200);

        } else {
            // Log if trying to increment beyond the total
            Log::info("Attempted to increment but current value is already at or exceeds the total: {$progress['current']} of {$progress['total']} for cache key: $cacheKey");

            return response()->json([
                'message' => 'Cannot increment, current is equal to or exceeds total',
                'progress' => $progress
            ], 400);
        }
    }


    // Method to delete a cache key with session ID
    public function deleteCache(Request $request, $sessionId)
    {
        $cacheKey = "processing:$sessionId";

        // Check if the cache key exists
        $existingProgress = Cache::get($cacheKey);

        if (!$existingProgress) {
            Log::info("No cache key found for session: $sessionId");
            return response()->json(['message' => 'Cache key not found'], 404);
        }

        // Delete the cache key
        Cache::forget($cacheKey);

        Log::info("Cache key deleted for session: $sessionId");

        return response()->json(['message' => 'Cache key deleted']);
    }
}
