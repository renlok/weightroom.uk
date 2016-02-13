<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invite_code extends Model
{
    protected $primaryKey = 'code_id';

    public function scopeValid($query, $user_id)
    {
        return $query->where('code_expires', '>=', Carbon::now()->format('Y-m-d'))
                    ->where('code_uses', '>', 0)
                    ->orWhere(function ($query) {
                        global $user_id;
                        $query->where('user_id', '=', $user_id)
                        ->where('user_id', '=', 0);
                    });
    }

    public function scopeIsvalid($query, $code)
    {
        return $query->where('code_expires', '>=', Carbon::now()->format('Y-m-d'))->where('code_uses', '>', 0)->where('code', '=', $code);
    }

    /**
     * invite codes belongs to a single user
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
