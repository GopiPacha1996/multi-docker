<?php

namespace App\Http\Controllers\Api;

use App\Model\OauthClients;
use App\model\SubAdmin;
use App\Model\SubAdminMenu;
use App\Model\SubAdminHistory;
use App\Model\SubAdminPayment;
use App\User;
use Carbon\Carbon;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubAdminController extends Controller
{

    public function index(Request $request)
    {
        $perPageCount = $request->perPageCount ? $request->perPageCount : 15;
        $oauth = OauthClients::where('user_id', Auth::id())->where('revoked', false)
            ->where('issue_status', 2)
            ->whereDate('validity', '>=', now(env('APP_TIMEZONE', 'Asia/Kolkata')))
            ->first();
        if(!$oauth){
            return $this->simpleReturn('error', 'Could not find valid oauth details for user', 400);
        }
        if($request->access && (count(json_decode($request->access)) > 0)){
            $user_menu_ids = SubAdminMenu::whereIn('menu_setting_id', json_decode($request->access))->where('active', true)->pluck('sub_admin_id')->toArray();
        }

        $subadmins = SubAdmin::select('sub_admins.*')
            ->where('sub_admins.oauth_client_id', $oauth->id)
            ->join('users', 'users.id', '=', 'sub_admins.user_id')
            ->with('user')
            ->with('oauth')
            ->with('menus');

        if($request->search && ($request->search !='')) {
            $name = $request->search;
            $subadmins->where(function($query) use ($name){
                $query->where('users.phone', 'LIKE', '%'.$name.'%');
                $query->orWhere('users.email', 'LIKE', '%'.$name.'%');
            });
        }    

        if($request->active == "false"){
            $activeStatus = $request->active;
            $subadmins->where(function($query) use ($activeStatus){
                $query->where('sub_admins.active', false);
                $query->orWhere('sub_admins.expires_at', '<=', now(env('APP_TIMEZONE', 'Asia/Kolkata')));
            });
        }else{
            $subadmins->where('sub_admins.active', true);
            $subadmins->whereDate('sub_admins.expires_at', '>=', now(env('APP_TIMEZONE', 'Asia/Kolkata')));
        } 
        
        if(($request->start && $request->start != '')
            && ($request->end && $request->end !='')) {
            $subadmins->where('sub_admins.created_at', '>=', $request->start." 00:00:00")
                ->where('sub_admins.created_at' , '<=', $request->end." 23:59:59");
        }

        if($request->access && (count(json_decode($request->access)) > 0) && (count($user_menu_ids) > 0)){
            $subadmins->whereIn('sub_admins.id', $user_menu_ids);
        }

        if($request->sort == "oldest"){
            $subadmins->orderBy('sub_admins.id', 'asc');
        }else{
            $subadmins->orderBy('sub_admins.id', 'desc');
        }
        
        $subadmins = $subadmins->paginate($perPageCount);    
        if($subadmins){    
            return $this->simpleReturn('success', $subadmins);
        }
        return $this->simpleReturn('error', 'No data found', 404);
    }

    
    public function store(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'oauth_client_id' => 'required',
            'menus' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        }

        try {
            $menus = explode(',', $request->menus);
            if(count($menus) < 1){
                return $this->simpleReturn('error', 'Please select atleast one menu option', 400);
            }

            $subAdminPayment = SubAdminPayment::where('id', $request->subadmin_payment_id)->first();
            if(!$subAdminPayment){
                return $this->simpleReturn('error', 'Could not find valid subscription payment', 400);
            }

            $expires = Carbon::parse($request->expires_at, env('APP_TIMEZONE', 'Asia/Kolkata'));
            // $oauth = OauthClients::where('id', $request->oauth_client_id)->where('revoked', false)
            //     ->where('issue_status', 2)
            //     ->whereDate('validity', '>=', $subAdminPayment->plan_end)
            //     ->first();
            // if(!$oauth){
            //     return $this->simpleReturn('error', 'Could not find valid oauth details for oauth_client_id', 400);
            // }

            $user = User::where('id', $request->user_id)->first();
            if(!$user){
                return $this->simpleReturn('error', 'User not found', 404);
            }

            $exists = SubAdmin::where('oauth_client_id', $request->oauth_client_id)->where('user_id', $request->user_id)->where('active', true)->whereDate('expires_at', '>=', now(env('APP_TIMEZONE', 'Asia/Kolkata')))->first();
            if($exists){
                return $this->simpleReturn('error', 'Duplicate sub admin is not allowed', 409);
            }

            $subadmin = SubAdmin::create([
                'user_id' => $request->user_id,
                'oauth_client_id' => $request->oauth_client_id,
                'expires_at' => $subAdminPayment->plan_end,
                'subadmin_payment_id' => $request->subadmin_payment_id,
            ]);
            if($subadmin){
                $user->assignRole('sub-admin');
            }

            foreach(json_decode($request->menus) as $key => $value) {
                SubAdminMenu::create([
                    'sub_admin_id' => $subadmin->id,
                    'menu_setting_id' => $value,
                ]);
            }

            return $this->simpleReturn('success', 'Subadmin added successfully');
        } catch (\Exception $exception){
            Log::error($exception);
            return $this->simpleReturn('error', 'Server error. Please contact support', 500);
        }
    }

    public function enableOrDisableSubAdmin(Request $request)
    {
        $rules = [
            'sub_admin_id' => 'required',
            'action' => 'required|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        }

        SubAdmin::where('id', $request->sub_admin_id)->update([
            'active' => $request->action
        ]);

        $res = $request->action == true ? 'Subadmin activated!!' : 'Subadmin deactivated!!';
        return $this->simpleReturn('success', $res);
    }

    public function revokeMenuAccess(Request $request)
    {
        $rules = [
            'sub_admin_id' => 'required',
            'menu_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        }

        SubAdminMenu::where('sub_admin_id', $request->sub_admin_id)->where('menu_setting_id', $request->menu_id)->update([
            'active' => false
        ]);

        return $this->simpleReturn('success', 'Menu access revoked successfully');
    }

    public function update(Request $request)
    {
        $rules = [
            'sub_admin_id' => 'required',
            'menus' => 'required',
            'subadmin_payment_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        }

        try {
            $menus = explode(',', $request->menus);
            if(count($menus) < 1){
                return $this->simpleReturn('error', 'Please select atleast one menu option', 400);
            }

            $subAdminPayment = SubAdminPayment::where('id', $request->subadmin_payment_id)->first();
            if(!$subAdminPayment){
                return $this->simpleReturn('error', 'Could not find valid subscription payment', 400);
            }

            $expires = Carbon::parse($request->expires_at, env('APP_TIMEZONE', 'Asia/Kolkata'));
            $subadmin = SubAdmin::find($request->sub_admin_id);
            if($subadmin) {
                $subadmin->expires_at = $subAdminPayment->plan_end;
                $subadmin->subadmin_payment_id = $request->subadmin_payment_id;
                $subadmin->save();
            }

            foreach(json_decode($request->menus) as $key => $menu) {
                $subadminmenu = SubAdminMenu::where('sub_admin_id', $request->sub_admin_id)->where('menu_setting_id', $menu)->first();
                if($subadminmenu){
                    if($subadminmenu->active == false){
                        $subadminmenu->active = true;
                        $subadminmenu->save();
                    }
                } else {
                    SubAdminMenu::create([
                        'sub_admin_id' => $request->sub_admin_id,
                        'menu_setting_id' => $menu,
                    ]);
                }
            }

            $remaining = SubAdminMenu::where('sub_admin_id', $request->sub_admin_id)->whereNotIn('menu_setting_id', json_decode($request->menus))->get();
            foreach ($remaining as $subadminmenu){
                if($subadminmenu->active == true){
                    $subadminmenu->active = false;
                    $subadminmenu->save();
                }
            }
        } catch (\Exception $exception){
            Log::error($exception);
            return $this->simpleReturn('error', 'Server error. Please contact support', 500);
        }

        return $this->simpleReturn('success', 'Menu has been updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $sub_admin = SubAdmin::where('id', $id)->first();
		if($sub_admin){
            $sub_admin_delete = SubAdmin::where('id', $id)->update([
                'active' => false,
            ]);
            if($sub_admin_delete){
                return $this->simpleReturn('success', 'Successfully deleted');
            }
            return $this->simpleReturn('error', 'deletion error', 400);
		}
		return $this->simpleReturn('error', 'No data found', 404);
    }


    public function saveSubAdminActivity(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'oauth_client_id' => 'required',
            'type_id' => 'required',
            'type' => 'required',
            'menu' => 'required',
            'action' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors());
        }

        try {

            $user = User::where('id', $request->user_id)->first();
            if(!$user){
                return $this->simpleReturn('error', 'User not found', 404);
            }

            $exists = SubAdmin::where('oauth_client_id', $request->oauth_client_id)->where('user_id', $request->user_id)->first();
            if(!$exists){
                return $this->simpleReturn('error', 'Sub Admin not found', 404);
            }

            $saveActivity = SubAdminHistory::create([
                'sub_admin_id' => $exists->id,
                'type_id' => $request->type_id,
                'type' => $request->type,
                'action' => $request->action,
                'menu' => $request->menu,
                'comment' => $request->comment,
            ]);
            if($saveActivity){
                return $this->simpleReturn('success', 'Subadmin activity recorded successfully');
            }
            return $this->simpleReturn('error', 'insertion error', 400);

        } catch (\Exception $exception){
            Log::error($exception);
            return $this->simpleReturn('error', 'Server error. Please contact support', 500);
        }
    }

    public function listSubAdminActivity(Request $request)
    {
        $perPageCount = $request->perPageCount ? $request->perPageCount : 15;

        $oauth = OauthClients::where('user_id', Auth::id())->where('revoked', false)
            ->where('issue_status', 2)
            ->whereDate('validity', '>=', now(env('APP_TIMEZONE', 'Asia/Kolkata')))
            ->first();
        if(!$oauth){
            return $this->simpleReturn('error', 'Could not find valid oauth details for user', 400);
        }

        $subadminActivity = SubAdminHistory::select('sub_admin_histories.*')
            ->where('sub_admins.oauth_client_id', $oauth->id)
            ->join('sub_admins', 'sub_admins.id', '=', 'sub_admin_histories.sub_admin_id')
            ->join('users', 'users.id', '=', 'sub_admins.user_id')
            ->with('subadmin')
            ->orderBy('sub_admin_histories.id', 'desc');

        if($request->search && ($request->search !='')) {
            $name = $request->search;
            $subadminActivity->where(function($query) use ($name){
                $query->where('users.phone', 'LIKE', '%'.$name.'%');
                $query->orWhere('users.email', 'LIKE', '%'.$name.'%');
            });
        }    
        
        $subadminActivity = $subadminActivity->paginate($perPageCount);    
        if($subadminActivity){    
            return $this->simpleReturn('success', $subadminActivity);
        }
        return $this->simpleReturn('error', 'No data found', 404);
    }
}
