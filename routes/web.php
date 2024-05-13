<?php

use App\Events\MessageSent;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

Route::get('/', function () {
    return view('welcome');
});
Route::get('chat', function() {
    return view('chat.index');
});
Route::post('message', function(Request $request) {
    Log::info($request->messages);
    broadcast(new MessageSent(auth()->user(), $request->messages));
    return $request->messages;
});

Route::post('login/{id}', function($id) {
    Auth::loginUsingId($id);
    return back();
});

Route::get('logout', function() {
    Auth::logout();
    return back();
});
Route::get('sad', function() {
    dd(extension_loaded('redis'));
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('forgot.password.get');
Route::post('forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetPost'])->name('forgot.password.post');
Route::get('forgot-passworddddd/{token}', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetGet'])->name('password.reset_update');
Route::put('forgot-password/{token}', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetPut'])->name('forgot.password.update');
Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/home');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::get('ad', function() {
    $user = User::find(11);
    dd($user->markEmailAsVerified());
})->middleware(['auth', 'verified']);
