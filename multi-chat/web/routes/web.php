<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\PlayController;
use App\Http\Controllers\ManageController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\LanguageMiddleware;
use App\Http\Middleware\AuthCheck;
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

    Route::get('/lang/{lang}', function ($lang) {
        session()->put('locale', $lang);
        return back();
    })->name('lang');
    
    Route::get('/IPNotAllowed', function () {
        return view('errors.IPNotAllowed');
    })->name('errors.ipnotallowed');

    # This allow other registering from other platform
    Route::post('/api/register', [ProfileController::class, 'api_register'])->name('api.register');

    Route::middleware('ipCheck')->group(function () {
        # This will auth the user token that is used to connect.
        Route::post('/v1.0/chat/completions', [ProfileController::class, 'api_auth']);
        Route::post('/v1.0/chat/abort', [ProfileController::class, 'api_abort']);
        # This will auth the server secret that is used by localhost
        Route::get('/api_stream', [ProfileController::class, 'api_stream'])->name('api.stream');

        # Admin routes, require admin permission
        Route::middleware('auth', 'verified', AdminMiddleware::class . ':tab_Dashboard', 'auth.check')->group(function () {
            Route::group(['prefix' => 'dashboard'], function () {
                Route::get('/', [DashboardController::class, 'home'])->name('dashboard.home');
                Route::middleware(AdminMiddleware::class . ':Dashboard_read_feedbacks')
                    ->post('/feedback', [DashboardController::class, 'feedback'])
                    ->name('dashboard.feedback');
                Route::middleware(AdminMiddleware::class . ':Dashboard_read_safetyguard')
                    ->prefix('safetyguard')
                    ->group(function () {
                        Route::get('/rule', [DashboardController::class, 'guard_fetch'])->name('dashboard.safetyguard.fetch');
                        Route::delete('/rule/{rule_id}', [DashboardController::class, 'guard_delete'])->name('dashboard.safetyguard.delete');
                        Route::patch('/rule/{rule_id}', [DashboardController::class, 'guard_update'])->name('dashboard.safetyguard.update');
                        Route::post('/rule', [DashboardController::class, 'guard_create'])->name('dashboard.safetyguard.create');
                    });
            });
        });

        # User routes, required email verified
        Route::middleware('auth', 'verified')->group(function () {
            Route::get('/change_password', function () {
                if (request()->user()->require_change_password){
                    return view('profile.change_password');
                }
                return redirect(route('chat.home'));
            })->name('change_password');

            Route::middleware('auth.check')->group(function () {
                Route::get('/tos', function () {
                    $user = User::find(Auth::user()->id);
                    $user->term_accepted = true;
                    $user->save();

                    return back();
                })->name('tos');

                Route::get('/announcement', function () {
                    $user = User::find(Auth::user()->id);
                    $user->announced = true;
                    $user->save();
                    return back();
                })->name('announcement');

                Route::post('/compile-verilog', [ChatController::class, 'compile_verilog'])->name('compile.verilog');

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
                        Route::post('/feedback', [ChatController::class, 'feedback'])->name('chat.feedback');

                        Route::middleware(AdminMiddleware::class . ':Chat_delete_chatroom')
                            ->delete('/delete', [ChatController::class, 'delete'])
                            ->name('chat.delete');
                        Route::middleware(AdminMiddleware::class . ':Chat_read_export_chat')
                            ->get('/share/{chat_id}', [ChatController::class, 'share'])
                            ->name('chat.share');
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

                #---Room
                Route::middleware(AdminMiddleware::class . ':tab_Room')
                    ->prefix('room')
                    ->group(function () {
                        Route::get('/', [RoomController::class, 'main'])->name('room.home');

                        Route::post('/new', [RoomController::class, 'new'])->name('room.new');
                        Route::middleware(AdminMiddleware::class . ':Room_update_new_chat')
                            ->post('/create', [RoomController::class, 'create'])
                            ->name('room.create');
                        Route::get('/{room_id}', [RoomController::class, 'main'])->name('room.chat');
                        Route::get('/abort/{room_id}', [RoomController::class, 'abort'])->name('room.abort');
                        Route::post('/edit', [RoomController::class, 'edit'])->name('room.edit');
                        Route::middleware(AdminMiddleware::class . ':Room_delete_chatroom')
                            ->delete('/delete', [RoomController::class, 'delete'])
                            ->name('room.delete');
                        Route::middleware(AdminMiddleware::class . ':Room_update_send_message')
                            ->post('/request', [RoomController::class, 'request'])
                            ->name('room.request');
                        Route::middleware(AdminMiddleware::class . ':Room_update_import_chat')
                            ->post('/import', [RoomController::class, 'import'])
                            ->name('room.import');
                        Route::middleware(AdminMiddleware::class . ':Room_read_export_chat')
                            ->get('/share/{room_id}', [RoomController::class, 'share'])
                            ->name('room.share');
                    })
                    ->name('room');

                #---Play
                Route::middleware(AdminMiddleware::class . ':tab_Play')
                    ->prefix('play')
                    ->group(function () {
                        Route::get('/', function () {
                            return view('play');
                        })->name('play.home');
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
        });
    });

    require __DIR__ . '/auth.php';
});
