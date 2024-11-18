<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use ZipArchive;



class ProcessingController extends Controller
{
    public function statusPage()
    {
        $apiUrl = 'http://localhost:5000'; // The API URL to interact with the Flask app
        Log::info("Backend URL for status page: " . $apiUrl);
        return view('status.status-page', compact('apiUrl'));
        // return view('status.status-page');
    }


//     public function showStatusPage($session_id)
// {
//     $apiUrl = 'http://localhost:5000'; // The API URL to interact with the Flask app
//     Log::info("Backed URL: " . $apiUrl);
//     return view('status.status-page', compact('session_id', 'apiUrl'));
// }

    public function startProcessing(Request $request, $sessionId, $total)
    {
        $cacheKey = "processing:$sessionId";
    
        // Check if processing has already started for this session
        $existingProgress = Cache::get($cacheKey);
    
        if ($existingProgress) {
            Log::info("Processing already started for session: $sessionId");
            return response()->json(['message' => 'Processing already started'], 400);
        }
    
        Log::info("Starting processing for session: $sessionId with $total files");
    
        $initialProgress = ['current' => 0, 'total' => $total];
        Cache::put($cacheKey, $initialProgress, now()->addMinutes(10));
    
        dispatch(function () use ($cacheKey, $total) {
            $sleepDuration = 1; // Update every 5 seconds
            Log::info("Asynchronous processing started for cache key: $cacheKey");
    
            for ($i = 1; $i <= $total; $i++) {
                sleep($sleepDuration); // Sleep for 5 seconds
    
                // Update progress
                $progress = ['current' => $i, 'total' => $total];
                Cache::put($cacheKey, $progress, now()->addMinutes(10));
    
                // Log progress update
                Log::info("Progress updated: $i of $total for cache key: $cacheKey");
    
                // Print the current content of the cache key every 5 seconds
                $cacheContent = Cache::get($cacheKey);
                Log::info("Cache content at $i: " . json_encode($cacheContent));
            }
    
            // Cache::forget($cacheKey); // Remove cache entry after processing is complete
            Log::info("Processing completed for cache key: $cacheKey");
        });
    
        return response()->json(['message' => 'Processing started']);
    }
    
    public function getProgress($sessionId)
    {
        $cacheKey = "processing:$sessionId";
        $progress = Cache::get($cacheKey, ['current' => 0, 'total' => 0]); // Default values to handle cases where cache might not exist
    
        Log::info("Cache Key: $cacheKey - Progress Data: " . json_encode($progress));
    
        return response()->json($progress);
    }

    public function downloadFile($sessionId)
    {
        // Verify if the session ID is valid and that processing is complete
        $progress = Cache::get("processing:$sessionId");

        if (!$progress || $progress['current'] < $progress['total']) {
            return response()->json(['message' => 'Processing is not complete'], 400);
        }

        // Implement your file download logic
        $zip = new ZipArchive();
        $zipFileName = "files_$sessionId.zip";
        $zipFilePath = storage_path("app/$zipFileName");

        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
            return response()->json(['message' => 'Could not create ZIP file'], 500);
        }

        // Add files to the ZIP archive
        foreach (glob(storage_path('app/uploads/*')) as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}
