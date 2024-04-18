<?php

use App\Events\MessageSent;
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
