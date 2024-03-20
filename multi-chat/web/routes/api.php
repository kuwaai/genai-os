<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('ipCheck','auth:sanctum')->group(function () {

    Route::get('user', function (Request $request) {
        // Get the user data
        $userData = $request->user();

        // Filter the user data to include only specific fields
        $filteredUserData = [
            'name' => $userData->name,
            'email' => $userData->email,
            'group_id' => $userData->group_id,
            'email_verified_at' => $userData->email_verified_at,
            'id' => $userData->id,
            'term_accepted' => $userData->term_accepted,
            'announced' => $userData->announced,
            'created_at' => $userData->created_at,
        ];

        return $filteredUserData;
    });
});
Route::middleware('ipCheck')->get('islogin', function (Request $request) {
    return ['logged_in' => $request->user() ? true : false];
});