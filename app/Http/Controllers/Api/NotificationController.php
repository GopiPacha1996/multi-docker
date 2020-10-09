<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Pc_Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;
use stdClass;

class NotificationController extends Controller
{

    private $course_push_type = 'course_push_notification';
    private $quiz_push_type = 'quiz_push_notification';
    private $live_push_type = 'live_push_notification';
    private $mock_push_type = 'mock_push_notification';


    public function index()
    {
        $responseArray = array();

        try{
            $notifications = Pc_Notifications::getAllById(Auth::id(), [$this->course_push_type, $this->quiz_push_type, $this->live_push_type]);
            foreach ($notifications as $index => $notification) {
                $response = new stdClass();
                $response->notification_id = $notification->id;
                $response->title = $notification->title;
                $response->sound = 'default';
                $response->read = $notification->read;
                $response->dtime = Carbon::parse($notification->created_at)->timezone('Asia/Kolkata')->diffForHumans();

                $contentArray = json_decode($notification->content, TRUE);
                switch ($notification->type){
                    case $this->course_push_type :
                        $response->channel = 'course';
                        $response->body = $contentArray['body'];
                        $response->course_id = $contentArray['content_id'];
                        break;
                    case $this->quiz_push_type :
                        $response->channel = 'quiz';
                        $response->body = $contentArray['body'];
                        $response->quiz_id = $contentArray['content_id'];
                        break;
                    case $this->live_push_type :
                        $response->channel = 'live';
                        $response->body = $contentArray['body'];
                        break;
                }

                array_push($responseArray, $response);
            }


        } catch (Exception $e){
            Log::error('Error while fetching notification content', e);
            return $this->simpleReturn('error',"Something went wrong, contact support!");
        }

        return $this->simpleReturn('success',$responseArray);
    }


    public function read(Request $request, $channel, $id)
    {
        $notification = Pc_Notifications::findOrfail($id);
        if ($channel != 'mocktest' && $notification) {
            Pc_Notifications::markReadById(Auth::id(), $id);
        }

        return $this->simpleReturn('success','Updated successfully');
    }
}
