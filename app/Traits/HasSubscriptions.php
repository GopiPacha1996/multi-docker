<?php


namespace App\Traits;


trait HasSubscriptions
{
    use \Rinvex\Subscriptions\Traits\HasSubscriptions;

    public function getSubscription($type){
        return $this->subscriptions()
            ->where('type', $type)
            ->where('ends_at', '>', now(env('APP_TIMEZONE', 'Asia/Kolkata')))
            ->orderBy('id', 'desc')
            ->first();
    }
}
