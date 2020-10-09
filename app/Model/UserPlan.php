<?php

namespace App\Model;

use Rinvex\Subscriptions\Models\Plan as RinvexPlan;

class UserPlan extends RinvexPlan
{
    /**
     * Indicate if the model cache clear is enabled.
     *
     * @var bool
     */
    protected $cacheClearEnabled = true;
    protected $cacheLifetime = -1;
    protected $cacheDriver = null;

    protected $ignoreCache = ['created', 'updated'];
}
