<?php
require_once __DIR__.'/visitor.php';
require_once __DIR__.'/auth.php';
Route::group(['prefix' => 'admin' , 'namespace' => 'Admin' , 'middleware' => ['auth','admin' , 'permissions']] , function() {
    require_once __DIR__ . '/admin.php';
});
require_once __DIR__.'/errors.php';






