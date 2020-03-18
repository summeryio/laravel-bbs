<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Overtrue\EasySms\EasySms;
use App\Http\Requests\Api\VerificationCodesRequest;
use Illuminate\Auth\AuthenticationException;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodesRequest $request, EasySms $easySms)
    {
        $captchaData = \Cache::get($request->captcha_key);

        if (!$captchaData) {
            abort(403, '图片验证码已失效');
        }

        //abort(403, $captchaData['code']);
        if (!hash_equals($captchaData['code'], $request->captcha_code)) {
            // 验证错误就清除缓存
//            \Cache::forget($request->captcha_key);

            throw new AuthenticationException('验证码错误');
        }


        $phone = $captchaData['phone'];

        // 判断线上环境，发送短信验证码
        if (!app()->environment('production')) {
            $code = '1234';
        } else {
            $sms = app('easysms');
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT); // 生成4位随机数，左侧补0
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
