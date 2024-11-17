<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UploadController extends Controller
{
    public function uploadFiles(Request $request, $session_id)
    {
        $request->validate([
            'files.*' => 'required|file|mimes:pdf|max:2048', // Adjust as needed
        ]);

        $responses = [];

        foreach ($request->file('files') as $file) {
            $response = Http::attach('files[]', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post("http://localhost:5000/upload/{$session_id}");

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to upload to Flask'], 500);
            }

            $responses[] = $response->json();
        }

        return response()->json($responses);
    }
}
