<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Pc_Course;
use App\Model\UserFollower;
use App\Model\Pc_Preference;
use App\User;
use App\UserCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Validator;
use App\Model\EducatorInsight;
use App\Model\Pc_StudentsCourse;

class DynamicHomeController extends Controller
{

    public function following(Request $request)
    {
        $perPageCount = $request->perPageCount ? $request->perPageCount : 15;
        $roles = ['educator', 'institute'];
        if($request->type){
            $roles = [$request->type];
        }
        $author = User::role($roles);
        $author->whereIn('id', $this->getLoggedInUserIdsFollowing());
        return $this->getEducatorDetails($request, $author, null, $perPageCount);
    }

    public function topEducator(Request $request)
    {
        if($request->selected_preference_id){
            $selectedUserIds = $this->getEducatorsByPreference($request->selected_preference_id);
            if(count($selectedUserIds) <=0 ){
               return $this->simpleReturn('success', 'Ops! no educator available', 404);
            }
        }
        $perPageCount = $request->perPageCount ? $request->perPageCount : 15;
        $results=[];
        $author = User::role(['educator']);
        $author->whereNotIn('id', $this->getLoggedInUserIdsFollowing());
        return $this->getEducatorDetails($request, $author, $selectedUserIds, $perPageCount);
    }

    public function topEducatorUpdated(Request $request)
    {
        if($request->selected_preference_id){
            $selectedUserIds = $this->getEducatorsByPreference($request->selected_preference_id);
            if(count($selectedUserIds) <=0 ){
               return $this->simpleReturn('success', 'Ops! no educator available', 404);
            }
        }
        $perPageCount = $request->perPageCount ? $request->perPageCount : 15;
        $results=[];
        $author = User::role(['educator']);
        $author->whereNotIn('id', $this->getLoggedInUserIdsFollowing());
        return $this->getEducatorDetailsUpdated($request, $author, $selectedUserIds, $perPageCount);
    }


    public function topInstitute(Request $request)
    {
        $perPageCount = $request->perPageCount ? $request->perPageCount : 15;
        $selectedUserIds = $this->getEducatorsIdByPreferences($request);
        if(count($selectedUserIds) <=0 ){
           return $this->simpleReturn('success', 'Ops! no educator available');
        }
        $results=[];
        $author = User::role(['institute']);
        $author->whereNotIn('id', $this->getLoggedInUserIdsFollowing());
        return $this->getEducatorDetails($request, $author, $selectedUserIds, $perPageCount);
    }

    public function topInstituteUpdated(Request $request)
    {
        $perPageCount = $request->perPageCount ? $request->perPageCount : 15;
        $selectedUserIds = $this->getEducatorsIdByPreferences($request);
        if(count($selectedUserIds) <=0 ){
           return $this->simpleReturn('success', 'Ops! no educator available');
        }
        $results=[];
        $author = User::role(['institute']);
        $author->whereNotIn('id', $this->getLoggedInUserIdsFollowing());
        return $this->getInstituteDetailsUpdated($request, $author, $selectedUserIds, $perPageCount);
    }

    public function topUser(Request $request)
    {
        $selectedUserIds = [];
        if($request->selected_preference_id){
            $selectedUserIds = $this->getUsersByPreference($request->selected_preference_id);
            if(count($selectedUserIds) <=0 ){
               return $this->simpleReturn('success', 'Ops! no users found', 404);
            }
        }
        $perPageCount = $request->perPageCount ? $request->perPageCount : 15;
        $users = User::role(['student']);
        $barred = User::role(['educator', 'institute', 'quiz'])->pluck('id')->toArray();
        $merged = array_merge($this->getLoggedInUserIdsFollowing(), $barred);
        $users->whereNotIn('id', $merged);
        /* search by name */
        return $this->getUserDetail($request, $users, $selectedUserIds, $perPageCount);
    }

    public function getEducatorsByPreference($selected_preference_id){
        $selectedPreferenceId=[];
        $selectedPreferenceId = explode(',', $selected_preference_id);
        return Pc_Course::distinct()->whereIn('category', $selectedPreferenceId)->where('status','published')->pluck('user_id')->toArray();
    }

    public function getUsersByPreference($selected_preference_id){
        $selectedPreferenceId=[];
        $selectedPreferenceId = explode(',', $selected_preference_id);
        return Pc_Preference::whereIn('preference_id', $selectedPreferenceId)->pluck('user_id')->toArray();
    }
    /**
     * @return mixed
     */
    public function getLoggedInUserIdsFollowing()
    {
        return UserFollower::where('follow', 1)
            ->where('user_id', Auth::id())
            ->pluck('tutor_id')
            ->toArray();
    }

    /**
     * @param $authors
     * @return mixed
     */
    private function getFollowers($authors)
    {
        $followers = UserFollower::selectRaw('tutor_id, count(*) as follower_count')
            ->where('follow', 1)
            ->whereIn('tutor_id', $authors->pluck('id'))
            ->groupBy('tutor_id')
            ->get()
            ->mapWithKeys(function ($item){
                return [$item['tutor_id'] => $item['follower_count']];
            })->toArray();

        foreach ($authors as $author) {
            $author->follower_count
                = array_key_exists($author->id, $followers) ? $followers[$author->id] : 0;
        }

        return $authors;
    }

    /**
     * @param $authors
     * @return mixed
     */
    private function getCoursesSold($authors)
    {
        foreach ($authors as $author) {
            $courses = Pc_Course::select('id')
            ->where('user_id', $author->id)
            ->get();

            $courseCount = Pc_StudentsCourse::whereIn('course_id',$courses)
                    ->count();
            $author->course_sold = isset($courseCount) ? $courseCount : 0;
        }




        return $authors;
    }

    /**
     * @param Request $request
     * @param $author
     * @param $selectedUserIds
     * @param int $perPageCount
     * @return JsonResponse
     */
    public function getEducatorDetails(Request $request, $author, $selectedUserIds, int $perPageCount): JsonResponse
    {
        $author->where('is_active', 1)
            ->with('teacher_info')
            ->with('user_info')
            ->with('coursesCount')
            ->with(['courses' => function ($query) {
                $query->select('id', 'user_id', 'course_name')
                    ->with('student_coursesCount')
                    ->get();
            }])
            ->with('ratingAvg')
            ->with('rating_avg');

        if (!empty($request->search)) {
            $author->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->selected_preference_id && $selectedUserIds != null) {
            $author->whereIn('id', $selectedUserIds);
        }

        $author = $author->paginate($perPageCount);
        if ($author) {
            $author = $this->getFollowers($author);
            return $this->simpleReturn('success', $author);
        }

        return $this->simpleReturn('error', 'Author details not found.', 404);
    }

    /**
     * @param Request $request
     * @param $authr
     * @param $selectedUserIds
     * @param int $perPageCount
     * @return JsonResponse
     */
     public function getUserDetail(Request $request, $users, $selectedUserIds,
                                   int $perPageCount): JsonResponse
    {
        $users->where('is_active', 1)
            ->with('user_info');

        $users->with('student_preferences');
        
        if (!empty($request->search)) {
            $users->where('name', 'like', '%' . $request->search . '%');
            $users->orWhere('email', 'like', '%' . $request->search . '%');
            $users->orWhere('phone', 'like', '%' . $request->search . '%');
        }

//        if ($request->selected_preference_id && $selectedUserIds != null) {
//            $users->whereIn('id', $selectedUserIds);
//        }

        $users = $users->paginate($perPageCount);
        if ($users) {
            $users = $this->getFollowers($users);
            return $this->simpleReturn('success', $users);
        }

        return $this->simpleReturn('error', 'User details not found.', 404);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getEducatorsIdByPreferences(Request $request)
    {
        if ($request->selected_preference_id) {
            $selectedUserIds = $this->getEducatorsByPreference($request->selected_preference_id);
        }
        return $selectedUserIds;
    }

    public function generateClient()
    {
        $query = http_build_query([
            'client_id'     => '3',
            'redirect_uri'  => 'http://passport.dev/oauth/callback',
            'response_type' => 'code',
            'scope'         => '',
        ]);
        $abcd = redirect('http://passport.dev/oauth/authorize?' . $query);
        return $this->simpleReturn('success', $abcd);
    }

    public function getEducatorDetailsUpdated(Request $request, $author, $selectedUserIds, int $perPageCount): JsonResponse
    {
        
        $author->where('is_active', 1)
            ->with('teacher_info')
            ->with('user_info')
            ->with('coursesCount')
            ->with(['courses' => function ($query) {
                $query->select('id', 'user_id', 'course_name')
                    ->with('student_coursesCount')
                    ->get();
            }])
            ->with('ratingAvg')
            ->with('educator_insights');
         
             
        if (!empty($request->search)) {
            $author->where('name', 'like', '%' . $request->search . '%');
        }

        if (!empty($request->popularity)) {
            $authorList = EducatorInsight::whereNotIn('educator_id',$this->getLoggedInUserIdsFollowing())
            ->select('educator_id')
            ->orderBy('popularity','desc')
            ->get()
            ->pluck('educator_id')
            ->toArray();
            //$author->orderByRaw('FIELD(id,'.implode(',', $authorList).')');
            if(count($authorList) > 0){
                $author->orderByRaw('IF(FIELD(id,'.implode(',', $authorList).')=0,1,0) ASC,FIELD(id,'.implode(',', $authorList).')');
            } 
        }

        if (!empty($request->rating)) {
            $authorList = EducatorInsight::whereNotIn('educator_id',$this->getLoggedInUserIdsFollowing())
            ->select('educator_id')
            ->orderBy('rating','desc')
            ->get()
            ->pluck('educator_id')
            ->toArray();
            //$author->orderByRaw('FIELD(id,'.implode(',', $authorList).')');
            if(count($authorList) > 0){
                $author->orderByRaw('IF(FIELD(id,'.implode(',', $authorList).')=0,1,0) ASC,FIELD(id,'.implode(',', $authorList).')');
            } 
        }

        if (!empty($request->followers)) {
            $authorList = EducatorInsight::whereNotIn('educator_id',$this->getLoggedInUserIdsFollowing())
            ->select('educator_id')
            ->orderBy('followers','desc')
            ->get()
            ->pluck('educator_id')
            ->toArray();
            //$author->orderByRaw('FIELD(id,'.implode(',', $authorList).')');
            if(count($authorList) > 0){
                $author->orderByRaw('IF(FIELD(id,'.implode(',', $authorList).')=0,1,0) ASC,FIELD(id,'.implode(',', $authorList).')');
            } 
        }

        if (!empty($request->max_watched)) {
            $authorList = EducatorInsight::whereNotIn('educator_id',$this->getLoggedInUserIdsFollowing())
            ->select('educator_id')
            ->orderBy('progress_count','desc')
            ->get()
            ->pluck('educator_id')
            ->toArray();
            //$author->orderByRaw('FIELD(id,'.implode(',', $authorList).')');
            if(count($authorList) > 0){
                $author->orderByRaw('IF(FIELD(id,'.implode(',', $authorList).')=0,1,0) ASC,FIELD(id,'.implode(',', $authorList).')');
            }            
        }

        if (!empty($request->newest)) {
            $author->orderBy('created_at', 'Desc');
        }


        if ($request->selected_preference_id && $selectedUserIds != null) {
            $author->whereIn('id', $selectedUserIds);
        }
        $author = $author->paginate($perPageCount);      
                
        if ($author) {
            $author = $this->getFollowers($author);
            $author = $this->getCoursesSold($author);
            
            return $this->simpleReturn('success', $author);
        }

        return $this->simpleReturn('error', 'Author details not found.', 404);
    }


    public function getInstituteDetailsUpdated(Request $request, $author, $selectedUserIds, int $perPageCount): JsonResponse
    {
        
        $author->with('teacher_info')
            ->with('user_info')
            ->with('coursesCount')
            ->with(['courses' => function ($query) {
                $query->select('id', 'user_id', 'course_name')
                    ->with('student_coursesCount')
                    ->get();
            }])
            ->with('ratingAvg')
            ->with('educator_insights');
         
             
        if (!empty($request->search)) {
            $author->where('name', 'like', '%' . $request->search . '%');
        }

        if (!empty($request->popularity)) {
            $authorList = EducatorInsight::whereNotIn('educator_id',$this->getLoggedInUserIdsFollowing())
            ->select('educator_id')
            ->orderBy('popularity','desc')
            ->get()
            ->pluck('educator_id')
            ->toArray();
            //$author->orderByRaw('FIELD(id,'.implode(',', $authorList).')');
            if(count($authorList) > 0){
                $author->orderByRaw('IF(FIELD(id,'.implode(',', $authorList).')=0,1,0) ASC,FIELD(id,'.implode(',', $authorList).')');
            } 
        }

        if (!empty($request->rating)) {
            $authorList = EducatorInsight::whereNotIn('educator_id',$this->getLoggedInUserIdsFollowing())
            ->select('educator_id')
            ->orderBy('rating','desc')
            ->get()
            ->pluck('educator_id')
            ->toArray();
            //$author->orderByRaw('FIELD(id,'.implode(',', $authorList).')');
            if(count($authorList) > 0){
                $author->orderByRaw('IF(FIELD(id,'.implode(',', $authorList).')=0,1,0) ASC,FIELD(id,'.implode(',', $authorList).')');
            } 
        }

        if (!empty($request->followers)) {
            $authorList = EducatorInsight::whereNotIn('educator_id',$this->getLoggedInUserIdsFollowing())
            ->select('educator_id')
            ->orderBy('followers','desc')
            ->get()
            ->pluck('educator_id')
            ->toArray();
            //$author->orderByRaw('FIELD(id,'.implode(',', $authorList).')');
            if(count($authorList) > 0){
                $author->orderByRaw('IF(FIELD(id,'.implode(',', $authorList).')=0,1,0) ASC,FIELD(id,'.implode(',', $authorList).')');
            } 
        }

        if (!empty($request->max_watched)) {
            $authorList = EducatorInsight::whereNotIn('educator_id',$this->getLoggedInUserIdsFollowing())
            ->select('educator_id')
            ->orderBy('progress_count','desc')
            ->get()
            ->pluck('educator_id')
            ->toArray();
            //$author->orderByRaw('FIELD(id,'.implode(',', $authorList).')');
            if(count($authorList) > 0){
                $author->orderByRaw('IF(FIELD(id,'.implode(',', $authorList).')=0,1,0) ASC,FIELD(id,'.implode(',', $authorList).')');
            }            
        }

        if (!empty($request->newest)) {
            $author->orderBy('created_at', 'Desc');
        }


        if ($request->selected_preference_id && $selectedUserIds != null) {
            $author->whereIn('id', $selectedUserIds);
        }
        $author = $author->paginate($perPageCount);      
                
        if ($author) {
            $author = $this->getFollowers($author);
            $author = $this->getCoursesSold($author);
            
            return $this->simpleReturn('success', $author);
        }

        return $this->simpleReturn('error', 'Author details not found.', 404);
    }

}
