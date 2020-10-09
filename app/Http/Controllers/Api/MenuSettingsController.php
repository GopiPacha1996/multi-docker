<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\MenuSettings;
use Illuminate\Support\Facades\Auth;
use App\Model\OauthClients;
use App\Model\SubAdmin;
use Validator;
use Carbon\Carbon;
class MenuSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $menu_settings = MenuSettings::where('status','active')->with('parent');
        if(($request->type) && ($request->type != '')){
            $menu_settings->where('user_type',$request->type);
        }
        if(($request->keyword) && ($request->keyword != '')){
            $menu_settings->where('title','like', '%' .$request->keyword. '%');
        }

        $menu_settings =$menu_settings->get();
        if ($menu_settings) {
            return $this->simpleReturn('success', $menu_settings);
        }
        return $this->simpleReturn('error', 'No data found', 404);
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
        $rules = array(
            'user_type' => 'required',
            'title' => 'required',
            'routes' => 'required',
            'icons' => 'required',
            'sort_order' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $service = MenuSettings::create([
            'user_type' => $request->user_type,
            'title' => $request->title,
            'routes' => $request->routes,
            'icons' => $request->icons,
            'sort_order' => $request->sort_order,
            'parent_id' => $request->parent_id,
            'plan_id' => $request->plan_id,
            'is_locked' => $request->is_locked ? $request->is_locked :false,
            'is_parent' => $request->is_parent ? $request->is_parent : false,
            'is_new' => $request->is_new ? $request->is_new : false,
            'type' => $request->type,
            'status' => 'active',
        ]);

        if ($service) {

            return $this->simpleReturn('success', 'Inserted Successfully');
        }
        return $this->simpleReturn('error', 'Error in insertion', 400);
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
        $rules = array(
            'user_type' => 'required',
            'title' => 'required',
            'routes' => 'required',
            'icons' => 'required',
            'sort_order' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        $service = MenuSettings::where('id', $id)->first();
        if ($service) {
            $service->user_type = $request->user_type;
            $service->title = $request->title;
            $service->routes = $request->routes;
            $service->icons = $request->icons;
            $service->sort_order = $request->sort_order;
            $plan_id = $request->plan_id;
            $is_locked = $request->is_locked ? $request->is_locked :false;
            $is_parent = $request->is_parent ? $request->is_parent : false;
            $is_new = $request->is_new ? $request->is_new : false;
            $type = $request->type;
            if($request->parent_id){
                $service->parent_id = $request->parent_id;
            }

            $service->save();
            if($service){
                return $this->simpleReturn('success', 'Updated Successfully');
            }
        }
        return $this->simpleReturn('error', 'Error in updation', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $menu_settings = MenuSettings::where('id', $id)->first();
		if($menu_settings){
            $menu_settings_delete = MenuSettings::where('id', $id)->update([
                'status' => 'deleted',
            ]);
            if($menu_settings_delete){
                return $this->simpleReturn('success', 'Successfully deleted');
            }
            return $this->simpleReturn('error', 'deletion error', 400);
		}
		return $this->simpleReturn('error', 'No data found', 404);
    }

    public function fetchUserMenu(Request $request)
    {
        if($request->user_type == "sub-admin"){
            $subscription_applicable = false;
            $validity_expired = false;
            $oauth_exists = false;
            $oauth = OauthClients::where('id', $request->header('AdminClientId'))->where('revoked', 0)->first();
            if($oauth){
                $oauth_exists = true;
                $validity = Carbon::parse($oauth->validity)->timezone('Asia/Kolkata');
                $validity_expired = $validity->isPast() ? true : false;
            }
            $blocked =  false;
            if ( $oauth_exists && $validity_expired ) {
                $blocked =  true;
            }
            $user_plan_exists = SubAdmin::where('oauth_client_id', $request->header('AdminClientId'))->where('user_id', Auth::user()->id)->whereDate('expires_at', '>=', now(env('APP_TIMEZONE', 'Asia/Kolkata')))->where('active', true)->first();
            if(!$user_plan_exists){
                $blocked =  true;
            }

            $user_exists = SubAdmin::where('oauth_client_id', $request->header('AdminClientId'))->where('user_id', Auth::user()->id)->where('active', true)->first();
            if($user_exists){
                $menu_setting = MenuSettings::select('menu_settings.*')
                    ->join('sub_admin_menus', 'sub_admin_menus.menu_setting_id', 'menu_settings.id')
                    ->where('menu_settings.status','active')
                    ->where('menu_settings.user_type','teacher')
                    ->where('sub_admin_menus.active',true)
                    ->where('sub_admin_menus.sub_admin_id',$user_exists->id);
                if($request->title){
                    $menu_setting->where('title',$request->title);
                } 
                if($request->is_locked){
                    $menu_setting->where('is_locked',$request->is_locked);
                }   
                $menu_settings['items'] = $menu_setting->orderBy('sort_order','asc')->groupBy('title')->get();
                $menu_settings['subscription_applicable'] = true;
                $menu_settings['subscription_expired'] = false;
                $menu_settings['oauth_exists'] = false;
                $menu_settings['blocked'] = $blocked;
            }else{
                $menu_setting = MenuSettings::select('menu_settings.*')
                    ->join('sub_admin_menus', 'sub_admin_menus.menu_setting_id', 'menu_settings.id')
                    ->where('menu_settings.status','active')
                    ->where('sub_admin_menus.sub_admin_id',$user_exists->id);
                $menu_settings['items'] = $menu_setting->orderBy('sort_order','asc')->groupBy('title')->get();
                $menu_settings['oauth_exists'] = false;
                $menu_settings['blocked'] = $blocked;
                $menu_settings['subscription_applicable'] = false;
                $menu_settings['subscription_expired'] = true;
            }            

        }else{
            
            $subscription_applicable = false;
            $validity_expired = false;
            $oauth_exists = false;
            $oauth = OauthClients::where('user_id', Auth::user()->id)->where('revoked', 0)->first();
            if($oauth){
                $oauth_exists = true;
                $validity = Carbon::parse($oauth->validity)->timezone('Asia/Kolkata');
                $validity_expired = $validity->isPast() ? true : false;
            }

            $menu_setting = MenuSettings::where('status','active')->where('user_type',$request->user_type);

            $user_subscriptions = Auth::user()->getSubscription('mobile');
            if(($user_subscriptions) &&($request->user_type != 'student')) {
                $subscription_applicable = true;
                $menu_setting->where('plan_id',$user_subscriptions->plan_id);
            }else{
                $menu_setting->where('plan_id','0');
            }

            if($request->is_locked){
                $menu_setting->where('is_locked',$request->is_locked);
            }

            if($request->title){
                $menu_setting->where('title',$request->title);
            }

            $menu_settings['items'] = $menu_setting->orderBy('sort_order','asc')->groupBy('title')->get();
            $menu_settings['subscription_applicable'] = $subscription_applicable;
            $menu_settings['subscription_expired'] = $validity_expired;
            $menu_settings['oauth_exists'] = $oauth_exists;

            $blocked =  false;
            if ( $oauth_exists && $validity_expired ) {
                $blocked = true;
            }
            $menu_settings['blocked'] = $blocked;
        }

        if ($menu_settings) {
            return $this->simpleReturn('success', $menu_settings);
        }
        return $this->simpleReturn('error', 'No data found', 404);
    }
}
