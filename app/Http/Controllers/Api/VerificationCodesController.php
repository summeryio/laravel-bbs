<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Overtrue\EasySms\EasySms;
use App\Http\Requests\Api\VerificationCodesRequest;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodesRequest $request, EasySms $easySms)
    {
        $phone = $request->phone;

        // 生成4位随机数，左侧补0
        /*$code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);

        try {
            $result = $easySms->send($phone, [
                'template' => config('easysms.gateways.aliyun.templates.register'),
                'data' => [
                    'code' => $code
                ],
            ]);
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            $message = $exception->getException('qcloud')->getMessage();
            abort(500, $message ?: '短信发送异常');
        }*/

        // 判断线上环境，发送短信验证码
        if (!app()->environment('production')) {
            $code = '1234';
        } else {
            $sms = app('easysms');
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);
            try {
                $sms->send($phone, [
                    'content'  => "【叶兹利的前端技术记录】验证码为：{$code}，您正在登录，若非本人操作，请勿泄露。",
                ]);
            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                $message = $exception->getException('qcloud')->getMessage();
                return $this->response->errorInternal($message ?: '短信发送异常');
            }
        }


        $key = 'verificationCode_'.Str::random(15);
        $expiredAt = now()->addMinutes(5);
        // 缓存验证码 5 分钟过期。
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);

        return response()->json([
            'key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ])->setStatusCode(201);
    }
}
