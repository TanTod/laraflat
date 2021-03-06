<?php
    Route::get('icons' , function(){
        return view('admin.layout.static.icons');
    });

    Route::get('docs' , function(){
        return view('vendor.apidoc.index');
    });

    #### user control
    Route::get('user' , 'UserController@index');
    Route::get('user/item/{id?}' , 'UserController@show');
    Route::post('user/item/{id?}' , 'UserController@store');
    Route::get('user/{id}/delete' , 'UserController@destroy');
    Route::get('user/{id}/view' , 'UserController@getById');

    #### group control
    Route::get('group' , 'GroupController@index');
    Route::get('group/item/{id?}' , 'GroupController@show');
    Route::post('group/item/{id?}' , 'GroupController@store');
    Route::get('group/{id}/delete' , 'GroupController@destroy');
    Route::get('group/{id}/view' , 'GroupController@getById');
    #### role control
    Route::get('role' , 'RoleController@index');
    Route::get('role/item/{id?}' , 'RoleController@show');
    Route::post('role/item/{id?}' , 'RoleController@store');
    Route::get('role/{id}/delete' , 'RoleController@destroy');
    Route::get('role/{id}/view' , 'RoleController@getById');
    #### permission control
    Route::get('permission' , 'PermissionController@index');
    Route::get('permission/item/{id?}' , 'PermissionController@show');
    Route::post('permission/item/{id?}' , 'PermissionController@store');
    Route::get('permission/{id}/delete' , 'PermissionController@destroy');
    Route::get('permission/{id}/view' , 'PermissionController@getById');

    #### home control
    Route::get('home' , 'HomeController@index');
