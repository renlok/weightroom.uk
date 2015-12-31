<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

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
    protected $hidden = ['user_password', 'remember_token'];

    protected $dates = ['user_joined'];

    protected $casts = [
        'user_beta' => 'boolean',
        'user_admin' => 'boolean',
        'user_weekstart' => 'boolean',
        'user_showreps' => 'array',
    ];

    public function getAuthPassword() {
        return $this->user_password;
    }

    public function getAuthIdentifierName () {
        return $this->user_id;
    }

    /**
     * a user can many logs
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function logs()
    {
        $this->hasMany('App\Log');
    }

    /**
     * a user can many notifications
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function notifications()
    {
        $this->hasMany('App\Notification');
    }

    /**
     * a user can many invite codes
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function invite_codes()
    {
        $this->hasMany('App\Invite_code');
    }

    /**
     * a user can many user follows
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function user_follows()
    {
        $this->hasMany('App\User_follow');
    }
}
