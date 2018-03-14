<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentPc\Controllers;


use Session;
use Overtrue\Socialite\SocialiteManager;
use Illuminate\Http\Request;
use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\socialiteRequest;

class SocialiteController extends BaseController
{
    /**
     * 三方登录/绑定（未登录）
     * @author ZsyD
     * @param Request $request
     * @param string  $service [三方类型]
     * @return mixed
     */
    public function redirectToProvider(Request $request, $service)
    {
        $config[$service] = $this->PlusData['config']['common'][$service];
        $config[$service]['redirect'] = $this->PlusData['routes']['siteurl'].'/socialite/'.$service.'/callback';

        $socialite = new SocialiteManager($config);

        $response = $socialite->driver($service)->redirect();

        $response->send();
    }
    
    /**
     * 三方登录/绑定（已登录）
     * @author ZsyD
     * @param  Request $request
     * @param  string  $service [三方类型]
     * @return mixed
     */
    public function redirectToProviderByBind(Request $request, $service)
    {
        if ($this->PlusData['TS']->phone == null) {

            return $this->notice(0, Route('pc:binds'), '绑定失败', '绑定第三方账号必须绑定手机号码');
        }
        $config[$service] = $this->PlusData['config']['common'][$service];
        $config[$service]['redirect'] = $this->PlusData['routes']['siteurl'].'/socialite/'.$service.'/callback?type=bind';

        $socialite = new SocialiteManager($config);

        $response = $socialite->driver($service)->redirect();

        $response->send();
    }

    /**
     * 第三方回调页
     * @author ZsyD
     * @param  Request $request
     * @param  string  $service [三方类型]
     * @return mixed
     */
    public function handleProviderCallback(Request $request, $service)
    {
        $config[$service] = $this->PlusData['config']['common'][$service];
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $config[$service]['redirect'] = $this->PlusData['routes']['siteurl'].'/socialite/'.$service.'/callback'.($type != '' ? '?type=bind' : '');

        $socialite = new SocialiteManager($config);
        $user = $socialite->driver($service)->user();
        $access_token = $user->getToken()->access_token;

        // 已登录时账号绑定
        if ($type == 'bind') {
            $res = socialiteRequest('PATCH', '/api/v2/user/socialite/'.$service, ['access_token' => $access_token]);

            return isset($res['message'])
                ? $this->notice(0, Route('pc:binds'), '绑定失败', $res['message'])
                : $this->notice(1, Route('pc:binds'), '绑定成功', '您的账号已成功绑定');

        } else {
        // 未登录时账号注册/绑定
            $res = socialiteRequest('POST', '/api/v2/socialite/'.$service, ['access_token' => $access_token]);

            if (isset($res['token'])) { // 登录
                $return = [
                    'status' => 1,
                    'message' => '登录成功',
                    'data' => [
                        'token' => $res['token'],
                    ],
                ];

            } else { // 绑定、注册
                $return = [
                    'status' => -1,
                    'message' => '正在前往绑定窗口...',
                    'data' => [
                        'other_type' => $service,
                        'access_token' => $access_token,
                        'name' => $user->getName(),
                    ],
                ];
            }

            return view('pcview::socialite.socialite', $return, $this->PlusData);
        }

        return;
    }
    
    /**
     * 三方用户注册/绑定账号（未登录时）
     * @author ZsyD
     * @param  Request $request
     * @return mixed
     */
    public function bind(Request $request)
    {
        $data = $request->input();

        return view('pcview::socialite.bind', $data, $this->PlusData);
    }
}