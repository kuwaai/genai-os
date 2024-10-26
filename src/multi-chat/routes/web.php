<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ManageController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CloudController;
use App\Http\Controllers\KernelController;
use App\Http\Controllers\WorkerController;
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

    Route::get('/storage/pdfs/{any}', function ($any) {
        return redirect("/storage/homes/{$any}");
    })->where('any', '.*');

    Route::get('/storage/homes/{any}', function ($any) {
        return redirect("/storage/root/homes/{$any}");
    })->where('any', '.*');

    Route::post('/api/register', [ProfileController::class, 'api_register'])->name('api.register');

    Route::middleware('ipCheck')->group(function () {
        # This will auth the user token that is used to connect.
        Route::post('/v1.0/chat/completions', [ProfileController::class, 'api_auth']);
        Route::post('/v1.0/chat/abort', [ProfileController::class, 'api_abort']);
        # This will auth the server secret that is used by localhost
        Route::get('/api_stream', [ProfileController::class, 'api_stream'])->name('api.stream');

        # User API routes
        Route::prefix('api/user')->group(function () {
            Route::prefix('upload')->group(function () {
                Route::post('/file', [ProfileController::class, 'api_upload_file'])->name('api.user.upload.file');
            });

            Route::prefix('create')->group(function () {
                Route::post('/base_model', [ManageController::class, 'api_create_base_model'])->name('api.user.create.base_model');
                Route::post('/bot', [BotController::class, 'api_create_bot'])->name('api.user.create.bot');
                Route::post('/room', [RoomController::class, 'api_create_room'])->name('api.user.create.room');
            });

            Route::prefix('read')->group(function () {
                Route::get('/rooms', [RoomController::class, 'api_read_rooms'])->name('api.user.read.rooms');
                Route::get('/models', [ManageController::class, 'api_read_models'])->name('api.user.read.models');
                Route::get('/bots', [BotController::class, 'api_read_bots'])->name('api.user.read.bots');
                Route::get('/cloud/{paths?}', [CloudController::class, 'api_read_cloud'])
                    ->where('paths', '.*')
                    ->name('api.user.read.cloud');
            });

            Route::prefix('delete')->group(function () {
                Route::delete('/cloud/{paths?}', [CloudController::class, 'api_delete_cloud'])
                    ->where('paths', '.*')
                    ->name('api.user.delete.cloud');
                Route::prefix('room')->group(function () {
                    Route::delete('/', [RoomController::class, 'api_delete_room'])->name('api.user.delete.room');
                });
            });
        });

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
                if (request()->user()->require_change_password) {
                    return view('profile.change_password');
                }
                return redirect()->route('room.home');
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
                        Route::middleware(AdminMiddleware::class . ':Profile_update_external_api_token')
                            ->patch('/chatgpt/api', [ProfileController::class, 'openai_update'])
                            ->name('profile.chatgpt.api.update');
                        Route::middleware(AdminMiddleware::class . ':Profile_update_external_api_token')
                            ->patch('/google/api', [ProfileController::class, 'google_update'])
                            ->name('profile.google.api.update');
                        Route::middleware(AdminMiddleware::class . ':Profile_update_external_api_token')
                            ->patch('/third-party/api', [ProfileController::class, 'third_party_update'])
                            ->name('profile.third_party.api.update');

                        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
                        Route::middleware(AdminMiddleware::class . ':Profile_delete_account')
                            ->delete('/', [ProfileController::class, 'destroy'])
                            ->name('profile.destroy');
                    });
                #---Archives, disabled, should be updated like inspecter or just deleted and replaced by export all data button
                /*Route::middleware(AdminMiddleware::class . ':tab_Archive')
            ->prefix('archive')
            ->group(function () {
                Route::get('/', function () {
                    return view('archive');
                })->name('archive.home');

                Route::get('/{chat_id}', [ArchiveController::class, 'main'])->name('archive.chat');
                Route::post('/edit', [ArchiveController::class, 'edit'])->name('archive.edit');
                Route::delete('/delete', [ArchiveController::class, 'delete'])->name('archive.delete');
            })
            ->name('archive');*/

                #---Room
                Route::middleware(AdminMiddleware::class . ':tab_Room')
                    ->prefix('room')
                    ->group(function () {
                        Route::get('/', [RoomController::class, 'home'])->name('room.home');

                        Route::post('/new', [RoomController::class, 'new'])->name('room.new');
                        Route::middleware(AdminMiddleware::class . ':Room_update_new_chat')
                            ->post('/create', [RoomController::class, 'create'])
                            ->name('room.create');
                        Route::get('/stream', [ChatController::class, 'SSE'])->name('room.sse');
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
                        Route::middleware(AdminMiddleware::class . ':Room_read_export_chat')
                            ->get('/share/{room_id}/pdf', [RoomController::class, 'export_to_pdf'])
                            ->name('room.export_pdf');
                        Route::middleware(AdminMiddleware::class . ':Room_read_export_chat')
                            ->get('/share/{room_id}/doc', [RoomController::class, 'export_to_doc'])
                            ->name('room.export_doc');
                        Route::get('/translate/{history_id}', [ChatController::class, 'translate'])->name('room.translate');

                        Route::get('/chain', [ChatController::class, 'update_chain'])->name('room.chain');
                        Route::post('/feedback', [ChatController::class, 'feedback'])->name('room.feedback');
                        Route::get('/{room_id}', [RoomController::class, 'chat_room'])->name('room.chat');
                    })
                    ->name('room');

                #---Store
                Route::middleware(AdminMiddleware::class . ':tab_Store')
                    ->prefix('store')
                    ->group(function () {
                        Route::get('/', [BotController::class, 'home'])->name('store.home');
                        Route::middleware([AdminMiddleware::class . ':tab_Manage,Store_create_community_bot,Store_create_group_bot,Store_create_private_bot'])
                            ->post('/create', [BotController::class, 'create'])
                            ->name('store.create');
                        Route::middleware(AdminMiddleware::class . ':Store_update_modify_bot')
                            ->patch('/update', [BotController::class, 'update'])
                            ->name('store.update');
                        Route::middleware(AdminMiddleware::class . ':Store_delete_delete_bot')
                            ->delete('/delete', [BotController::class, 'delete'])
                            ->name('store.delete');
                    })
                    ->name('store');
                #---Cloud
                Route::middleware(AdminMiddleware::class . ':tab_Cloud')
                    ->prefix('cloud')
                    ->group(function () {
                        Route::get('/', [CloudController::class, 'home'])->name('cloud.home');
                    });
                #---Manage
                Route::middleware(AdminMiddleware::class . ':tab_Manage')
                    ->prefix('manage')
                    ->group(function () {
                        Route::get('/', function () {
                            return view('manage.home');
                        })->name('manage.home');
                        Route::get('/setup', function () {
                            return view('manage.setup');
                        })->name('manage.setup');
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
                                Route::get('/updateWeb', [SystemController::class, 'updateWeb'])->name('manage.setting.updateWeb');
                                Route::post('/sendUpdateInput', [SystemController::class, 'sendUpdateInput'])->name('manage.setting.sendUpdateInput');
                                Route::post('/checkUpdate', [SystemController::class, 'checkUpdate'])->name('manage.setting.checkUpdate');
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

                        Route::prefix('kernel')
                            ->group(function () {
                                Route::prefix('record')->group(function () {
                                    Route::get('/fetch-data', [KernelController::class, 'fetchData'])->name('manage.kernel.record.fetchData');
                                    Route::post('/update-data', [KernelController::class, 'updateData'])->name('manage.kernel.record.updateData');
                                    Route::post('/delete-data', [KernelController::class, 'deleteData'])->name('manage.kernel.record.deleteData');
                                    Route::post('/shutdown', [KernelController::class, 'shutdown'])->name('manage.kernel.record.shutdown');
                                    Route::post('/update-field', [KernelController::class, 'updateField'])->name('manage.kernel.record.updateField');
                                    Route::post('/create-data', [KernelController::class, 'createData'])->name('manage.kernel.record.createData');
                                });
                                Route::prefix('storage')->group(function () {
                                    Route::get('/', [KernelController::class, 'storage'])->name('manage.kernel.storage');
                                    Route::get('/jobs', [KernelController::class, 'storage_job'])->name('manage.kernel.storage.jobs');
                                    Route::get('/download', [KernelController::class, 'storage_download'])->name('manage.kernel.storage.download');
                                    Route::post('/abort', [KernelController::class, 'storage_abort'])->name('manage.kernel.storage.abort');
                                    Route::post('/remove', [KernelController::class, 'storage_remove'])->name('manage.kernel.storage.remove');
                                    Route::post('/hf_login', [KernelController::class, 'storage_hf_login'])->name('manage.kernel.storage.hf_login');
                                    Route::post('/hf_logout', [KernelController::class, 'storage_hf_logout'])->name('manage.kernel.storage.hf_logout');
                                });
                            });

                        Route::prefix('workers')
                            ->group(function () {
                                Route::post('/start', [WorkerController::class, 'start'])->name('manage.workers.start');
                                Route::post('/stop', [WorkerController::class, 'stop'])->name('manage.workers.stop');
                                Route::get('/get', [WorkerController::class, 'get'])->name('manage.workers.get');
                            })
                            ->name('manage.workers');

                        Route::post('/tab', [ManageController::class, 'tab'])->name('manage.tab');
                    });
            });
        });
    });

    require __DIR__ . '/auth.php';
});
