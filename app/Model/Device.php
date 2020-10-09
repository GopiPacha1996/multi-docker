<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use SoftDeletes;


    public $table = 'devices';

    protected $hidden = [
        'deleted_at',
    ];

    public $fillable = [
        'deviceToken',
        'deviceUUID',
        'deviceOS',
        'deviceMake',
        'deviceOSVersion',
        'deviceModel',
        'deviceUsername',
        'user_id'
    ];

    public static function tokenExists($deviceToken)
    {
        $exists = self::where('deviceToken', $deviceToken)
            ->count();
        return $exists ? true : false;
    }

    public static function deviceExists($deviceUUID)
    {
        $exists = self::where('deviceUUID', $deviceUUID)
            ->count();
        return $exists ? true : false;
    }

    public static function findByUUID($deviceUUID)
    {
        return self::where('deviceUUID', $deviceUUID)
            ->where('active', true)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    public static function findByPlatformAndUser($user_id, $platform)
    {
        return self::where('user_id', $user_id)
            ->where('platform', $platform)
            ->get();
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
