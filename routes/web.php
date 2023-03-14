<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LLMController;
use App\Http\Controllers\apiController;
use BeyondCode\LaravelSSE\Facades\SSE;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\LLMs;
use App\Models\Chats;
use \Symfony\Component\HttpFoundation\StreamedResponse;

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
Route::get('/chats/stream', [ChatController::class, "SSE"])->name('chat_sse');

# Admin routes, require admin permission
Route::middleware('auth', 'verified', 'isAdmin')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::delete('/LLMs/delete', [LLMController::class, 'delete'])->name('delete_LLM_by_id');
    Route::post('/LLMs/create', [LLMController::class, 'create'])->name('create_new_LLM');
    Route::patch('/LLMs/update', [LLMController::class, 'update'])->name('update_LLM_by_id');
});

#Route::post('/api/verifyToken', [apiController::class, 'verifyToken']);
#Route::post('/api/createRecord', [apiController::class, 'createRecord']);

# User routes, required email verified
Route::middleware('auth', 'verified')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/api', [ProfileController::class, 'renew'])->name('profile.api.renew');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    # Just for the chat page, no chat opened
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
    Route::post('/chats/create', [ChatController::class, 'create'])->name('create_chat');
    # Continue chatting by POST to this route
    Route::post('/chats/request', [ChatController::class, 'request'])->name('request_chat');
    # Edit chat name
    Route::post('/chats/edit', [ChatController::class, 'edit'])->name('edit_chat');
    # Delete chat by this route
    Route::delete('/chats/delete', [ChatController::class, 'delete'])->name('delete_chat');
    # SSE Listener for listen to generated texts

    # Access to a chat's histories by this route
    Route::get('/chats/{chat_id}', function ($chat_id) {
        if (
            !Chats::findOrFail($chat_id)
                ->where('user_id', request()->user()->id)
                ->exists()
        ) {
            return redirect()->route('chat');
        }
        return view('chat');
    })->name('chats');
});

require __DIR__ . '/auth.php';
