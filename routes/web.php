<?php

use App\Http\Controllers\LLMController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
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

Route::get('/', function () {
    return view('welcome');
})->name("/");

Route::middleware('auth')->group(function () {
    # Admin routes
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'isAdmin'])->name('dashboard');

    Route::delete('/LLMs/delete', [LLMController::class, "delete"])->name("delete_LLM_by_id");
    Route::post('/LLMs/create', [LLMController::class, "create"])->name("create_new_LLM");
    Route::patch('/LLMs/update', [LLMController::class, "update"])->name("update_LLM_by_id");

    # User routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/api', [ProfileController::class, 'renew'])->name('profile.api.renew');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/chat', function () {
        return view('chat');
    })->name('chat');

});

require __DIR__.'/auth.php';
