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

Route::get('//', 'ImageUploadController@viewUploadForm');

Route::post('/upload-image', 'ImageUploadController@uploadImage');

Route::get('/view-image', 'ImageUploadController@getImage')->name('view-image');

Route::get('/upload-error', 'ImageUploadController@getImageUploadError')->name('upload-error');
