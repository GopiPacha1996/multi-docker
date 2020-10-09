<?php

namespace App;

use App\Model\Device;
use App\Model\Pc_Checkout;
use App\Model\Pc_Course;
use App\Model\Pc_Quiz;
use App\Model\Pc_QuizAttempt;
use App\Model\Pc_StudentsCourse;
use App\Model\Pc_Review;
use App\Model\SocialAccount;
use App\model\SubAdmin;
use App\Model\TeacherInfo;
use App\Model\UserBankData;
use App\Model\UserInfo;
use App\Model\Pc_Preference;
use App\Model\UserFollower;
use App\Model\OauthClients;
use App\Model\ModelHasRole;
use App\Traits\HasSubscriptions;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;
use Rinvex\Addresses\Traits\Addressable;
use Schedula\Laravel\PassportSocialite\User\UserSocialAccount;
use Spatie\Permission\Traits\HasRoles;
use App\Model\EducatorInsight;
use Illuminate\Support\Facades\Auth;

/**
 * Class User
 * @package App
 */
class User extends Authenticatable implements UserSocialAccount {
	use HasApiTokens, HasRoles, Notifiable, HasSubscriptions, Addressable;

	protected $fillable = [
		'name', 'email', 'phone', 'password', 'email_otp', 'phone_otp', 'provider', 'provider_id',
	];

	protected $hidden = [
		'password', 'remember_token', 'is_active', 'email_verified_at',
	];

	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	public function scopeActive($query) {
		return $query->where('is_active', 1);
	}

	public function teacher_info() {
		return $this->hasOne(TeacherInfo::class)->with('plan');
	}

	public function user_info() {
		return $this->hasOne(UserInfo::class)->with('country');
	}

	public function student_user_info() {
		return $this->hasOne(UserInfo::class, 'user_id', 'id');
	}

	public function client_info() {
		return $this->hasOne(OauthClients::class, 'user_id', 'id')->where('revoked', 0);
	}

	public function bank_info() {
		return $this->hasOne(UserBankData::class);
	}

	public function courses() {
		return $this->hasMany(Pc_Course::class);
	}

	public function published_courses() {
		return $this->hasMany(Pc_Course::class)->where('status', 'published');
	}

	public function coursesCount() {
		return $this->courses()
			->where('status', 'published')
			->selectRaw('user_id, count(*) as course_count')
			->groupBy('user_id');
	}

	public function following() {
		return $this->hasMany(UserFollower::class)->where('follow', '1');
	}

	public function is_following() {
		return $this->hasMany(UserFollower::class,'tutor_id','id')->where('user_id',Auth::id())->where('follow', '1');
	}

	public function followerCount() {
		return $this->followers()
			// ->where('follow', '1')
			->selectRaw('tutor_id,count(*) as user_count')
			->groupBy('tutor_id');
	}

	public function quizzes() {
		return $this->hasMany(Pc_Quiz::class);
	}

	public function quizzesCount() {
		return $this->quizzes()
			->selectRaw('user_id, count(*) as quiz_count')
			->groupBy('user_id');
	}

	public function findForPassport($username) {
		// return $this->where('is_active', '1')->where(function ($query) use ($username){
		//         $query->where('email', $username)->orWhere('phone', $username);
		//     })->get()->first();
		return $this->where(function ($query) use ($username) {
			$query->where('email', $username)->orWhere('phone', $username);
		})->get()->first();
	}

	public static function findForPassportSocialite($provider, $id) {
		$account = SocialAccount::where('provider', $provider)->where('provider_user_id', $id)->first();
		if ($account) {
			if ($account->user) {
				return $account->user;
			}
		}
		return;
	}

	public function student_courses() {
		return $this->hasMany(Pc_StudentsCourse::class, 'user_id', 'id')
			->with('course')
			->orderBy('id', 'desc');
	}

	public function ongoing_student_courses() {
		return $this->hasMany(Pc_StudentsCourse::class, 'user_id', 'id')
			->where('status', 'active')->with('course')->orderBy('id', 'desc');
	}

	public function student_quizzes() {
		return $this->hasMany(Pc_QuizAttempt::class, 'user_id', 'id')
			->with('quiz')->orderBy('id', 'desc');
	}

	public function student_transactions() {
		return $this->hasMany(Pc_Checkout::class, 'user_id', 'id')
			->where('status', 'success')
			->orderBy('id', 'desc');
	}

	public function student_preferences() {
		return $this->hasMany(Pc_Preference::class, 'user_id', 'id')->with('preference')->where('status', true);
	}

	public function student_reviews() {
		return $this->hasMany(Pc_Review::class, 'author_id', 'id')->where('status', 'active');
	}

	public function ratingAvg() {

		return  $this->student_reviews()
						->selectRaw('author_id,AVG(rating) as rating_avg')
						->groupBy('author_id');
	}


	public function rating_avg() {
		return $this->student_reviews()
			->selectRaw('author_id, avg(rating) as rating_avg')
			->groupBy('author_id');
	}


    public function devices() {
        return $this->hasMany(Device::class);
    }


    public function time_spent()
    {
        return $this->hasMany(TimeSpent::class, 'user_id', 'id');
    }

    public function preferences()
    {
        return $this->hasMany(Pc_Preference::class, 'user_id', 'id')
            ->where('status', '=', 1);
    }

    public function social_info()
    {
        return $this->hasOne(SocialAccount::class, 'user_id', 'id');
    }

	public function educator_insights()
	{
		return $this->hasOne(EducatorInsight::class, 'educator_id', 'id');
	}

  	public static function getCourseSoldCount($arr){
  		$sold_count = Pc_StudentsCourse::whereIn('course_id',$arr)
				    ->whereNotNull('txnid')
  					->get()
  					->count();
		return $sold_count;
	}


    public function subadmins(){
        return $this->hasMany(SubAdmin::class, 'user_id', 'id')
            ->where('active', true)
            ->where('expires_at', '>', now(env('APP_TIMEZONE', 'Asia/Kolkata')))
            ->with('oauth')
            ->with('menus')
			->with('menus.menu');
	}
	public function followers() {
		return $this->hasMany(UserFollower::class,'tutor_id','id')->where('follow', '1');
	}

	public function role_names()
	{
		return $this->hasMany(ModelHasRole::class, 'model_id','id')->where('model_id',Auth::id())->with('rolename');
	}

	public function role_name_user()
	{
		return $this->hasMany(ModelHasRole::class, 'model_id','id')->with('rolename');
	}


	public function accessTokens(){
	    return $this->hasMany(Token::class, 'user_id', 'id');
    }

    public function revokeAccessTokens(){
        try {
            $clientId = request()->header('ClientId');
            Log::info('Login request has come from clientId='. $clientId);
            if($clientId && $clientId > 3){
                Log::info('Total active token count=' . $this->accessTokens()->get()->count());
                if($this->accessTokens()->get()->count() > 0){
                    $clientInfo = Cache::remember('client-info-' . $clientId, 86400 , function() use ($clientId) {
                        Log::info('Fetching client details from db as cache is expired');
                        return OauthClients::where('id', $clientId)
                            ->where('issue_status', 2)
                            ->where('revoked', 0)
                            ->get();
                    });
                    $exempt = [2328,2337,2241,2240,1670,2177,2225,1761];
                    if(!($clientInfo && isset($clientInfo->id) && $clientInfo->id == $clientId) && !in_array($clientId, $exempt)){
                        $this->accessTokens()->each(function($token, $key) use ($clientId) {
                            if($token->client_id > 3 ) {
                                $token->delete();
                            }
                        });
                    }
                }
            }
        } catch (\Exception $exception){
            Log::error($exception);
        }
    }
}
