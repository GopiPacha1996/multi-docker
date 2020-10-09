<?php

namespace App\Model;
use App\User;
use App\Model\OauthClients;
use Illuminate\Database\Eloquent\Model;

class RazorSubscription extends Model
{
    protected $table = 'subscriptions';

    protected $fillable = [
        'plan_id',
        'user_id',
        'entity',
        'customer_id',
        'subscription_id',
        'short_url',
        'status',
        'current_start',
        'current_end',
        'ended_at',
        'start_at',
        'end_at',
        'created_at',
        'expire_by',
        'quantity',
        'total_count',
        'paid_count',
        'remaining_count',
        'customer_notify',
    ];

    public function plan()
    {
        return $this->belongsTo(RazorPlan::class, 'plan_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function client_info() {
		return $this->hasOne(OauthClients::class, 'user_id', 'user_id');
	}
}
