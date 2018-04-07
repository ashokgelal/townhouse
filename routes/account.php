<?php

// access route names as tenant.account.profile.* e.g. tenant.account.profile.edit
// access route path as account/profile/* e.g. account/profile/edit
Route::group(['prefix' => 'profile'], function () {
    Route::get('/', 'ProfileController@edit')->name('profile.edit'); // show profile edit form
    Route::patch('/', 'ProfileController@update')->name('profile.update'); // edit profile
});
