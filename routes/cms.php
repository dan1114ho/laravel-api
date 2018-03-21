<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

// --------------------------------------------------------------------------
// GUEST ROUTES
Route::post('auth/login', 'Auth\CmsAuthController@login');
Route::post('auth/signup', 'Auth\CmsAuthController@signup');

// --------------------------------------------------------------------------
// PROTECTED ROUTES
Route::middleware(['jwt.auth', 'role:business|admin'])->group(function () {
    Route::get('auth/session', 'Auth\CmsAuthController@userSession');
});

Route::middleware(['jwt.refresh', 'role:business|admin'])->group(function () {
    Route::get('auth/refresh', function () {
        return response(null, 204);
    });
});
