<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::auth();

Route::get('/home', 'HomeController@index');

Route::get('/net/', ['as'=>'net.index', 'uses'=>'NetController@index']);
Route::get('/net/create', ['as'=>'net.create', 'uses'=>'NetController@create']);
Route::post('/net/store', ['as'=>'net.store', 'uses'=>'NetController@store']);
Route::get('/net/edit/{id}', ['as'=>'net.edit', 'uses'=>'NetController@edit']);
Route::post('/net/simulate', ['as'=>'net.simulate', 'uses'=>'NetController@simulate']);

Route::post('/state/store', ['as'=>'state.store', 'uses'=>'StateController@store']);
Route::get('/state/load/{net_id}', ['as'=>'state.load', 'uses'=>'StateController@load']);
