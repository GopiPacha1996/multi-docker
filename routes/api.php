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

Route::namespace ('Api')->group(function () {

	Route::post('signup', 'LoginController@signUp');
    Route::post('auth/otp', 'LoginController@authViaOtp');
	Route::post('signin', 'LoginController@signIn');
	Route::post('auth/facebook', 'LoginController@loginFacebook');
	Route::post('auth/google', 'LoginController@loginGoogle');
	Route::post('forgot/password', 'LoginController@forgotPassword');
	Route::post('forgot/password/reset', 'LoginController@resetPassword');
	Route::resource('countries', 'CountryController', ['only' => ['index', 'show']]);
	Route::resource('zipcodes', 'ZipcodeController', ['only' => ['index', 'show']]);
	Route::resource('author', 'AuthorController', ['only' => ['show']]);


	Route::get('device', 'DeviceController@index');
	Route::post('device', 'DeviceController@store');
	Route::put('device/{deviceUUID}', 'DeviceController@update');

	Route::post('mailtest', 'TestController@test');

	Route::get('partners/list', 'DeviceController@partnersList');
	Route::get('partners/plans', 'RinvexPlanController@index');
	Route::resource('partners/contact/sales', 'ContactSalesController');

	Route::middleware('auth:api')->group(function () {
        Route::post('signup-varify', 'LoginController@signUpVerify');
		Route::post('sso/validate', 'LoginController@ssoValidate');
		Route::post('signout', 'LoginController@signOut');
		Route::get('auth/user', 'LoginController@authUser');
		Route::post('verify', 'LoginController@verify');
		Route::post('contact', 'LoginController@contact');
		Route::post('contact/verify', 'LoginController@contactVerify');
		Route::post('contact/resend', 'LoginController@contactResend');

		Route::resource('user/profile', 'UserController');
		Route::post('user/profiles/update', 'UserController@profileUpdate');
		Route::get('user/profiles/show', 'UserController@profileShow');
		Route::get('users/list', 'UserController@usersList');
		Route::get('users/studentlist', 'UserController@usersStudentList');
		Route::resource('user/info', 'UserInfoController');
		Route::resource('teacher/registration', 'TeacherInfoController');
		Route::post('teacher/profile/update', 'TeacherInfoController@updateProfile');
		Route::resource('quiz/creator', 'QuizCreatorController');
		Route::post('teacher/registration/basic', 'TeacherInfoController@updateRegistration');
		Route::post('teacher/registration/cover', 'TeacherInfoController@updateCoverPic');
		Route::post('teacher/registration/video', 'TeacherInfoController@updateVideo');
		Route::resource('user/bank', 'BankInfoController');
		Route::resource('plans', 'PlansController');
		Route::resource('plan/payment', 'PaymentController');
		Route::resource('teacher/approval', 'TeacherApprovalController');
//		Route::resource('tutor/profile', 'TeacherProfileController');
		Route::post('generate-oauth', 'OauthController@generateOauth');

		Route::resource('student/dashboard', 'StudentDashboardController', ['except' => ['edit', 'create']]);
		Route::resource('teacher/dashboard', 'TeacherDashboardController', ['except' => ['edit', 'create']]);
		Route::resource('admin/dashboard', 'AdminDashboardController', ['except' => ['edit', 'create']]);
		// Route::resource('student', 'StudentController', ['except' => ['edit', 'create']]);

		//
		//
		//
		//

		// Route::post('get-user', 'LoginController@getUser');

		Route::post('add-plan', 'PlanController@addPlan');
		Route::post('plans', 'PlanController@getPlans');
		Route::post('teacher-verification', 'AdminController@teacherVerification');
		Route::post('teacher-details', 'TeacherController@teacherDetails');
		Route::post('teacher-approval-list', 'TeacherController@teacherApprovalList');
		Route::get('tutor/myprofile', 'TeacherController@myProfile');
		Route::get('tutor/{tutor_id}', 'TeacherController@getTutorDetails');
		Route::get('tutors/list', 'TeacherController@teacherList');
		Route::get('tutor-list', 'TeacherController@allTeacherList');
		// Route::post('user', 'UserController@editProfile');

        //added notification api
        Route::get('notification', 'NotificationController@index');
        Route::post('notification/read/{channel}/{id}',  'NotificationController@read');

		Route::post('user/cover/update', 'UserController@coverUpdate');
		Route::post('user-follow', 'AuthorController@userfollow');
		Route::get('users/all-list', 'UserController@usersAllList');
		Route::post('user/data-update', 'UserController@usersDataUpDate');
		Route::get('top/educators', 'DynamicHomeController@topEducator');
		Route::get('updated/top/educators', 'DynamicHomeController@topEducatorUpdated');
		Route::get('top/institutes', 'DynamicHomeController@topInstitute');
		Route::get('updated/top/institutes', 'DynamicHomeController@topInstituteUpdated');
        Route::get('top/users', 'DynamicHomeController@topUser');
		Route::get('user/following', 'DynamicHomeController@following');
		

		Route::get('user/clientProfile', 'RazorpayController@index');
		Route::post('user/clientProfileDeactivate', 'RazorpayController@deactivate');
		Route::post('institute/client/payment', 'MobilePayController@store');
		Route::post('institute/client/payment/{institute_pay_id}', 'MobilePayController@update');
		Route::get('mobile/payment/history', 'MobilePayController@getHistory');
		Route::get('mobile/payment/invoice/{invoice_id}', 'MobilePayController@getInvoice');
		Route::get('admin/client/invoice', 'MobilePayController@getAdminInvoice');
		
		Route::resource('institute/approval', 'MobileAppController');
		Route::post('institute/app/action', 'MobileAppController@update');
		Route::post('institute/action', 'RazorpayController@instituteAction');

		Route::get('rpay/plans', 'RazorPlanController@index');
        Route::get('rpay/subscriptions', 'RazorSubscriptionController@index');
        Route::post('rpay/subscriptions', 'RazorSubscriptionController@store');
        Route::post('rpay/plans', 'RazorPlanController@store');
		Route::post('rpay/plans/{id}', 'RazorPlanController@update');
		Route::get('subscription/appuser', 'RazorSubscriptionController@appUser');
		Route::post('subscription/appuser/action', 'RazorSubscriptionController@appUserAction');
		Route::post('subscription/free/trial', 'RazorSubscriptionController@freeTrial');

		Route::post('user/cleanup', 'UserCleanupController@userCleanup');

        Route::get('user/followers', 'AuthorController@getFollower');

//        Route::group(['middleware' => ['role:admin']], function () {
//            Route::get('mobile/plans', 'RinvexPlanController@index');
//            Route::post('mobile/plans', 'RinvexPlanController@store');
//            Route::post('mobile/plans/{id}', 'RinvexPlanController@update');
//            Route::delete('mobile/plans/{id}', 'RinvexPlanController@destroy');
//        });
//

        Route::get('master/plans', 'RinvexPlanController@index');
        Route::post('master/plans', 'RinvexPlanController@store');
        Route::post('master/plans/{id}', 'RinvexPlanController@update');
        Route::delete('master/plans/{id}', 'RinvexPlanController@destroy');

        Route::get('user/plan/subscriptions', 'UserMobileSubscriptionController@index');
        Route::get('user/plan/subscriptions/mine', 'UserMobileSubscriptionController@mine');
        Route::post('user/plan/subscriptions/subscribe', 'UserMobileSubscriptionController@subscribe');
        Route::get('user/plan/subscriptions/upgrade/calculate', 'UserMobileSubscriptionController@proRatedCalculation');
        Route::post('user/plan/subscriptions/upgrade', 'UserMobileSubscriptionController@upgrade');
        Route::post('user/plan/subscriptions/cancel', 'UserMobileSubscriptionController@cancel');
		Route::post('user/plan/subscriptions/renew', 'UserMobileSubscriptionController@renew');

		Route::resource('term/service', 'TermServiceController');
		Route::resource('contact/sales', 'ContactSalesController');
		Route::resource('menu/settings', 'MenuSettingsController');

        Route::get('user/addresses', 'UserAddressController@index');
        Route::post('user/addresses', 'UserAddressController@store');
        Route::post('user/addresses/{id}', 'UserAddressController@update');
		Route::delete('user/addresses/{id}', 'UserAddressController@destroy');
		
		Route::get('user/menu/list', 'MenuSettingsController@fetchUserMenu');


		Route::post('mannual/subscription', 'MobilePayController@mannualSubscription');
		Route::get('clients/list', 'MobilePayController@getClientList');

		Route::resource('subadmin/assign', 'SubAdminController');
		Route::post('subadmin/assign/update', 'SubAdminController@update');
        Route::post('subadmin/menu/revoke', 'SubAdminController@revokeMenuAccess');
        Route::post('subadmin/activity/save', 'SubAdminController@saveSubAdminActivity');
		Route::get('subadmin/activity/list', 'SubAdminController@listSubAdminActivity');
		

        Route::post('subadmin/payment', 'SubAdminPaymentController@store');
		Route::post('subadmin/payment/{subadmin_pay_id}', 'SubAdminPaymentController@update');
		Route::get('subadmin/payment', 'SubAdminPaymentController@index');
		Route::get('subadmin/subscribe/details', 'SubAdminPaymentController@subscribeDetails');
		Route::get('subadmin/subscribe/payment/details/{invoice_id}', 'SubAdminPaymentController@subscribePaymentDetails');

		
	});
	Route::get('generate-client', 'DynamicHomeController@generateClient');

});
