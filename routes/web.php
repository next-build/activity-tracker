<?php

use Illuminate\Support\Facades\Route;

// Requests entries...
Route::get('/activity-tracker-api/requests', 'RequestsController@index');
Route::get('/activity-tracker-api/requests/{uuid}', 'RequestsController@show');
Route::get('/activity-tracker-api/visitor-ip/{uuid}/requests', 'RequestsController@requestIndex');
Route::get('/activity-tracker-api/visitor-ip/{visitor_uuid}/requests/{request_uuid}', 'RequestsController@requestShow');

// Toggle Recording...
Route::post('/activity-tracker-api/toggle-recording', 'RecordingController@toggle');

// Clear Entries...
Route::delete('/activity-tracker-api/dump', 'RequestsController@destroy');

Route::get('/{view?}', 'HomeController@index')->where('view', '(.*)')->name('activity-tracker');
