<?php

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

Route::get('/media/{path}/{img}', function ($path, $img) {    
    $file = public_path() . "/storage/$path/$img";
    return response(File::get($file), 200)->header('Content-Type', mime_content_type($file));
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::post('plan/payment', 'PaymentController@paymentResponse');
Route::post('institute/mobile/payment', 'Api\MobilePayController@payUupdate');
Route::post('subadmin/plan/payment', 'Api\SubAdminPaymentController@payUupdate');

