<?php

namespace App\Http\Controllers;

use App\Model\Pc_BaseSettings;
use App\Model\Pc_Course;
use App\Model\Pc_Notifications;
use App\Model\Plan;
use App\Model\PlanFeatures;
use App\Model\SocialAccount;
use App\Model\UserBankData;
use App\Model\UserInfo;
use App\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Storage;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function simpleReturn($status = '', $response = array(), $code = 200)
    {
        $out['status'] = $status;
        $out['response'] = is_string($response) ? array('msg' => $response) : $response;
        $out['code'] = $code;
        return response()->json($out, $code);
    }

    public function userProfileDp($id, $size = '')
    {
        $user_info = UserInfo::where('user_id', $id)->get()->first();
        $default = 'default.png';

        $dp = '';
        if ($user_info) {
            $dp = Storage::disk('profile')->exists($user_info->profile_pic) ? $user_info->profile_pic : '';
        }

        if ($dp) {
            return $this->mediaUrl('profile/' . $dp);

        } else {
            $sa = SocialAccount::where('user_id', $id)->get()->first();
            if ($sa) {
                if ($sa->provider_user_avatar) {
                    return $sa->provider_user_avatar;
                }
            }
            return $this->mediaUrl('profile/' . $default);
        }
    }

    public function getBankStatement($id, $size = '')
    {
        $pic = '';
        $bank_info = UserBankData::where('user_id', $id)->get()->first();
        if ($bank_info) {
            $pic = Storage::disk('bankData')->exists($bank_info->bank_statement) ? $bank_info->bank_statement : '';
        }
        return $pic ? $this->mediaUrl('bank-details/' . $pic) : null;
    }

    public static function mediaUrl($file = '')
    {
        return asset("media/{$file}");
    }

    public function createFileName($file, $user_id, $prefix = '')
    {
        if ($file) {
            $ext = '.' . $file->getClientOriginalExtension();
            $name = substr(str_replace(' ', '', str_replace('.', '', $file->getClientOriginalName())), 0, 4);

            return $prefix . $user_id . '_' . $name . mt_rand(11111, 99999) . $ext;
        }
        return '';
    }

    public function getPlanFeatures($id)
    {
        $data = PlanFeatures::where('plan_id', $id)->get();
        return $feature[] = $data;
    }

    public function getPlan($id)
    {
        $data = Plan::where('id', $id)->get()->first();
        return $data;
    }

    public function getUserBasic($id)
    {
        $data = User::where('id', $id)->get()->first();
        return $data;
    }

    public function getPlanName($id)
    {
        $data = Plan::where('id', $id)->get()->first();
        return $data->name;
    }

    public function get_bs($id, $val = false, $field = false)
    {
        $bs = Pc_BaseSettings::where('id', $id)->where('status', 'active')->get()->first();
        if ($bs) {
            $item['id'] = $bs->id;
            $item['value'] = $bs->value;
            $item['type'] = $bs->type;
            $item['created_at'] = $bs->created_at;
            $item['parent_id'] = $bs->parent_id;
            $item['status'] = $bs->status;
            $item['others'] = $bs->others;
            // return $val ? $bs->value : $item;
            return $val ? ($field ? ($item[$field] ? $item[$field] : '') : $bs->value) : $item;
        }
        return '';
    }

    public function get_course($cid)
    {
        return Pc_Course::where('id', $cid)->get()->first();
    }

    public function getUserDetails($id)
    {
        $data = UserInfo::where('user_id', $id)->get()->first();
        return $data;
    }

    public function userPic($id, $size = '')
    {
        $default = 'default.png';
        $pic = UserInfo::where('user_id', $id)->select(['profile_pic'])->get()->first();
        $dp = $pic->profile_pic;
        $dp = $dp ? $dp : $default;
        return $this->mediaUrl('userPic/' . $dp);
    }

    public function notification($user_id, $done_by = null, $type, $title, $content)
    {
        $add = new Pc_Notifications();
        $add->user_id = $user_id;
        $add->done_by = $done_by;
        $add->type = $type;
        $add->title = $title;
        $add->content = $content;
        if ($add->save()) {
            return true;
        }
        return false;
    }

}
