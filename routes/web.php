<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


// routes/web.php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProcessingController;
use App\Http\Controllers\CacheController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\FileUploadController;

Route::get('/phpinfo', function () {
    return phpinfo();
});

// Route for displaying the login page
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// Route for handling the login form submission
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Route for handling logout
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Apply the custom middleware to protect routes
Route::middleware(['check.user.session'])->group(function () {
    // Route for displaying the start page
    Route::get('/', [HomeController::class, 'showStartPage'])->name('start.page');

    // Protected routes

    // home and upload
    Route::get('/home', [HomeController::class, 'showHomePage']);
    
    Route::post('/upload', [HomeController::class, 'upload'])->name('upload');

    // // Route::post('/upload/{session_id}', [HomeController::class, 'upload'])->name('upload');
    // // Route::post('/upload/{session_id}', [UploadController::class, 'uploadFiles'])->name('upload.files');

    // Route::post('/upload', [FileUploadController::class, 'store'])->name('file.upload');


    // status page

    // Route for displaying the status page
    Route::get('/status', [ProcessingController::class, 'statusPage']);

    // Route to start processing
    Route::get('/startprocess/{sessionId}/{total}', [ProcessingController::class, 'startProcessing']);

    // Route to get processing progress
    Route::get('/progress/{sessionId}', [ProcessingController::class, 'getProgress']);

    // Route to download processed files
    Route::get('/download/{sessionId}', [ProcessingController::class, 'downloadFile']);


    // Route to create a cache key
    Route::get('/createcache/{sessionId}/{total}', [CacheController::class, 'createCache']);

    Route::get('/incrementcache/{sessionId}', [CacheController::class, 'incrementCache']);

    // Route to delete a cache key
    Route::get('/deletecache/{sessionId}', [CacheController::class, 'deleteCache']);


    // Custom prompt routes
    Route::get('/custom-prompt', [HomeController::class, 'showEditor'])->name('custom_prompt.page');
    // Route::post('/save-content', [HomeController::class, 'saveContent'])->name('custom_prompt.save');
    Route::get('/download-template', [HomeController::class, 'downloadTemplate'])->name('custom_prompt.download');

    // Route for updating the last run time
    Route::get('/update-last-run-time', [HomeController::class, 'updateLastRunTime'])->name('update.last.run.time');

});