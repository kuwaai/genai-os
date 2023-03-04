<?php

use App\Http\Controllers\LLMController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
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

# Admin routes, require admin permission
Route::middleware('auth', 'verified', 'isAdmin')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::delete('/LLMs/delete', [LLMController::class, 'delete'])->name('delete_LLM_by_id');
    Route::post('/LLMs/create', [LLMController::class, 'create'])->name('create_new_LLM');
    Route::patch('/LLMs/update', [LLMController::class, 'update'])->name('update_LLM_by_id');
});

# User routes, required email verified
Route::middleware('auth', 'verified')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/api', [ProfileController::class, 'renew'])->name('profile.api.renew');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/chats', function () {
        return view('chat');
    })->name('chat');

    Route::get('/chats/new/{llm_id}', function ($llm_id) {
        if (!LLMs::findOrFail($llm_id)->exists()) {
            return redirect()->route('chat');
        }
        return view('chat');
    })->name('new_chat');

    Route::post('/chats/create/{llm_id}', [ChatController::class, 'create'])->name('create_chat');
    Route::post('/chats/request/{chat_id}', [ChatController::class, 'request'])->name('request_chat');
    Route::delete('/chats/delete', [ChatController::class, 'delete'])->name('delete_chat');

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
