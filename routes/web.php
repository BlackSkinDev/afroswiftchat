<?php

use Illuminate\Support\Facades\Auth;
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
	//Alert::alert('Title', 'Message', 'Type');
    return view('welcome');
});

Auth::routes();


Route::group(['middleware' => 'auth'], function () {
    Route::get('/create', 'GroupController@getCreate')->name('create');
    Route::post('/create', 'GroupController@postCreate')->name('post');
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/groups', 'GroupController@getGroups')->name('groups');
    Route::get('/join/{roomId}', 'GroupController@join')->name('join');
    Route::get('/group/{group}', 'GroupController@getMessages')->name('getMessage');
    Route::post('/send/{group}', 'GroupController@sendMessage')->name('sendMessage');
    Route::get('/deletegroup/{group}', 'GroupController@delete')->name('deletegroup');
});

Route::post('/signin','HomeController@login');

Route::get('/test','GroupController@test');

Route::get('/login/google', 'Auth\LoginController@redirectToGoogle')->name('google');
Route::get('google/callback', 'Auth\LoginController@handleGoogleCallback');


 