<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements  MustVerifyEmailContract, JWTSubject
{
    use MustVerifyEmailTrait;

    use Notifiable {
        notify as protected  laravelNotify;
    }

    public function notify($instance)
    {
        if ($this->id == Auth::id()) {
            return;
        }

        // 只有数据库类型通知才需提醒，直接发送 Email 或者其他的都 Pass
        if (method_exists($instance, 'toDatabase')) {
            $this->increment('notification_count');
        }

        $this->laravelNotify($instance);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'introduction', 'avatar', 'phone', 'weixin_openid', 'weixin_unionid'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'weixin_openid', 'weixin_unionid'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function topics() {
        return $this->hasMany(Topic::class);
    }

    // 判断是否为当前登录用户，在policy中使用
    public function isAuthOf($model) {
        return $this->id == $model->user_id;
    }

    // 一个用户可以拥有多条评论
    public function replies() {
        return $this->hasMany(Reply::class);
    }

    // 将所有通知状态设定为已读，并清空未读消息数
    public function markAsRead() {
        $this->notification_count = 0;
        $this->save();

        // 这里unreadNotifications不加()
        $this->unreadNotifications->markAsRead();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
