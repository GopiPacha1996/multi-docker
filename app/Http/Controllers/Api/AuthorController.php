<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use App\Model\Pc_Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Model\UserFollower;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthorController extends Controller
{

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Request $request, $id)
    {
        $results=[];
        $author = User::where('id', $id)
            ->with('teacher_info')
            ->with('user_info')
            ->with('published_courses')
            ->with('student_courses')
			->with('coursesCount')
			->with(['courses' => function ($query) {
				$query->select('id', 'user_id', 'course_name')
					->with('student_courses')
					->with('student_coursesCount')
					->get();
			}])
            ->with('ratingAvg')
            ->with(['educator_insights' => function ($query) {
				$query->select('id', 'educator_id', 'popularity','followers','progress_count','rating');
			}])
            // ->with('followerCount')
            ->get()
            ->first();
        if ($author) {
            $author['pic'] = null;
            if($author->user_info){
            $author['pic'] = $author->user_info->profile_pic ? $author->user_info->profile_pic : null;
            }
            $author['reviews_count'] = Pc_Course::where('course.user_id', $id)
                ->join('reviews', 'course.id', '=', 'reviews.type_id')
                ->where('reviews.type', 'course')
                ->count();
            $author['follower_count'] = UserFollower::where('tutor_id', $id)
                ->where('follow', 1)->count();

        }
        // $user_id = $request->user()->id;


        if ($author) {
            $user_id = $request->userId;
            $matchThese = ['user_id'=> $user_id,'tutor_id'=> $id];
            $follow = UserFollower::select('follow','notification')->where($matchThese)->first();

            // $results = array("author" => $author, "follow" => $follow);

            // $author['author'] = $author;
            //courses list
            $course_arr = Pc_Course::where('user_id',$id)->pluck('id');
            $course_sold_count = User::getCourseSoldCount($course_arr); 
            $author['course_sold_count'] = $course_sold_count;
            $author['course_sold'] = $course_sold_count;

            $author['follow'] = UserFollower::select('follow','notification')->where($matchThese)->first();

            return $this->simpleReturn('success', $author);
        }

        return $this->simpleReturn('error', 'Author details not found.', 404);
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function userfollow(Request $request)
    {
        // return $request;
        $user_id = $request->user()->id;

        $rules = array(
            'tutor_id' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }
        $followMatch = ['user_id'=> $user_id,'tutor_id'=> $request->tutor_id, 'follow' => '1'];

        if($request->type == 'follow'){
            // if same data inserted
            $matchThese = ['user_id'=> $user_id,'tutor_id'=> $request->tutor_id, 'follow' => '1'];
            $exists = UserFollower::where($matchThese)->count();
            if ($exists) {
                UserFollower::where($matchThese)->update([ 'follow' => '0' ]);
                $follow['message'] = 'Unfollow successful';
                $follow['followerCount'] = UserFollower::where($followMatch)->count();
                return $this->simpleReturn('0', $follow);
            }

            // if notifiaction data inserted in table
            $matchThese1 = ['user_id'=> $user_id,'tutor_id'=> $request->tutor_id];
            $exists1 = UserFollower::where($matchThese1)->count();
            if ($exists1) {
                if (UserFollower::where($matchThese1)->first()->update([ 'follow' => '1' ])) {
                    $follow['message'] = 'Thanks for following !';
                    $follow['followerCount'] = UserFollower::where($followMatch)->count();
                    return $this->simpleReturn('1', $follow);
                } else {
                    return $this->simpleReturn('error', 'Error');
                }
            }

            $add = new UserFollower();
            $add->user_id = $user_id;
            $add->tutor_id = $request->tutor_id;
            $add->follow = 1;
            $add->notification = 0;
            if ($add->save()) {
                $follow['message'] = 'Thanks for following !';
                $follow['followerCount'] = UserFollower::where($followMatch)->count();
                return $this->simpleReturn('1', $follow);
            }
            return $this->simpleReturn('error', 'Error in insertion', 500);
        }else if($request->type == 'notification'){

            // if same data inserted
            $matchThese = ['user_id'=> $user_id,'tutor_id'=> $request->tutor_id, 'notification' => '1'];
            $exists = UserFollower::where($matchThese)->count();
            if ($exists) {
                UserFollower::where($matchThese)->update([ 'notification' => '0' ]);
                return $this->simpleReturn('0', 'Unsubscribe successful');
            }

            // if follow data inserted in table
            $matchThese1 = ['user_id'=> $user_id,'tutor_id'=> $request->tutor_id];
            $exists1 = UserFollower::where($matchThese1)->count();
            if ($exists1) {
                if (UserFollower::where($matchThese1)->update([ 'notification' => '1' ])) {
                    return $this->simpleReturn('1', 'Thanks for Subscribe !');
                } else {
                    return $this->simpleReturn('error', 'Error');
                }
            }
            // else for new entry
            $add = new UserFollower();
            $add->user_id = $user_id;
            $add->tutor_id = $request->tutor_id;
            $add->follow = 0;
            $add->notification = 1;
            if ($add->save()) {
                return $this->simpleReturn('1', 'Thanks for Subscribe !');
            }
            return $this->simpleReturn('error', 'Error in insertion', 500);

        }

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getFollower(Request $request)
    {
        $user = User::where('id', Auth::id())->with('user_info')->withCount('followers')->withCount('following')->first();
        return $this->simpleReturn('success', $user);

    }
}
