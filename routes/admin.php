<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['jwt.auth', 'role:superadmin|admin'])->group(function () {
    Route::get('session', 'AuthController@userSession');

    Route::get('clients', 'Admin\ClientController@index')->name('admin.clients.index');
    Route::post('clients', 'Admin\ClientController@store')->name('admin.clients.store');
    Route::get('clients/{client}', 'Admin\ClientController@show')->name('admin.clients.show');
    Route::patch('clients/{client}', 'Admin\ClientController@update')->name('admin.clients.update');
    Route::delete('clients/{client}', 'Admin\ClientController@destroy')->name('admin.clients.destroy');

    Route::get('users', 'Admin\MobileUserController@index')->name('admin.users.index');
    Route::post('users', 'Admin\MobileUserController@store')->name('admin.users.store');
    Route::get('users/{user}', 'Admin\MobileUserController@show')->name('admin.users.show');
    Route::patch('users/{user}', 'Admin\MobileUserController@update')->name('admin.users.update');
    Route::delete('users/{user}', 'Admin\MobileUserController@destroy')->name('admin.users.destroy');

    Route::get('admins', 'Admin\AdminController@index')->name('admin.admins.index');
    Route::post('admins', 'Admin\AdminController@store')->name('admin.admins.store')->middleware(['can:create,App\Admin']);
    Route::get('admins/{admin}', 'Admin\AdminController@show')->name('admin.admins.show');
    Route::patch('admins/{admin}', 'Admin\AdminController@update')->name('admin.admins.update')->middleware(['can:update,admin']);
    Route::delete('admins/{admin}', 'Admin\AdminController@destroy')->name('admin.admins.destroy')->middleware(['can:delete,admin']);

    Route::resource('tours', 'Admin\TourController', ['as' => 'admin']);
    Route::put('tours/{tour}/media', 'Admin\TourController@uploadMedia')->name('admin.tours.media');
    // Route::resource('tours/{tour}/stops', 'Admin\StopController', ['as' => 'admin']);
    // Route::put('tours/{tour}/stops/{stop}/order', 'Admin\StopController@changeOrder')->name('admin.stops.order');
    // Route::put('tours/{tour}/stops/{stop}', 'Admin\StopController@uploadMedia')->name('admin.stops.media');
});
