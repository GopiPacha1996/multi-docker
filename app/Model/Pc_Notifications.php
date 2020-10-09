<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Pc_Notifications extends Model
{ 
    protected $connection = 'mysql_course';
    protected $table = 'notifications';


    public static function getAllById($id, $types=[])
    {
        return self::where('user_id', $id)
            ->whereIn('type', $types)
            ->orderBy('created_at', 'DESC')
            ->take(10)
            ->get();

    }

    public static function markReadById($user_id, $id)
    {
        $update = array(
            'read' => 1,
        );

        $notification = self::where('id', $id)
            ->where('user_id', $user_id)
            ->update($update);


        return $notification;
    }

}
