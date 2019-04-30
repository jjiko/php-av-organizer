<?php
Route::get('/', 'YapoController@index');
Route::get('trash', 'YapoController@trash');
Route::get('actor/{id}', 'YapoController@actor');
Route::get('scene', 'YapoController@scene');
Route::delete('scene', 'SceneController@delete');
Route::put('scene/{id}', 'SceneController@update');
Route::get('scenes', 'YapoController@scenes');
Route::get('media/{path}', 'YapoController@thumbnail')->where('path', '(.*)\.jpg$');
Route::get('stats', 'YapoController@stats');

Route::prefix('cleanup')->group(function () {
    Route::get('scenes', 'CleanupController@scenes');
    Route::get('folders', 'CleanupController@folders');
});
