<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use App\User;
use App\Model\Pu_OauthClients;
use App\Model\StudentActivity;
use App\Model\StudentActivitesSubType;
use App\Model\TimeSpent;
use Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Model\Pc_TopicAssignment;
use App\Model\Pc_Course;
use App\Model\UserInfo;
use App\Model\SocialAccount;
use App\Model\LiveCourses;
use App\Model\LiveCoursesSchedules;


class StudentActivityController extends Controller
{
    public function store(Request $request)
    {
        $rules = array(
            'user_id' => 'required',
            'educator_id' => 'required',
            'date' => 'required',
            'title' => 'required',
            'type' => 'required',
            'type_id' => 'required',
        );

        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        // if($request->type == 'ASSIGNMENT')
        // {
        //     $validate = $this->validateActivity($request->type,$request->type_id,$request->user_id)

        //     if(!$validate){
        //         return $this->simpleReturn('error', 'Record exists');
        //     }
        // }

        $add = new StudentActivity();
        $add->user_id = $request->user_id;
        $add->educator_id = $request->educator_id;
        $add->type = $request->type;
        $add->type_id = $request->type_id;
        $add->title = $request->title;
        $add->description = $request->description;
        $add->is_institute = isset($request->is_institute)?$request->is_institute:0;
        $add->institute_id = isset($request->institute_id)?$request->institute_id:0;
        $add->date = Carbon::today()->toDateString();

        switch ($request->type) {
            case 'CHAPTER' :
                $add->type = 'COURSE';
                $add->type_id = $request->course_id;
                $add->save();
                if($add) {
                    $addSubType = new StudentActivitesSubType();
                    $addSubType->sub_type = $request->type;
                    $addSubType->sub_type_id = $request->type_id;
                    $addSubType->student_activity_id = $add->id;
                    $addSubType->save();
                }
            break;

            case 'QUIZ' :
                $add->type = 'COURSE';
                $add->type_id = $request->course_id;
                $add->save();
                if($add) {
                    $addSubType = new StudentActivitesSubType();
                    $addSubType->sub_type = $request->type;
                    $addSubType->sub_type_id = $request->type_id;
                    $addSubType->student_activity_id = $add->id;
                    $addSubType->save();
                }
            break;

            case 'ASSIGNMENT' :
                $add->type = 'COURSE';
                $add->type_id = $request->course_id;
                $add->save();
                if($add) {
                    $addSubType = new StudentActivitesSubType();
                    $addSubType->sub_type = $request->type;
                    $addSubType->sub_type_id = $request->type_id;
                    $addSubType->student_activity_id = $add->id;
                    $addSubType->save();
                }
            break;

            case 'ASSIGNMENT' :
                $add->type = 'COURSE';
                $add->type_id = $request->course_id;
                $add->save();
                if($add) {
                    $addSubType = new StudentActivitesSubType();
                    $addSubType->sub_type = $request->type;
                    $addSubType->sub_type_id = $request->type_id;
                    $addSubType->student_activity_id = $add->id;
                    $addSubType->save();
                }
            break;

            case 'CHAT' :
                $add->type = 'COURSE';
                $add->type_id = $request->course_id;
                $add->save();
                if($add) {
                    $addSubType = new StudentActivitesSubType();
                    $addSubType->sub_type = $request->type;
                    $addSubType->sub_type_id = $request->type_id;
                    $addSubType->student_activity_id = $add->id;
                    $addSubType->save();
                }
            break;

            case 'MOCKTEST' :
                $add->type = 'PACKAGE_MOCKTEST';
                $add->type_id = $request->package_id;
                $add->save();
                if($add) {
                    $addSubType = new StudentActivitesSubType();
                    $addSubType->sub_type = $request->type;
                    $addSubType->sub_type_id = $request->type_id;
                    $addSubType->student_activity_id = $add->id;
                    $addSubType->save();
                }
            break;

            case 'EBOOK' :
                $add->type = 'PACKAGE_EBOOK';
                $add->type_id = $request->package_id;
                $add->save();
                if($add) {
                    $addSubType = new StudentActivitesSubType();
                    $addSubType->sub_type = $request->type;
                    $addSubType->sub_type_id = $request->type_id;
                    $addSubType->student_activity_id = $add->id;
                    $addSubType->save();
                }
            break;

            default :
                $add->save();
            break;

        }
        /*if($request->type == 'CHAPTER' || $request->type == 'QUIZ' || $request->type == 'ASSIGNMENT' || $request->type == 'CHAT')
        {
            $add->type = 'COURSE';
            $add->type_id = $request->course_id;
            $add->save();
            if($add) {
                $addSubType = new StudentActivitesSubType();
                $addSubType->sub_type = $request->type;
                $addSubType->sub_type_id = $request->type_id;
                $addSubType->student_activity_id = $add->id;
                $addSubType->save();
            }            
        } else if($request->type == 'MOCKTEST') {
            $add->type = 'PACKAGE_MOCKTEST';
            $add->type_id = $request->package_id;
            $add->save();
            if($add) {
                $addSubType = new StudentActivitesSubType();
                $addSubType->sub_type = $request->type;
                $addSubType->sub_type_id = $request->type_id;
                $addSubType->student_activity_id = $add->id;
                $addSubType->save();
            }            
        } else if($request->type == 'EBOOK') {
            $add->type = 'PACKAGE_EBOOK';
            $add->type_id = $request->package_id;
            $add->save();
            if($add) {
                $addSubType = new StudentActivitesSubType();
                $addSubType->sub_type = $request->type;
                $addSubType->sub_type_id = $request->type_id;
                $addSubType->student_activity_id = $add->id;
                $addSubType->save();
            }            
        }
        else {
            $add->save();
        }*/
        

        return $this->simpleReturn('success', 'Record added successfully');

    }


    public function storeAssignmentLog($id,Request $request)
    {

        $institute_id = $request->header('ClientId');
        $is_institute_mobile = $request->header('EduStore');
        //$is_institute = strval($is_institute_mobile === 'true'? 0:1);
        if($is_institute_mobile) {
            $is_institute = strval($is_institute_mobile === 'true'? 0:1);
        }else {
            $is_institute = 0;
        }
        $assignment = Pc_TopicAssignment::find($id);

        if($assignment) {
            $course = Pc_Course::find($assignment->course_id);
            $add = new StudentActivity();
            $add->user_id = Auth::id();
            $add->educator_id = $course->user_id;
            $add->type = 'COURSE';
            $add->type_id = $assignment->course_id;
            $add->title = 'Downlaoded an assignment';
            $add->description = $assignment->assignment_name;
            $add->is_institute = $is_institute;
            $add->institute_id = isset($institute_id)?$institute_id:0;
            $add->date = Carbon::today()->toDateString();
            $add->save();
            if($add) {
                $addSubType = new StudentActivitesSubType();
                $addSubType->sub_type = 'ASSIGNMENT';
                $addSubType->sub_type_id = $id;
                $addSubType->student_activity_id = $add->id;
                $addSubType->save();
            } 
            return $this->simpleReturn('success', 'Record added successfully');
        }        

        $this->simpleReturn('error', 'Record not added.');
    }

    public function storeLiveCourseLog($id,Request $request)
    {

        $institute_id = $request->header('ClientId');
        $is_institute_mobile = $request->header('EduStore');
        //$is_institute = strval($is_institute_mobile === 'true'? 0:1);
        if($is_institute_mobile) {
            $is_institute = strval($is_institute_mobile === 'true'? 0:1);
        }else {
            $is_institute = 0;
        }
        $live_class = LiveCoursesSchedules::find($id);

        if($live_class) {
            $live_course = LiveCourses::find($live_class->live_course_id);
            $add = new StudentActivity();
            $add->user_id = Auth::id();
            $add->educator_id = $live_course->user_id;
            $add->type = 'LIVE_COURSE';
            $add->type_id = $live_course->id;
            $add->title = 'Attended a live class';
            $add->description = $live_course->batch_name;
            $add->is_institute = $is_institute;
            $add->institute_id = isset($institute_id)?$institute_id:0;
            $add->date = Carbon::today()->toDateString();
            $add->save();
            if($add) {
                $addSubType = new StudentActivitesSubType();
                $addSubType->sub_type = 'LIVE_CLASS';
                $addSubType->sub_type_id = $id;
                $addSubType->student_activity_id = $add->id;
                $addSubType->save();
            } 
            return $this->simpleReturn('success', 'Record added successfully');
        }        

        $this->simpleReturn('error', 'Record not added.');
    }

    public function getActivityLog(Request $request,$userId)
    {
        
        $author_id = Auth::id();

        $output = User::select('id','name','created_at','email','phone')
                ->where('id',$userId)
                ->where('is_active',1);

        $output->with(['preferences' => function($query) {
            $query->select('user_id','preference_id');
            $query->distinct('preference_id');
            $query->with(['preference_name' => function($query1) {
                $query1->select('id','value');
            }]);
        }]);

        $date = Carbon::today()->toDateString();
        $from_date = "2018-01-01 00:00:00";
        $to_date = $date." 23:59:59";

        if($request->to_date)
        {
            $to_date = $request->to_date." 23:59:59";
        }

        if($request->from_date)
        {
            $from_date = $request->from_date." 00:00:00";
        }
        $limit = 5;
        if($request->limit)
        {
            $limit = $request->limit;
        }

        $response = $output->get();
        foreach ($response as $res) {
           $res->recent_log = $this->getActivityDetails($res->id,$author_id,1,$to_date,$from_date,$limit);
           $res->status = $this->getUserStatus($res->id);
           $res->profile_pic = $this->getUserProfile($res->id);
        }
        
        return $this->simpleReturn('success', $response);    
    }

    public function getAllMobileActivityDetails(Request $request)
    {

        $user_id = Auth::id();
        $client_id = $request->header('ClientId');
        $edu_store = $request->header('EduStore');
        $is_institute = strval($edu_store === 'true'? 0:1);
        if($edu_store === 'true'){
            $author_id = 0;
        }
        else {
           $client = Pu_OauthClients::where('id',$client_id)
                    ->where('revoked',0)
                    ->whereRaw('issue_status = "2"')
                    ->first();
            $author_id = $client->user_id; 
        }
        
        $date = Carbon::today()->toDateString();
        $from_date = $date." 00:00:00";
        $to_date = $date." 23:59:59";

        if($request->to_date)
        {
            $to_date = $request->to_date." 23:59:59";
        }

        if($request->from_date)
        {
            $from_date = $request->from_date." 00:00:00";
        }

        //$author_id = 13;
        $pageCount = isset($request->PageCount)?$request->PageCount:5;
        $activity_log = StudentActivity::select('title','description','date','created_at')
                        ->where('user_id',$user_id)
                        ->whereRaw('created_at >= "'.$from_date.'" and created_at <= "'.$to_date.'"')
                        ->when($is_institute, function($query) use ($author_id){
                            return $query->where('educator_id',$author_id);
                        })
                        ->orderBy('created_at','desc')
                        ->paginate($pageCount);

        return $this->simpleReturn('success', $activity_log);
    }

    public function getActivityDetails($user_id, $educator_id,$is_institute,$to_date,$from_date,$limit = 10)
    {

        $activity_log = StudentActivity::select('title','description','date','created_at')
                        ->where('user_id',$user_id)
                        ->whereRaw('created_at >= "'.$from_date.'" and created_at <= "'.$to_date.'"')
                        ->when($is_institute, function($query) use ($educator_id){
                            return $query->where('educator_id',$educator_id);
                        })
                        ->orderBy('created_at','desc')
                        ->limit($limit)
                        ->get(); 

        return $activity_log;
    }

    public function getTimeSpentDetails($user_id, $institute_id,$to_date,$from_date,$is_institute)
    {
        $time_spent = array (
            'COURSE'=>0,
            'MOCKTEST'=>0,
            'QUIZ'=>0,
            'EBOOK'=>0,
            'COMMUNITY'=>0,
            'LIVECLASS'=>0,
            'YOUTUBE'=>0,
            'OTHERS'=>0,
        );

        $user_time_logs = TimeSpent::selectRaw('user_id,type,SUM(duration) as duration')
                ->where('user_id',$user_id)
                ->whereRaw('created_at >= "'.$from_date.'" and created_at <= "'.$to_date.'"')
                ->where('is_institute',$is_institute)
                ->when($is_institute, function($query) use ($institute_id){
                    return $query->where('institute_id',$institute_id);
                })
                ->groupBy(['user_id', 'type'])
                ->get(); 

        foreach ($user_time_logs as $user_time_log){
            $time_spent[$user_time_log->type] = isset($user_time_log->duration)?$user_time_log->duration:0;
        }

        return $time_spent;
    }

    public function getTimeSpentLog(Request $request,$userId)
    {
        $author_id = Auth::id();
        $client = Pu_OauthClients::where('user_id',$author_id)
                    ->where('revoked',0)
                    ->whereRaw('issue_status = "2"')
                    ->first();
        $date = Carbon::today()->toDateString();
        $from_date = $date." 00:00:00";
        $to_date = $date." 23:59:59";

        if($request->to_date)
        {
            $to_date = $request->to_date." 23:59:59";
        }

        if($request->from_date)
        {
            $from_date = $request->from_date." 00:00:00";
        }
        if($client){
            //Log::info('CLIENT'.$client->id);
            $response =  $this->getTimeSpentDetails($userId,$client->id,$to_date,$from_date,1);
            return $this->simpleReturn('success', $response);
        }
        return $this->simpleReturn('error', 'Data not found.', 404);
        
    }

    public function getMobileTimeSpentLog(Request $request)
    {
        $user_id = Auth::id();
        $client_id = $request->header('ClientId');
        $is_institute_mobile = $request->header('EduStore');
        $is_institute = strval($is_institute_mobile === 'true'? 0:1);
        $date = Carbon::today()->toDateString();
        $from_date = $date." 00:00:00";
        $to_date = $date." 23:59:59";

        if($request->to_date)
        {
            $to_date = $request->to_date." 23:59:59";
        }

        if($request->from_date)
        {
            $from_date = $request->from_date." 00:00:00";
        }
        if($user_id){
            $response =  $this->getTimeSpentDetails($user_id,$client_id,$to_date,$from_date,$is_institute);
            //$response =  $this->getTimeSpentDetails(13,16,$date,1);
            return $this->simpleReturn('success', $response);
        }
        return $this->simpleReturn('error', 'Data not found.', 404);
        
    }

    public function getUserLogs(Request $request,$userId)
    {
        $author_id = Auth::id();
        $client = Pu_OauthClients::where('user_id',$author_id)
                    ->where('revoked',0)
                    ->whereRaw('issue_status = "2"')
                    ->first();
        $date = Carbon::today()->toDateString();
        $from_date = $date." 00:00:00";
        $to_date = $date." 23:59:59";

        if($request->to_date)
        {
            $to_date = $request->to_date." 23:59:59";
        }

        if($request->from_date)
        {
            $from_date = $request->from_date." 00:00:00";
        }

        $limit = 30;
        if($request->limit)
        {
            $limit = $request->limit;
        }

        //$perPageCount = $request->get("perPageCount") ? $request->get("perPageCount") : 10 ;

        $response =  $this->getActivityDetails($userId,$author_id,1,$to_date,$from_date,$limit);
        //$is_institute = 1;
        // $response = StudentActivity::select('title','description','date','created_at')
        //                 ->where('user_id',$userId)
        //                 ->whereRaw('created_at >= "'.$from_date.'" and created_at <= "'.$to_date.'"')
        //                 ->where('educator_id',$author_id)
        //                 ->orderBy('created_at','desc')
        //                 ->paginate($perPageCount);
        return $this->simpleReturn('success', $response);        
    }

    public function getMobileUserLogs(Request $request)
    {
        $user_id = Auth::id();
        $client_id = $request->header('ClientId');
        $edu_store = $request->header('EduStore');
        $is_institute = strval($edu_store === 'true'? 0:1);
        if($edu_store === 'true'){
            $author_id = 0;
        }
        else {
           $client = Pu_OauthClients::where('id',$client_id)
                    ->where('revoked',0)
                    ->whereRaw('issue_status = "2"')
                    ->first();
            $author_id = $client->user_id; 
        }
        
        $date = Carbon::today()->toDateString();
        $from_date = $date." 00:00:00";
        $to_date = $date." 23:59:59";

        if($request->to_date)
        {
            $to_date = $request->to_date." 23:59:59";
        }

        if($request->from_date)
        {
            $from_date = $request->from_date." 00:00:00";
        }

        $limit = 5;
        if($request->limit)
        {
            $limit = $request->limit;
        }

        $response =  $this->getActivityDetails($user_id,$author_id,$is_institute,$to_date,$from_date,$limit);
        return $this->simpleReturn('success', $response);        
    }


    public function getUserStatus($user_id)
    {
        $user_log = StudentActivity::where('user_id',$user_id)->orderBy('created_at','desc')->first();
        //$status = 'DEAD';
        $now = carbon::now()->timezone('Asia/Kolkata');
        if($user_log){
            $date = Carbon::parse($user_log->created_at);
            $diff = $date->diffInDays($now);
            if($diff < 7)
            {
                return 'Active';
            }

            if($diff < 30)
            {
                return 'Passive';
            }
        }
        return 'None';
    }

    public function getUserProfile($user_id)
    {
        $url='';
        $social_account = SocialAccount::select('provider_user_avatar')
                                    ->where('user_id',$user_id)
                                    ->first();
        if($social_account){
            $url = $social_account->provider_user_avatar;
        } else {
           $user_info = UserInfo::select('profile_pic')
                                    ->where('user_id',$user_id)
                                    ->first();
            if($user_info){
                $url = env('DO_SPACES_CDN').'/'.env('DO_SPACES_APP_ENV','e2e').'/images/profiles/'.$user_info->profile_pic;
            }
        }
        return $url;
    }

    // public function validateActivity($type,$type_id,$user_id)
    // {
    //     //$ifExists = StudentActivitesSubType::where('type')
    //     return true;
    // }

}
