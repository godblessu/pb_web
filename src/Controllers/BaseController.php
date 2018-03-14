<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentPc\Controllers;

use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Config\Repository;
use Zhiyi\Plus\Http\Controllers\Controller;
use Zhiyi\Plus\Models\User;
use Zhiyi\Plus\Models\Comment as CommentModel;
use Zhiyi\Plus\Models\JWTCache as JWTCacheModel;
use Zhiyi\Component\ZhiyiPlus\PlusComponentPc\Models\Navigation;
use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\createRequest;
use Zhiyi\Component\ZhiyiPlus\PlusComponentPc\Traits\CreateJsonResponseData;

class BaseController extends Controller
{
    use CreateJsonResponseData;

    protected $PlusData;

    public function __construct()
    {
        // 初始化
        $this->middleware(function($request, $next) {
            // 用户认证
            $user = $request->user();
            if ($user) {
                $user->load('wallet', 'extra', 'tags', 'currency');
                $user->makeVisible(['phone', 'email']);
            }

            $token = $this->PlusData['token'] = $request->session()->get('token');
            $this->PlusData['TS'] = null;
            if ($user) {
                $jwt = app(\Tymon\JWTAuth\JWT::class);
                if (! $token || ! $jwt->setToken($token)->check()) {
                    $token = $this->PlusData['token'] = $jwt->fromUser($user);
                    $request->session()->put('token', $token);
                    $request->session()->save();
                }

                $this->PlusData['TS'] = clone $user;
                $this->PlusData['TS']['avatar'] = $this->PlusData['TS']['avatar'] ?: asset('assets/pc/images/avatar.png');
            }

            // 站点配置
            $config = Cache::get('config');

            if (!$config) {
                $config = [];

                // 启动信息接口
                $config['bootstrappers'] = createRequest('GET', '/api/v2/bootstrappers/', [], 0);
                $config['bootstrappers']['wallet:ratio'] = ($config['bootstrappers']['wallet:ratio'] ?? 100) / 100 / 100;
                $config['bootstrappers']['site']['reward']['amounts'] = '5,10,15';

                // 基本配置
                $repository = app(\Illuminate\Contracts\Config\Repository::class);
                $config['common'] = $repository->get('pc');
                $config['app'] = $repository->get('app');

                // 顶部导航
                $config['nav'] = Navigation::byPid(0)->byPos(0)->orderBy('order_sort')->get();

                // 底部导航
                $config['nav_bottom'] = Navigation::byPid(0)->byPos(1)->get();

                // 获取所有广告位
                $config['ads_space'] = createRequest('GET', '/api/v2/advertisingspace', [], 0)->keyBy('space');

                // 环信
                $easemob = $repository->get('easemob');
                $config['easemob_key'] = $easemob['app_key'];

                // 小助手
                if (isset($config['bootstrappers']['im:helper'])) {
                    foreach ($config['bootstrappers']['im:helper'] as $key => &$value) {
                        $value['name'] = User::where('id', $value['uid'])->value('name');
                    }
                }

                // 缓存配置信息
                Cache::forever('config', $config);
            }

            $this->PlusData['config'] = $config;

            // 公共地址
            $this->PlusData['routes']['api'] = asset('/api/v2');
            $this->PlusData['routes']['storage'] = asset('/api/v2/files'). '/';
            
            // 环信相关用户
            $this->PlusData['easemob_users'] = isset($_COOKIE['easemob_uids']) ? User::whereIn('id', explode(',', $_COOKIE['easemob_uids']))->get() : [];

            return $next($request);
        });
    }


    /**
     * 操作提示
     * @author ZsyD
     * @param  int    $status  [状态]
     * @param  string $url     [跳转链接]
     * @param  string $message [信息]
     * @param  string $content [内容]
     * @param  int    $time    [跳转时间]
     * @return mixed
     */
    public function notice(Request $request)
    {
        $data['status'] = $request->query('status', 1);
        $data['message'] = $request->query('message');
        $data['content'] = $request->query('content');
        $data['url'] = $request->query('url');
        $data['time'] = $request->query('time', 3);

        return view('pcview::templates.notice', $data, $this->PlusData);
    }

    /**
     * 查看资源页面
     *
     * @param Request $request
     * @return mixed
     * @author BS <414606094@qq.com>
     */
    public function reportView(Request $request)
    {
        $reportable_id = $request->query('reportable_id');
        $reportable_type = $request->query('reportable_type');
        if (! $reportable_id || ! $reportable_type) {
            return abort(404);
        }

        switch ($reportable_type) {
            case 'users':
                return response()->redirectTo(route('pc:mine', ['user' => $reportable_id]), 302);
                break;
            case 'comments': // 评论部分暂时跳转到所属资源的详情页
                $comment = CommentModel::find($reportable_id);
                if (! $comment) {
                    return abort(404);
                }
                return response()->redirectTo(route('pc:reportview', ['reportable_id' => $comment->commentable_id, 'reportable_type' => $comment->commentable_type]), 302);
                break;
            case 'feeds':
                return response()->redirectTo(route('pc:feedread', ['feed' => $reportable_id]), 302);
                break;
            case 'questions':
                return response()->redirectTo(route('pc:questionread', ['question_id' => $reportable_id]), 302);
                break;
            case 'question-answers':
                return response()->redirectTo(route('pc:answeread', ['answer' => $reportable_id]), 302);
                break;
            case 'news':
                return response()->redirectTo(route('pc:newsread', ['news_id' => $reportable_id]), 302);
                break;
            case 'groups':
                return response()->redirectTo(route('pc:groupread', ['group_id' => $reportable_id]), 302);
                break;
            default:
                return abort(404);
                break;
        }
    }
}

