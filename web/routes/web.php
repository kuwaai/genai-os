<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LLMController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\DuelController;
use App\Http\Controllers\ElectionController;
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

# Admin routes, require admin permission
Route::middleware('auth', 'verified', 'isAdmin')->group(function () {
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('/', function () {
            return view('dashboard');
        })->name('dashboard.home');
        Route::get('/resetRedis', [ChatController::class, 'ResetRedis'])->name('dashboard.resetRedis');
        Route::patch('/update', [SystemController::class, 'update'])->name('dashboard.update');

        Route::group(['prefix' => 'LLMs'], function () {
            Route::get('/toggle/{llm_id}', [LLMController::class, 'toggle'])->name('dashboard.llms.toggle');
            Route::delete('/delete', [LLMController::class, 'delete'])->name('dashboard.llms.delete');
            Route::post('/create', [LLMController::class, 'create'])->name('dashboard.llms.create');
            Route::patch('/update', [LLMController::class, 'update'])->name('dashboard.llms.update');
        });
    });
});

# User routes, required email verified
Route::middleware('auth', 'verified')->group(function () {
    #---Profiles
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');

        Route::patch('/api', [ProfileController::class, 'renew'])->name('profile.api.renew');
        Route::patch('/chatgpt/api', [ProfileController::class, 'chatgpt_update'])->name('profile.chatgpt.api.update');

        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    #---Chats
    Route::group(['prefix' => 'chats'], function () {
        Route::get('/', function () {
            return view('chat');
        })->name('chat.home');

        Route::get('/new/{llm_id}', function ($llm_id) {
            if (!LLMs::findOrFail($llm_id)->exists()) {
                return redirect()->route('chat');
            }
            return view('chat');
        })->name('chat.new');
        Route::get('/stream', [ChatController::class, 'SSE'])->name('chat.sse');
        Route::get('/{chat_id}', [ChatController::class, 'main'])->name('chat.chat');
        Route::post('/create', [ChatController::class, 'create'])->name('chat.create');
        Route::post('/request', [ChatController::class, 'request'])->name('chat.request');
        Route::post('/edit', [ChatController::class, 'edit'])->name('chat.edit');
        Route::delete('/delete', [ChatController::class, 'delete'])->name('chat.delete');
    })->name('chat');

    #---Archives
    Route::group(['prefix' => 'archive'], function () {
        Route::get('/', function () {
            return view('archive');
        })->name('archive.home');

        Route::get('/{chat_id}', [ArchiveController::class, 'main'])->name('archive.chat');
        Route::post('/edit', [ArchiveController::class, 'edit'])->name('archive.edit');
        Route::delete('/delete', [ArchiveController::class, 'delete'])->name('archive.delete');
    })->name('archive');

    #---Duel
    Route::prefix('duel')
        ->group(function () {
            Route::get('/', [DuelController::class, 'main'])->name('duel.home');

            Route::post('/create', [DuelController::class, 'create'])->name('duel.create');
            Route::get('/{duel_id}', [DuelController::class, 'main'])->name('duel.chat');
            Route::post('/edit', [DuelController::class, 'edit'])->name('duel.edit');
            Route::delete('/delete', [DuelController::class, 'delete'])->name('duel.delete');
            Route::post('/request', [DuelController::class, 'request'])->name('duel.request');
        })
        ->name('duel');

    #---Play
    Route::prefix('play')
        ->group(function () {
            Route::get('/', function () {
                return view('play');
            })->name('play.home');

            Route::prefix('ai_election')
                ->group(function () {
                    Route::get('/', [ElectionController::class, 'home'])->name('play.ai_elections.home');
                })
                ->name('play.ai_elections');
        })
        ->name('play');
});

require __DIR__ . '/auth.php';
