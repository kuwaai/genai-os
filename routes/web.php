<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LLMController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\SystemController;
use BeyondCode\LaravelSSE\Facades\SSE;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\LLMs;
use App\Models\Chats;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('/');
Route::get('/chats/stream', [ChatController::class, 'SSE'])->middleware('suppress_errors')->name('chat_sse');

# Admin routes, require admin permission
Route::middleware('auth', 'verified', 'isAdmin')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/resetRedis', [ChatController::class, 'ResetRedis'])->name('reset_redis');
    Route::get('/LLMs/toggle/{llm_id}', [LLMController::class, 'toggle'])->name('toggle_LLM');

    Route::delete('/LLMs/delete', [LLMController::class, 'delete'])->name('delete_LLM_by_id');
    Route::post('/LLMs/create', [LLMController::class, 'create'])->name('create_new_LLM');
    Route::patch('/LLMs/update', [LLMController::class, 'update'])->name('update_LLM_by_id');

    Route::patch('/System/update', [SystemController::class, 'update'])->name('System.update');
});

# User routes, required email verified
Route::middleware('auth', 'verified')->group(function () {
    #---Profiles
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/api', [ProfileController::class, 'renew'])->name('profile.api.renew');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    #---Chats
    Route::get('/chats', function () {
        return view('chat');
    })->name('chat');

    # Setup the create chat parameters
    Route::get('/chats/new/{llm_id}', function ($llm_id) {
        if (!LLMs::findOrFail($llm_id)->exists()) {
            return redirect()->route('chat');
        }
        return view('chat');
    })->name('new_chat');

    # Create initial chat
    Route::post('/chats/create', [ChatController::class, 'create'])->name('chat_create_chat');
    # Continue chatting by POST to this route
    Route::post('/chats/request', [ChatController::class, 'request'])->name('chat_request_chat');
    # Edit chat name
    Route::post('/chats/edit', [ChatController::class, 'edit'])->name('chat_edit_chat');
    # Delete chat by this route
    Route::delete('/chats/delete', [ChatController::class, 'delete'])->name('chat_delete_chat');
    # SSE Listener for listen to generated texts

    # Access to a chat's histories by this route
    Route::get('/chats/{chat_id}',[ChatController::class, 'main'])->name('chats');
    
    #---Archives
    Route::get('/archive', function () {
        return view('archive');
    })->name('archive');
    # Access to a chat's histories by this route
    Route::get('/archives/{chat_id}', [ArchiveController::class, 'main'])->name('archives');
    # Edit chat name
    Route::post('/archives/edit', [ArchiveController::class, 'edit'])->name('archive_edit_chat');
    # Delete chat by this route
    Route::delete('/archives/delete', [ArchiveController::class, 'delete'])->name('archive_delete_chat');
});

require __DIR__ . '/auth.php';
