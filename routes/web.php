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

Route::get('/mysql','MysqlAffairController@buildOrder');
Route::get('/buildOrderNo','MysqlAffairController@buildOrderNo');
Route::get('/buildOrder','RedisAffairController@buildOrder');
Route::get('/redisInit','RedisAffairController@redisInit');
Route::get('/buildList','RedisAffairController@buildList');
Route::get('/redisDel','RedisAffairController@redisDel');
