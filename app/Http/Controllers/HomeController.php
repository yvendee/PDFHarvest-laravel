<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


use Illuminate\Support\Facades\Cache;

use ZipArchive;

class HomeController extends Controller
{

    public function showHomePage()
    {   
        $session_id = bin2hex(random_bytes(16)); // Generate session ID
        // $apiUrl = env('API_URL'); // Access the API URL from the .env file
        // $apiUrl = config('api.url'); // Access the API URL from the config
        $apiUrl = 'http://localhost:5000'; // Hardcoded API URL

        Log::info("Backed URL: " .  $apiUrl );
        return view('home.home-page', compact('session_id', 'apiUrl'));
    }

    public function showStartPage()
    {
        $lastRunTimePath = 'last_run_time.txt';

        if (Storage::exists($lastRunTimePath)) {
            $lastRunTime = Storage::get($lastRunTimePath);
            $lastRunTime = Carbon::parse($lastRunTime);
            $currentTime = Carbon::now();
            $timeDifference = $currentTime->diff($lastRunTime);

            // Convert the difference to human-readable format
            if ($timeDifference->days > 0) {
                $time_label = $timeDifference->days . ' day' . ($timeDifference->days > 1 ? 's' : '') . ' ago';
            } elseif ($timeDifference->h > 0) {
                $time_label = $timeDifference->h . ' hour' . ($timeDifference->h > 1 ? 's' : '') . ' ago';
            } elseif ($timeDifference->i > 0) {
                $time_label = $timeDifference->i . ' minute' . ($timeDifference->i > 1 ? 's' : '') . ' ago';
            } else {
                $time_label = 'Just now';
            }
        } else {
            $time_label = '-';
        }

        return view('start.start-page', compact('time_label'));
    }

    public function updateLastRunTime()
    {
        $time = now(); // Current time
        Storage::put('last_run_time.txt', $time); // Store time in storage
    }

    // home page

    public function logout()
    {
        // Add logout logic here
        // Example: Auth::logout();
        return redirect('/login'); // Redirect to login page after logout
    }

    public function customPrompt()
    {
        // Add custom prompt logic here
        return view('custom-prompt');
    }

    public function upload(Request $request)
    {
        try {
            $uploadedFiles = [];
            foreach ($request->file('files') as $file) {
                $filename = $file->getClientOriginalName();
                $file->storeAs('uploads', $filename); // Save file to storage/app/uploads
                $uploadedFiles[] = $filename;
            }

            $session_id = bin2hex(random_bytes(16)); // Generate session ID

            $totalFiles = count($uploadedFiles);

            $response = [
                'message' => 'Files successfully uploaded',
                'files' => $uploadedFiles,
                'session_id' => $session_id,
                'total_files' => $totalFiles,
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function upload()
    // {
    //     try{

    //         // Generate or retrieve session_id (for example, from the session)
    //         $session_id = bin2hex(random_bytes(16)); // Generate session ID

    //         // Pass session_id to the view
    //         return view('home.home-page', compact('session_id'));
    //     } 
    //     catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //      }
        
    // }


    // custom_prompt editor

    public function showEditor()
    {
        // // Define the file path relative to the 'public' disk
        // $filePath = 'custom-prompt.txt'; // relative to storage/app/public
    
        // // Use the 'public' disk to check existence and get content
        // $defaultContent = Storage::disk('public')->exists($filePath) 
        //     ? Storage::disk('public')->get($filePath) 
        //     : '';
    
        // return view('custom_prompt.custom_prompt-page', ['default_content' => $defaultContent]);
        return view('custom_prompt.custom_prompt-page');
    }

    // public function saveContent(Request $request)
    // {
    //     // \Log::info('Save Content Request:', $request->all()); 
        
    //     $request->validate([
    //         'content' => 'required|string',
    //     ]);
    
    //     $filePath = 'custom-prompt.txt'; // relative to storage/app/public
    //     Storage::disk('public')->put($filePath, $request->input('content'));
    
    //     return response()->json(['message' => 'Saved Successfully']);
    // }

    public function downloadTemplate()
    {
        $filePath = public_path('storage/custom-prompt-template.txt'); // Absolute path to the file
    
        if (file_exists($filePath)) {
            // Manually set the MIME type as plain text
            $headers = [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="custom-prompt-template.txt"',
            ];
    
            return response()->download($filePath, 'custom-prompt-template.txt', $headers);
        } else {
            return response()->json(['message' => 'File not found'], 404);
        }
    }

}