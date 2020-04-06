<?php

Route::group(['namespace' => 'Insowe\ArticleManager\Http\Controllers'], function(){
    Route::get('articles', 'ArticleController@index')->name('admin.articles');
    Route::get('article/{id}', 'ArticleController@form')->name('admin.article_form');
    Route::post('article/{id}', 'ArticleController@createOrUpdate');
});