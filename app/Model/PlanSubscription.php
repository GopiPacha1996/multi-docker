<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Rinvex\Subscriptions\Models\PlanSubscription as RinvexPlanSubscription;

class PlanSubscription extends RinvexPlanSubscription
{

    public static function bootCacheableEloquent(): void
    {
    }

    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }
}
