<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Pc_Course;
use App\User;
use App\Model\Pc_Quiz;

class AdminDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $course_count = Pc_Course::where('status', 'published')->count();
        $student_count = User::role('student')->where('is_active', 1)->count();
        $teacher_count = User::role(['educator', 'institute'])->where('is_active', 1)->count();
        $quiz_count = Pc_Quiz::where('visible', 1)->count();
        $approvals_count = Pc_Course::where('status', '!=' ,'published')->count();

        $result['course_count'] = $course_count;
        $result['student_count'] = $student_count;
        $result['teacher_count'] = $teacher_count;
        $result['quiz_count'] = $quiz_count;
        $result['approvals_count'] = $approvals_count;

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
