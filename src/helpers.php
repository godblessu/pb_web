<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentPc;

use Auth;
use Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use SlimKit\PlusSocialite\API\Requests\AccessTokenRequest;
use Illuminate\Support\Facades\Route;
use Zhiyi\Plus\Models\User;

function getShort($str, $length = 40, $ext = '')
{
    $str = nl2br($str);
    $str = addslashes($str);
    $str = trim($str);
    $str = strip_tags($str);
    $str = htmlspecialchars_decode($str);
    $strlenth = 0;
    $out = '';
    $output = '';
    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/", $str, $match);
    foreach ($match[0] as $v) {
        preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $v, $matchs);
        if (!empty($matchs[0])) {
            $strlenth += 1;
        } elseif (is_numeric($v)) {
            //$strlenth +=  0.545;  // 字符像素宽度比例 汉字为1
            $strlenth += 0.5;    // 字符字节长度比例 汉字为1
        } else {
            //$strlenth +=  0.475;  // 字符像素宽度比例 汉字为1
            $strlenth += 0.5;    // 字符字节长度比例 汉字为1
        }

        if ($strlenth > $length) {
            $output .= $ext;
            break;
        }

        $output .= $v;
    }

    return $output;
}

function formatContent($content)
{
    // 链接替换
    $content = preg_replace_callback('/((?:https?|mailto|ftp):\/\/([^\x{2e80}-\x{9fff}\s<\'\"“”‘’，。}]*)?)/u', function($url){
        return '<a href="'.$url[0].'" target="_blank" style="color:#59b6d7;">访问链接+</a>';
    }, $content);

    // 回车替换
    $pattern = array("\r\n","\n","\r");
    $replace = '<br>';
    $content = str_replace($pattern, $replace, $content);

    return $content;
}

function createRequest($method = 'POST', $url = '', $params = array(), $instance = 1, $original = 1)
{
    $request = Request::create($url, $method, $params);
    $request->headers->add(['Accept' => 'application/json', 'Authorization' => 'Bearer '. Session::get('token')]);

    // 注入JWT请求单例
    app()->resolving(\Tymon\JWTAuth\JWT::class, function ($jwt) use ($request) {
        $jwt->setRequest($request);

        return $jwt;
    });
    Auth::guard('api')->setRequest($request);

    // 解决获取认证用户
    $request->setUserResolver(function() {
        return Auth::user('api');
    });

    // 解决请求传参问题
    if ($instance) { // 获取登录用户不需要传参
        app()->instance(Request::class, $request);
    }

    $response = Route::dispatch($request);
    return $original ? $response->original : $response;
}

function socialiteRequest($method = 'POST', $url = '', $params = array(), $instance = 1, $original = 1)
{
    $request = AccessTokenRequest::create($url, $method, $params);
    $request->headers->add(['Accept' => 'application/json', 'Authorization' => 'Bearer '. Session::get('token')]);

    // 注入JWT请求单例
    app()->resolving(\Tymon\JWTAuth\JWT::class, function ($jwt) use ($request) {
        $jwt->setRequest($request);

        return $jwt;
    });
    Auth::guard('api')->setRequest($request);

    // 解决获取认证用户
    $request->setUserResolver(function() {
        return Auth::user('api');
    });

    // 解决请求传参问题
    if ($instance) { // 获取登录用户不需要传参
        app()->instance(Request::class, $request);
    }

    $response = Route::dispatch($request);
    return $original ? $response->original : $response;
}


function getTime($time, int $type = 1, int $format = 1)
{
    // 本地化
    Carbon::setLocale('zh');

    $timezone = isset($_COOKIE['customer_timezone']) ? $_COOKIE['customer_timezone'] : 0;
    // 一小时内显示文字
    if ((Carbon::now()->subHours(24) < $time) && $format) {
        return $time->diffForHumans();
    }
    return $type ? $time->addHours($timezone)->toDateString() : $time->addHours($timezone);
}

function getImageUrl($image = array(), $width, $height, $cut = true, $blur = 0)
{
    if (!$image) { return false; }
    // 高斯模糊参数
    $b = $blur != 0 ? '&b=' . $blur : '';

    // 裁剪
    $file = $image['file'] ?? $image['id'];
    if ($cut) {
        $size = explode('x', $image['size']);
        if ($size[0] > $size[1]) {
            $width = number_format($height / $size[1] * $size[0], 2, '.', '');
        } else {
            $height = number_format($width / $size[0] * $size[1], 2, '.', '');
        }
        return asset('/api/v2/files/'.$file) . '?&w=' . $width . '&h=' . $height . $b . '&token=' . Session::get('token');
    } else {
        return asset('/api/v2/files/'.$file) . '?token=' . Session::get('token') . $b;
    }

}

function getUserInfo($id)
{
    return User::find($id);
}

/**
 * 清除缓存
 * @author ZsyD
 * @return void
 */
function cacheClear()
{
   return Artisan::call('cache:clear');
}

function getAvatar($user, $width = 0)
{
    if ($user['avatar']) {
        $avatar = $user['avatar'];
    } else {
        if(isset($user['sex'])) {
            switch ($user['sex']) {
                case 1:
                    $avatar = asset('assets/pc/images/pic_default_man.png');
                    break;
                case 2:
                    $avatar = asset('assets/pc/images/pic_default_woman.png');
                    break;
                default:
                    $avatar = asset('assets/pc/images/pic_default_secret.png');
                    break;
            }
        } else {
            $avatar = asset('assets/pc/images/avatar.png');
        }
    }

    $width && $avatar .= '?s='.$width;

    return $avatar;
}

function formatMarkdown($body)
{
    // 图片替换
    $body = preg_replace('/\@\!\[(.*?)\]\((\d+)\)/i', '![$1](' . getenv('APP_URL') . '/api/v2/files/$2)', $body);

    return htmlspecialchars_decode(\Parsedown::instance()->setMarkupEscaped(true)->text($body));
}

function formatList($body)
{
    $body = preg_replace('/\@\!\[(.*?)\]\((\d+)\)/', '[图片]', $body);

    return  htmlspecialchars_decode(\Parsedown::instance()->setMarkupEscaped(true)->text($body));
}
