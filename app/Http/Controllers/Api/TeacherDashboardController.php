<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Model\OauthClients;
use App\User;
use Illuminate\Http\Request;

use DB;
use Illuminate\Support\Facades\Log;

class TeacherDashboardController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$user_id = $request->user()->id;
		$result = User::select('id', 'name','profile_complete')
			->where('id', $user_id)
			->with('user_info')
			->with('coursesCount')
			->with(['courses' => function ($query) {
				$query->select('id', 'user_id', 'course_name')
					->with('student_courses')
					->with('student_coursesCount')
					->get();
			}])
			->with('quizzes')
			->with('ratingAvg')
			->with('quizzesCount')
			->with('teacher_info')
			->get()
			->first();

		if ($result) {

            $courses = DB::table('pathshala_course.checkouts as pc_checkouts')
                ->select('pc_course.id','pc_course.course_name','pc_course.amount','pc_course.payment_mode','pc_course.status','pc_base_settings.value as category_name', DB::raw('SUM(pc_checkout_items.total) as total_amount'))
                ->join('pathshala_course.checkout_items as pc_checkout_items', 'pc_checkout_items.checkout_id', 'pc_checkouts.id')
                // ->join('pathshala_course.students_course as pc_students_course', 'pc_checkouts.txnid', 'pc_students_course.txnid')
                ->join('pathshala_course.course as pc_course', 'pc_course.id', 'pc_checkout_items.type_id')
                ->join('pathshala_course.base_settings as pc_base_settings', 'pc_course.category', 'pc_base_settings.id')
                ->where('pc_course.user_id',$user_id)
                ->where('pc_course.status','published')
                ->where('pc_checkouts.status','success')
                ->where('pc_course.payment_mode', 'paid')
                ->groupBy('pc_checkout_items.type_id')
                ->get();

            $courseReport = 0;
            foreach($courses as $key=>$value){
                $courseReport =$courseReport + $value->total_amount;
            }
            $result['newTotalCourseEarning']= $courseReport;

			$result['reportState'] = DB::table('pathshala_course.checkouts as pc_checkouts')
			->select( DB::raw('SUM(total) as total_amount'))
			->join('pathshala_course.students_course as pc_students_course', 'pc_checkouts.txnid', 'pc_students_course.txnid')
			->join('pathshala_course.course as pc_course', 'pc_course.id', 'pc_students_course.course_id')
			->join('pathshala_course.base_settings as pc_base_settings', 'pc_course.category', 'pc_base_settings.id')
			->where('pc_course.user_id',$user_id)
			->where('pc_checkouts.status','success')
			->get();
			return $this->simpleReturn('success', $result);
		}
		return $this->simpleReturn('error', 'No data found', 404);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		//
	}
}
