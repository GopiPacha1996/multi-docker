<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermService extends Model
{
    public $table = 'terms_service';

    public $fillable = [
        'title',
        'details',
        'sub_details',
        'type',
        'sort_order',
        'status',
    ];
}
