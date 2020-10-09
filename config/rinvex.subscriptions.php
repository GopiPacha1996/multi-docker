<?php

declare(strict_types=1);

return [

    // Subscriptions Database Tables
    'tables' => [

        'plans' => 'user_plans',
        'plan_features' => 'user_plan_features',
        'plan_subscriptions' => 'user_plan_subscriptions',
        'plan_subscription_usage' => 'user_plan_subscription_usage',

    ],

    // Subscriptions Models
    'models' => [

        'plan' => \Rinvex\Subscriptions\Models\Plan::class,
        'plan_feature' => \Rinvex\Subscriptions\Models\PlanFeature::class,
        'plan_subscription' => \App\Model\PlanSubscription::class,
        'plan_subscription_usage' => \Rinvex\Subscriptions\Models\PlanSubscriptionUsage::class,

    ],

];
