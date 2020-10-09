<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Model\Pc_ChapterVideo;
use App\Model\Pc_Course;
use App\Model\Pc_CourseCompletionLog;
use App\Model\Pc_QuizAttempt;
use App\Model\Pc_StudentsCourse;
use App\User;
use App\Model\UserCategory;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

class StudentDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $user_id = $request->user()->id;

        // $preference_data = UserCategory::where('user_id', $user_id)->where('status', 'active')->get();
        // $preference = array();
        // $course = array();
        // $quiz = array();
        // $teachers = array();
        // $ongoing_course = array();

        // foreach ($preference_data as $pre_val) {
        //     $item = array();
        //     $item['id'] = $pre_val->category;
        //     $item['value'] = $this->get_bs($pre_val->category, 'value', 'value');
        //     $preference[] = $item;
        // }

        // $course_data = Pc_StudentsCourse::where('user_id', $user_id)->orderBy('id', 'desc')->take(5)->get();
        // foreach ($course_data as $co_val) {
        //     $item = array();
        //     $item['course_id'] = $co_val->course_id;
        //     $course_details = $this->get_course($co_val->course_id);
        //     $item['course_name'] = $course_details->course_name;
        //     $course_video = Pc_ChapterVideo::where('course_id', $co_val->course_id)->where('status', 'active')->count();
        //     $completed_video = Pc_CourseCompletionLog::where('course_id', $co_val->course_id)
        //         ->where('user_id', $user_id)
        //         ->where('type', 'video')
        //         ->count();
        //     $item['course_percentage'] = $completed_video == 0 ? 0 : round(($completed_video / $course_video) * 100);
        //     $course[] = $item;
        // }

        // $quiz_data = Pc_QuizAttempt::where('user_id', $user_id)->where('visible', 1)->with('quiz')->get();
        // if (count($quiz_data)) {
        //     $quiz = $quiz_data;
        // }

        // foreach ($course_data as $co_val) {
        //     $item = array();
        //     $user = Pc_Course::select('user_id')->where('id', $co_val->course_id)->get()->first();
        //     if ($user) {
        //         $item['id'] = $user->user_id;
        //         $item['user_pic'] = $this->userProfileDp($user->user_id);
        //         $item['name'] = $user->name;
        //     }
        //     $teachers[] = $item;
        // }

        // $ongoing_co = Pc_StudentsCourse::where('user_id', $user_id)->where('status', 'active')->get();
        // if (count($ongoing_co)) {
        //     foreach ($ongoing_co as $co_value) {
        //         $item = array();
        //         $item['course_id'] = $co_value->course_id;
        //         $course_details = $this->get_course($co_value->course_id);
        //         $item['course_name'] = $course_details->course_name;
        //         $ongoing_course[] = $item;
        //     }
        // }

        // $result['preference'] = $preference;
        // $result['my_course'] = $course;
        // $result['my_course_count'] = count($course);
        // $result['my_quiz'] = $quiz;
        // $result['my_quiz_count'] = count($quiz);
        // $result['my_teachers'] = array_unique($teachers, SORT_REGULAR);
        // $result['my_teachers_count'] = count(array_unique($teachers, SORT_REGULAR));
        // $result['ongoing_course'] = $ongoing_course;
        // $result['subscriptions'] = '';
        // $result['calendar'] = '';
        // $result['transactions'] = '';
        // $result['mock_test'] = '';
        // $result['ongoing_mock_test'] = '';
        // $result['my_chat'] = '';

        $user_id = $request->user()->id;
        $result = User::where('id', $user_id)
        ->with('student_courses')
        ->with('ongoing_student_courses')
        ->with('student_quizzes')
        ->with('student_transactions')
        ->with('student_preferences')
        ->get()
        ->first();

        $result['courses'] = $result->student_courses->take('3');
        $result['coursesCount'] = $result->student_courses->take('3')->count();
        $result['ongoingCourses'] = $result->ongoing_student_courses->take('4');
        $result['ongoingCoursesCount'] = $result->ongoing_student_courses->take('4')->count();
        $result['quizzes'] = $result->student_quizzes->unique('quiz_id')->take('3');
        $result['quizzesCount'] = $result->student_quizzes->unique('quiz_id')->count();
        $result['transactions'] = $result->student_transactions->unique('txnid')->take('3');
        $result['transactionsCount'] = $result->student_transactions->unique('txnid')->count();
        $result['preferences'] = $result->student_preferences->take('4');
        $result['preferencesCount'] = $result->student_preferences->take('4')->count();

//        $apiClient = new Client;
//        $link='api/dashboard/data';
//        $data['user_id']=$user_id;
//
//        try {
//            $responseAPI = $apiClient->post(env('URL_PATHSHALA_MOCKTEST') . $link, [
//                'headers' => [
//                    'Accept' => $request->header('Accept'),
//                    'Authorization' => $request->header('Authorization'),
//                    'client_id' => $request->header('ClientId'),
//                    'platform' => $request->header('Platform'),
//                    'edu_store' => $request->header('EduStore'),
//                ],
//                'json' => $data
//            ]);
//
//            $code = $responseAPI->getStatusCode();
//            if(200 == $code){
//                Log::info('API(Student dashboard mocktest) success ');
//                $result['mocktest'] = $responseAPI->getBody();
//            }
//        } catch (RequestException $e) {
//                Log::info('API(Student dashboard mocktest) failed'.$e->getCode());
//            if ($e->hasResponse()) {
//                Log::info("API(Student dashboard mocktest) failed(2) Code: ".$e->getCode());
//            }
//            //return self::fsReturn('error', 'Something went wrong on the server.',$e->getCode());
//        }

        return $this->simpleReturn('success', $result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
