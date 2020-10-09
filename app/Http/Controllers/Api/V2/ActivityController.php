<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Model\StudentActivity;
use App\Model\StudentSubActivity;
use DB;
class ActivityController extends Controller
{
    public function courseActivity($id, Request $request){

        $perPageCount = $request->get("perPageCount") ? $request->get("perPageCount") : 10 ;

        $activity = StudentActivity::select('id','title','description','user_id','created_at')->where('type','COURSE')
                    ->where('type_id',$id);

        if($request->start != null && $request->end != null){
            $activity = $activity->where('created_at','>=',$request->start." 00:00:00")
                        ->where('created_at','<=',$request->end." 23:59:59");
        }
        
        $activity = $activity->paginate($perPageCount);
        return $this->simpleReturn('success' , $activity , 201);
    }

    public function quizActivity($id, Request $request){

        $perPageCount = $request->get("perPageCount") ? $request->get("perPageCount") : 10 ;
        $activity = DB::table('pathshala_user.student_activities_subtype as sub_activity')
                ->select('sub_activity.id','activity.title','activity.description','sub_activity.student_activity_id','activity.user_id','activity.created_at')
                ->join('pathshala_user.student_activities as activity', 'sub_activity.student_activity_id', 'activity.id')
                ->where('sub_type','QUIZ')
                ->where('sub_type_id',$id);
        
        if($request->start != null && $request->end != null){
            $activity = $activity->where('created_at','>=',$request->start." 00:00:00")
                        ->where('created_at','<=',$request->end." 23:59:59");
        }

        $activity = $activity->paginate($perPageCount);
        return $this->simpleReturn('success' , $activity , 201);
    }

    public function mocktestActivity($id,Request $request){

        $perPageCount = $request->get("perPageCount") ? $request->get("perPageCount") : 10 ;

        $activity = StudentActivity::select('id','title','description','user_id','created_at')
                    ->where('type','MOCKTEST')
                    ->where('type_id',$id);
        
        if($request->start != null && $request->end != null){
            $activity = $activity->where('created_at','>=',$request->start." 00:00:00")
                        ->where('created_at','<=',$request->end." 23:59:59");
        }      

        $activity = $activity->paginate($perPageCount);

        return $this->simpleReturn('success' , $activity , 201);
    }

    public function ebookActivity($id,Request $request){

        $perPageCount = $request->get("perPageCount") ? $request->get("perPageCount") : 10 ;

        $activity = StudentActivity::select('id','title','description','user_d','created_at')->where('type','EBOOK')
                    ->where('type_id',$id);

        if($request->start != null && $request->end != null){
            $activity = $activity->where('created_at','>=',$request->start." 00:00:00")
                        ->where('created_at','<=',$request->end." 23:59:59");
        }

        $activity = $activity->paginate($perPageCount);

        return $this->simpleReturn('success' , $activity , 201);
    }
}
