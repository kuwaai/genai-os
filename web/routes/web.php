<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\DuelController;
use App\Http\Controllers\PlayController;
use App\Http\Controllers\ManageController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\LanguageMiddleware;
use BeyondCode\LaravelSSE\Facades\SSE;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Chats;
use App\Models\User;
use App\Models\LLMs;

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

Route::middleware(LanguageMiddleware::class)->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('/');

    Route::get('/lang', function () {
        session()->put('locale', session()->get('locale') ? (session()->get('locale') == 'en_us' ? 'zh_tw' : 'en_us') : 'en_us');

        return back();
    })->name('lang');

    Route::get('/announcement', function () {
        session()->put('announcement', hash('sha256', \App\Models\SystemSetting::where('key', 'announcement')->first()->value));

        return back();
    })->name('announcement');

    # This will auth the user token that is used to connect.
    Route::post('/v1.0/chat/completions', [ProfileController::class, 'api_auth']);
    # This will auth the server secret that is used by localhost
    Route::get('/api_stream', [ProfileController::class, 'api_stream'])->name('api.stream');
    # Debugging, test hashing API
    Route::post('/api/register', [ProfileController::class, 'api_register'])->name('api.register');

    # Admin routes, require admin permission
    Route::middleware('auth', 'verified', AdminMiddleware::class . ':tab_Dashboard')->group(function () {
        Route::group(['prefix' => 'dashboard'], function () {
            Route::get('/', [DashboardController::class, 'home'])->name('dashboard.home');
            Route::post('/feedback', [DashboardController::class, 'feedback'])->name('dashboard.feedback');
        });
    });

    # User routes, required email verified
    Route::middleware('auth', 'verified')->group(function () {
        Route::get('/tos', function () {
            $user = User::find(Auth::user()->id);
            $user->term_accepted = true;
            $user->save();

            return back();
        })->name('tos');

        #---Profiles
        Route::middleware(AdminMiddleware::class . ':tab_Profile')
            ->prefix('profile')
            ->group(function () {
                Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');

                Route::middleware(AdminMiddleware::class . ':Profile_update_api_token')
                    ->patch('/api', [ProfileController::class, 'renew'])
                    ->name('profile.api.renew');
                Route::middleware(AdminMiddleware::class . ':Profile_update_openai_token')
                    ->patch('/chatgpt/api', [ProfileController::class, 'chatgpt_update'])
                    ->name('profile.chatgpt.api.update');

                Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
                Route::middleware(AdminMiddleware::class . ':Profile_delete_account')
                    ->delete('/', [ProfileController::class, 'destroy'])
                    ->name('profile.destroy');
            });

        #---Chats
        Route::middleware(AdminMiddleware::class . ':tab_Chat')
            ->prefix('chats')
            ->group(function () {
                Route::get('/', [ChatController::class, 'home'])->name('chat.home');
                Route::get('/translate/{history_id}', [ChatController::class, 'translate'])->name('chat.translate');

                Route::get('/new/{llm_id}', [ChatController::class, 'new_chat'])->name('chat.new');

                Route::get('/chain', [ChatController::class, 'update_chain'])->name('chat.chain');
                Route::get('/stream', [ChatController::class, 'SSE'])->name('chat.sse');
                Route::get('/{chat_id}', [ChatController::class, 'main'])->name('chat.chat');
                Route::get('/abort/{chat_id}', [ChatController::class, 'abort'])->name('chat.abort');

                Route::middleware(AdminMiddleware::class . ':Chat_update_upload_file')
                    ->post('/upload', [ChatController::class, 'upload'])
                    ->name('chat.upload');
                Route::middleware(AdminMiddleware::class . ':Chat_update_import_chat')
                    ->post('/import', [ChatController::class, 'import'])
                    ->name('chat.import');
                Route::middleware(AdminMiddleware::class . ':Chat_update_new_chat')
                    ->post('/create', [ChatController::class, 'create'])
                    ->name('chat.create');
                Route::middleware(AdminMiddleware::class . ':Chat_update_send_message')
                    ->post('/request', [ChatController::class, 'request'])
                    ->name('chat.request');
                Route::post('/edit', [ChatController::class, 'edit'])->name('chat.edit');
                Route::post('/feedback', [ChatController::class, 'feedback'])
                    ->name('chat.feedback');

                Route::middleware(AdminMiddleware::class . ':Chat_delete_chatroom')
                    ->delete('/delete', [ChatController::class, 'delete'])
                    ->name('chat.delete');
            })
            ->name('chat');

        #---Archives
        Route::middleware(AdminMiddleware::class . ':tab_Archive')
            ->prefix('archive')
            ->group(function () {
                Route::get('/', function () {
                    return view('archive');
                })->name('archive.home');

                Route::get('/{chat_id}', [ArchiveController::class, 'main'])->name('archive.chat');
                Route::post('/edit', [ArchiveController::class, 'edit'])->name('archive.edit');
                Route::delete('/delete', [ArchiveController::class, 'delete'])->name('archive.delete');
            })
            ->name('archive');

        #---Duel
        Route::middleware(AdminMiddleware::class . ':tab_Duel')
            ->prefix('duel')
            ->group(function () {
                Route::get('/', [DuelController::class, 'main'])->name('duel.home');

                Route::post('/new', [DuelController::class, 'new'])->name('duel.new');
                Route::middleware(AdminMiddleware::class . ':Duel_update_new_chat')
                    ->post('/create', [DuelController::class, 'create'])
                    ->name('duel.create');
                Route::get('/{duel_id}', [DuelController::class, 'main'])->name('duel.chat');
                Route::get('/abort/{duel_id}', [DuelController::class, 'abort'])->name('duel.abort');
                Route::post('/edit', [DuelController::class, 'edit'])->name('duel.edit');
                Route::middleware(AdminMiddleware::class . ':Duel_delete_chatroom')
                    ->delete('/delete', [DuelController::class, 'delete'])
                    ->name('duel.delete');
                Route::middleware(AdminMiddleware::class . ':Duel_update_send_message')
                    ->post('/request', [DuelController::class, 'request'])
                    ->name('duel.request');
                Route::middleware(AdminMiddleware::class . ':Duel_update_import_chat')
                    ->post('/import', [DuelController::class, 'import'])
                    ->name('duel.import');
            })
            ->name('duel');

        #---Play
        Route::middleware(AdminMiddleware::class . ':tab_Play')
            ->prefix('play')
            ->group(function () {
                Route::get('/', function () {
                    return view('play');
                })->name('play.home');

                Route::prefix('ai_election')
                    ->group(function () {
                        Route::get('/', [PlayController::class, 'play'])->name('play.ai_elections.home');
                        Route::patch('/update', [PlayController::class, 'update'])->name('play.ai_elections.update');
                    })
                    ->name('play.ai_elections');

                Route::middleware(AdminMiddleware::class . ':tab_Chat')
                    ->prefix('bots')
                    ->group(function () {
                        Route::get('/', [BotController::class, 'home'])->name('play.bots.home');

                        Route::get('/new/{llm_id}', [BotController::class, 'new_chat'])->name('play.bots.new');

                        Route::get('/chain', [BotController::class, 'update_chain'])->name('play.bots.chain');
                        Route::get('/stream', [BotController::class, 'SSE'])->name('play.bots.sse');
                        Route::get('/{chat_id}', [BotController::class, 'main'])->name('play.bots.chat');

                        Route::middleware(AdminMiddleware::class . ':Chat_update_upload_file')
                            ->post('/upload', [BotController::class, 'upload'])
                            ->name('play.bots.upload');
                        Route::middleware(AdminMiddleware::class . ':Chat_update_new_chat')
                            ->post('/create', [BotController::class, 'create'])
                            ->name('play.bots.create');
                        Route::middleware(AdminMiddleware::class . ':Chat_update_send_message')
                            ->post('/request', [BotController::class, 'request'])
                            ->name('play.bots.request');
                        Route::post('/edit', [BotController::class, 'edit'])->name('play.bots.edit');
                        Route::middleware(AdminMiddleware::class . ':Chat_update_feedback')
                            ->post('/feedback', [BotController::class, 'feedback'])
                            ->name('play.bots.feedback');

                        Route::middleware(AdminMiddleware::class . ':Chat_delete_chatroom')
                            ->delete('/delete', [BotController::class, 'delete'])
                            ->name('play.bots.delete');
                    })
                    ->name('play.bots');
            })
            ->name('play');
        #---Play
        Route::middleware(AdminMiddleware::class . ':tab_Manage')
            ->prefix('manage')
            ->group(function () {
                Route::get('/', function () {
                    return view('manage.home');
                })->name('manage.home');

                Route::prefix('group')
                    ->group(function () {
                        Route::post('/create', [ManageController::class, 'group_create'])->name('manage.group.create');
                        Route::patch('/update', [ManageController::class, 'group_update'])->name('manage.group.update');
                        Route::delete('/delete', [ManageController::class, 'group_delete'])->name('manage.group.delete');
                    })
                    ->name('manage.group');

                Route::prefix('user')
                    ->group(function () {
                        Route::post('/create', [ManageController::class, 'user_create'])->name('manage.user.create');
                        Route::patch('/update', [ManageController::class, 'user_update'])->name('manage.user.update');
                        Route::delete('/delete', [ManageController::class, 'user_delete'])->name('manage.user.delete');
                        Route::post('/search', [ManageController::class, 'search_user'])->name('manage.user.search');
                    })
                    ->name('manage.user');

                Route::prefix('setting')
                    ->group(function () {
                        Route::get('/resetRedis', [SystemController::class, 'ResetRedis'])->name('manage.setting.resetRedis');
                        Route::patch('/update', [SystemController::class, 'update'])->name('manage.setting.update');
                    })
                    ->name('manage.user');

                Route::prefix('LLMs')
                    ->group(function () {
                        Route::get('/toggle/{llm_id}', [ManageController::class, 'llm_toggle'])->name('manage.llms.toggle');
                        Route::delete('/delete', [ManageController::class, 'llm_delete'])->name('manage.llms.delete');
                        Route::post('/create', [ManageController::class, 'llm_create'])->name('manage.llms.create');
                        Route::patch('/update', [ManageController::class, 'llm_update'])->name('manage.llms.update');
                    })
                    ->name('manage.llms');

                Route::post('/tab', [ManageController::class, 'tab'])->name('manage.tab');
            })
            ->name('play');
    });

    require __DIR__ . '/auth.php';
});
