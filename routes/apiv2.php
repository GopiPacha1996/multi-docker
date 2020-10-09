<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */
Route::middleware('auth:api')->group(function () {
    Route::get('device', 'DeviceController@index');
    Route::post('device', 'DeviceController@store');

	Route::get('teacher/report/students/timespent/{id}', 'StudentActivityController@getTimeSpentLog');
	Route::get('students/mobile/timespent', 'StudentActivityController@getMobileTimeSpentLog');
	
	Route::get('students/mobile/all/activities/logs', 'StudentActivityController@getAllMobileActivityDetails');	
	Route::get('students/mobile/activities/logs', 'StudentActivityController@getMobileUserLogs');
	Route::get('teacher/report/students/recent/activities/{id}', 'StudentActivityController@getActivityLog');
	Route::get('teacher/report/students/activities/logs/{id}', 'StudentActivityController@getUserLogs');
	Route::get('/teacher/course/{id}/activities','ActivityController@courseActivity');
    Route::get('/teacher/quiz/{id}/activities', 'ActivityController@quizActivity');
    Route::get('/teacher/package/{id}/activities', 'ActivityController@mocktestActivity');
    Route::get('/teacher/ebook/package/{id}/activities', 'ActivityController@ebookActivity');


    Route::post('/student/assignment/{id}/activity', 'StudentActivityController@storeAssignmentLog');
    Route::post('/student/live/course/{id}/activity', 'StudentActivityController@storeLiveCourseLog');

    Route::post('/user/profile', 'UserProfileController@profile');
    Route::get('/user/profile/{id}', 'UserProfileController@show');
    Route::get('/user/followers', 'UserFollowDetailsController@getFollowers');
    Route::get('/user/following', 'UserFollowDetailsController@getFollowing');

    Route::get('/clients/details', 'ClientDetailsController@index');
});

Route::post('student/activity/log/record', 'StudentActivityController@store');
Route::get('partner/data', 'PartnerDataController@getData');

