<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class NotificationsController extends Controller
{
    public function __construct()
    {
        // 要求必须登录以后才能访问控制器里的所有方法。
        $this->middleware('auth');
    }

    public function index() {
        $notifications = Auth::user()->notifications()->paginate(20);
        Auth::user()->markAsRead();

        return view('notifications.index', compact('notifications'));
    }
}
