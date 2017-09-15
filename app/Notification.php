<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'notification_id';
    protected $guarded = ['notification_id'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'notification_from' => 'array',
    ];

    /**
     * Notifications belongs to a single user
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'user_id');
    }
}
