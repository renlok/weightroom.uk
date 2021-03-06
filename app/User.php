<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Laravel\Cashier\Billable;
use Laravel\Passport\HasApiTokens;
use Cache;
use Auth;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable, Billable, HasApiTokens;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['user_id', 'user_beta', 'user_admin', 'user_don_level'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_password',
        'remember_token',
        'stripe_active',
        'stripe_id',
        'stripe_plan',
        'card_brand',
        'card_last_four',
        'stripe_custom_id'
    ];

    protected $dates = [
        'user_joined',
        'trial_ends_at',
        'subscription_ends_at'
    ];

    protected $casts = [
        'user_firstlog' => 'boolean',
        'user_beta' => 'boolean',
        'user_admin' => 'boolean',
        'user_showreps' => 'array',
        'user_showextrareps' => 'array',
        'stripe_active' => 'boolean'
    ];

    protected $appends = ['user_volumewarmup'];

    public function getAuthPassword() {
        return $this->user_password;
    }

    public function getAuthIdentifier () {
        return $this->user_id;
    }

    public function getAuthIdentifierName () {
        return 'user_id';
    }

    public function getUserVolumewarmupAttribute () {
        if ($this->attributes['user_volumeincwarmup'])
            return 0;
        else
            return 1;
    }

    public function getNotificationsAttribute()
    {
        return Notification::where('user_id', $this->attributes['user_id'])->get();
    }

    public static function findForPassport($username)
    {
        return User::where('user_name', $username)->first();
    }

    public static function activeTemplate($user_id) {
        $active_template = User::where('user_id', $user_id)->first()->template;
        if ($active_template == null) {
            return null;
        } else {
            return $active_template->template_id;
        }
    }

    public function setUserShowrepsAttribute($value) {
        if (is_array($value)) {
            $this->attributes['user_showreps'] = '[' . implode(',', array_map('intval', $value)) . ']';
        } else {
            $this->attributes['user_showreps'] = '[]';
        }
    }

    public function scopeUserlike($query, $username)
    {
        return $query->where(function ($query) use ($username) {
                            $query->where('user_id', $username)
                            ->orWhere('user_name', 'LIKE', '%'.$username.'%');
                        })
                        ->pluck('user_name');
    }

    public static function shadowBanList()
    {
        /*$list = Cache::remember('shadow_ban_list', 1440, function () {
            return User::where('user_shadowban', 1)->pluck('user_id');
        });*/
        $list = User::where('user_shadowban', 1)->pluck('user_id')->toArray();
        // if user is shadow banned remove themselves from the list
        if (Auth::check() && Auth::user()->user_shadowban)
        {
            $key = array_search(Auth::user()->user_id, $list);
            if ($key !== false) {
                $list = array_splice($list, $key + 1, 1);
            }
        }
        return $list;
    }

    /**
     * a user can many logs
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function logs()
    {
        return $this->hasMany('App\Log', 'user_id', 'user_id');
    }

    /**
     * a user can many notifications
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function notifications()
    {
        return $this->hasMany('App\Notification', 'user_id', 'user_id');
    }

    /**
     * a user can many invite codes
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function invite_codes()
    {
        return $this->hasMany('App\Invite_code', 'user_id', 'user_id');
    }

    /**
     * a user can many user follows
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function user_follows()
    {
        return $this->hasMany('App\User_follow', 'user_id', 'user_id');
    }

    /**
     * a user can many user exercise groups
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function exercise_groups()
    {
        return $this->hasMany('App\Exercise_group', 'user_id', 'user_id');
    }

    /**
     * user has one template
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function template()
    {
        return $this->hasOne('App\User_template', 'user_id', 'user_id');
    }

    public function getForeignKey()
    {
        return $this->primaryKey;
    }
}
