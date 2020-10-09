<?php


namespace App\Http\Controllers\Api\V2;


use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Model\PartnerData;
use App\Model\OauthClients;
use App\User;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;


class PartnerDataController extends Controller
{

    public function getData()
    {
        $date = Carbon::today()->toDateString();
        $data = Cache::remember('partner-data-'.$date, 86400 , function() {
            return PartnerData::where('id',1)->first();
        });
        return $this->simpleReturn('success',$data);
    }

    public function update()
    {
    	Log::info('Update Partner Data');
        $user_count = $this->getUserCount();
    	$course_count = $this->getCourseCount();
        list($mock_package_count,$ebook_package_count) = $this->getMockEbookCount();
        $institute_count = $this->getInstituteCount();

        $update = PartnerData::find(1);
        if($update){
            $update->courses = $course_count;
            $update->mock_packages = $mock_package_count;
            $update->ebook_packages = $ebook_package_count;
            $update->users = $user_count;
            $update->institutes = $institute_count;
            $update->save();
        } else{
            $new_record = new PartnerData();
            $new_record->courses = $course_count;
            $new_record->mock_packages = $mock_package_count;
            $new_record->ebook_packages = $ebook_package_count;
            $new_record->users = $user_count;
            $new_record->institutes = $institute_count;
            $new_record->save();
        }


        return;
    }

    public function getUserCount()
    {
        $user_count = User::where('is_active',1)
                            ->count();

        return $user_count;
    }

    public function getInstituteCount()
    {
        $institute_count = OauthClients::max('id');

        return $institute_count;
    }

    public function getCourseCount()
    {
        $link = '/api/v2/course/count';
        $course_count = 0;
        $http = new \GuzzleHttp\Client;
        try {
            $response = $http->get(env('URL_PATHSHALA_COURSE') . $link);
            $output = json_decode($response->getbody());
            return $output->response;

        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            Log::info('Error Code'.$e->getCode());
            return $course_count;
        }

        return $course_count;
    }

    public function getMockEbookCount()
    {
        $link = '/api/v2/mocktest/ebook/count';
        $mock_count = 0;
        $ebook_count = 0;

        $http = new \GuzzleHttp\Client;
        try {
            $response = $http->get(env('URL_PATHSHALA_MOCKTEST') . $link);

            $output = json_decode($response->getbody());

            return array($output->data->mock_count,$output->data->ebook_count);

        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            Log::info('Error Code'.$e->getCode());
            return array($mock_count,$ebook_count);
        }
        return array($mock_count,$ebook_count);
    }
}
