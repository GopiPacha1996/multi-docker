<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;

class ContactSales extends Model
{
    public $table = 'contact_sales';

    public $fillable = [
        'name',
        'email',
        'phone',
        'user_id',
        'subject',
        'query',
        'reply_query',
        'status',
        'created_at',
        'updated_at',
    ];

    public function user() {
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
}
