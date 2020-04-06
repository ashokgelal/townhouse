<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->namespace('App\Http\Controllers')
    ->group(function () {


//non-auth, non-api web routes for tenants



    });



Route::middleware(['web', 'auth'])
    ->namespace('App\Http\Controllers')
    ->group(function () {

        // access route names as tenant.account.profile.* e.g. tenant.account.profile.edit
        // access route path as account/profile/* e.g. account/profile/edit
        Route::group(['prefix' => 'profile'], function () {
            Route::get('/', 'ProfileController@edit')->name('profile.edit'); // show profile edit form
            Route::patch('/', 'ProfileController@update')->name('profile.update'); // edit profile
        });


    });
