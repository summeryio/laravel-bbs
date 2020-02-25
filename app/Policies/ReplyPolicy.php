<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reply;

class ReplyPolicy extends Policy
{
    public function destroy(User $user, Reply $reply)
    {
        // 拥有删除回复权限的用户，回复的作者 || 话题的作者
        return $user->isAuthOf($reply) || $user->isAuthOf($reply->topic);
    }
}
