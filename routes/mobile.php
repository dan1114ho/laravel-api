<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

Route::namespace('App\Http\Controllers')->group(function () {
    Route::post('auth/login', 'AuthController@login');
    Route::post('auth/signup', 'AuthController@signup');
    Route::post('auth/login/facebook', 'AuthController@facebook');
    Route::post('auth/forgot-password', 'ResetPasswordController@forgot');

    Route::middleware(['jwt.refresh'])->group(function () {
        Route::get('auth/refresh', function () {
            return response(null, 204);
        });
    });
});

Route::namespace('App\Mobile\Controllers')->middleware(['jwt.auth', 'role:user|client|admin|superadmin'])->group(function () {
    Route::get('tours', 'TourController@index')->name('mobile.tours.index');
    Route::post('tours/{tour}/track', 'ActivityController@tour');
    Route::get('tours/{tour}', 'TourController@show')->name('mobile.tours.show');
    Route::post('stops/{stop}/track', 'ActivityController@stop');
    Route::post('device', 'DeviceController@store')->name('mobile.device.store');
});

Route::namespace('App\Http\Controllers')->middleware(['jwt.auth', 'role:user|client|admin|superadmin'])->group(function () {
    Route::get('auth/session', 'AuthController@userSession');
});
