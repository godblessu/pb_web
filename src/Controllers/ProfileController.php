<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentPc\Controllers;

use Illuminate\Http\Request;
use Zhiyi\Plus\Models\User as UserModel;
use function zhiyi\Component\ZhiyiPlus\PlusComponentPc\replaceUrl;
use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\createRequest;

class ProfileController extends BaseController
{
    /**
     * 动态
     * @author Foreach
     * @param  Request     $request
     * @param  int|integer $user_id [用户id]
     * @return mixed
     */
    public function feeds(Request $request, UserModel $user)
    {
        $this->PlusData['current'] = 'feeds';
        if ($request->isAjax) {
            $params = [
                'type' => $request->query('type'),
                'user' => $request->query('user'),
                'after' => $request->query('after', 0),
            ];
            $cate = $request->query('cate', 1);
            switch ($cate) {
                case 1: //全部
                    $feeds = createRequest('GET', '/api/v2/feeds', $params);
                    $feed = clone $feeds['feeds'];
                    $after = $feed->pop()->id ?? 0;
                    $feeds['conw'] = 815;
                    $feeds['conh'] = 545;
                    $html = view('pcview::templates.feeds', $feeds, $this->PlusData)->render();
                    break;
                default:
                    # code...
                    break;
            }

            return response()->json(static::createJsonData([
                'status' => true,
                'after' => $after,
                'data' => $html
            ]));
        }
        $userInfo = createRequest('GET', '/api/v2/user');
        $currency_sum = $userInfo->currency->sum ?? 0;
        $user = $user->id ? $user : $request->user();
        $user->follower = $user->hasFollower($request->user()->id);
        $user->currency_sum = $currency_sum;

        return view('pcview::profile.index', compact('user'), $this->PlusData);
    }

    /**
     * 文章.
     * @author 28youth
     * @param  Request $request
     * @return mixed
     */
    public function news(Request $request, UserModel $user)
    {
        $this->PlusData['current'] = 'news';
        if ($request->isAjax) {
            $params = [
                'type' => $request->query('type'),
                'after' => $request->query('after', 0)
            ];
            if ($request->query('user')) {
                $params['user'] = $request->query('user');
            }
            $news = createRequest('GET', '/api/v2/user/news/contributes', $params);
            $news->map(function($item){
                $item->collection_count = $item->collections->count();
            });
            $new = clone $news;
            $after = $new->pop()->id ?? 0;
            $data['data'] = $news;
            $html = view('pcview::templates.profile_news', $data, $this->PlusData)->render();

            return response()->json([
                'status' => true,
                'after' => $after,
                'data' => $html
            ]);
        }
        $userInfo = createRequest('GET', '/api/v2/user');
        $currency_sum = $userInfo->currency->sum ?? 0;
        $user = $user->id ? $user : $request->user();
        $user->follower = $user->hasFollower($request->user()->id);
        $user->currency_sum = $currency_sum;
        $type = 0;

        return view('pcview::profile.news', compact('user', 'type'), $this->PlusData);
    }

    /**
     * 收藏
     * @author 28youth
     * @param  Request $request
     * @return mixed
     */
    public function collect(Request $request)
    {
        $this->PlusData['current'] = 'collect';
        if ($request->isAjax) {
            $cate = $request->query('cate', 1);
            switch ($cate) {
                case 1:
                    $params = [
                        'offset' => $request->query('offset', 0),
                        'limit' => $request->query('limit'),
                    ];
                    $feeds = createRequest('GET', '/api/v2/feeds/collections', $params);
                    $data['feeds'] = $feeds;
                    $after = 0;
                    $data['conw'] = 815;
                    $data['conh'] = 545;
                    $html = view('pcview::templates.feeds', $data, $this->PlusData)->render();
                    break;
                case 2:
                    $params = [
                        'after' => $request->query('after', 0),
                        'limit' => $request->query('limit', 10),
                    ];
                    $news = createRequest('GET', '/api/v2/news/collections', $params);
                    $news->map(function($item){
                        $item->collection_count = $item->collections->count();
                        $item->comment_count = $item->comments->count();
                    });
                    $new = clone $news;
                    $after = $new->pop()->id ?? 0;
                    $data['data'] = $news;
                    $html = view('pcview::templates.profile_news', $data, $this->PlusData)->render();
                    break;
                case 3:
                    $params = [
                        'after' => $request->query('after', 0),
                        'limit' => $request->query('limit', 10),
                    ];
                    $answers = createRequest('GET', '/api/v2/user/question-answer/collections', $params);

                    $answer = clone $answers;
                    $after = $answer->pop()->id ?? 0;
                    foreach ($answers as $k => $v) {
                        $v->collectible->liked = $v->collectible->liked($this->PlusData['TS']['id']);
                        $answers[$k] = $v->collectible;
                    }
                    $data['datas'] = $answers;
                    $html = view('pcview::templates.answer', $data, $this->PlusData)->render();
                    break;
                default:
                    # code...
                    break;
            }

            return response()->json([
                'status' => true,
                'after' => $after,
                'data' => $html
            ]);
        }
        $userInfo = createRequest('GET', '/api/v2/user');
        $currency_sum = $userInfo->currency->sum ?? 0;
        $user = $request->user();
        $user->currency_sum = $currency_sum;
        $type = 0;

        return view('pcview::profile.collect', compact('user', 'type'), $this->PlusData);
    }

    /**
     * 圈子
     * @author 28youth
     * @param  Request     $request
     * @param  int $user_id [用户id]
     * @return mixed
     */
    public function group(Request $request, UserModel $user)
    {
        $this->PlusData['current'] = 'group';
        if ($request->isAjax) {
            $type = (int) $request->query('type');
            $params = [
                'offset' => $request->query('offset', 0),
                'limit' => $request->query('limit', 10),
                'type' => $request->query('type', 'join'),
            ];
            if (!$type) {
                $data['type'] = $params['type'];
                $data['group'] = createRequest('GET', '/api/v2/plus-group/user-groups', $params);
                $html = view('pcview::templates.group', $data, $this->PlusData)->render();
            } else {
                $posts['pinneds'] = collect();
                $posts['posts'] = createRequest('GET', '/api/v2/plus-group/user-group-posts', $params);
                $html = view('pcview::templates.group_posts', $posts, $this->PlusData)->render();
            }

            return response()->json([
                'data' => $html,
            ]);
        }
        $type = 'join';
        $user = $user->id ? $user : $request->user();
        $user->follower = $user->hasFollower($request->user()->id);
        $userInfo = createRequest('GET', '/api/v2/user');
        $currency_sum = $userInfo->currency->sum ?? 0;
        $user->currency_sum = $currency_sum;

        return view('pcview::profile.group', compact('user', 'type'), $this->PlusData);
    }

    /**
     * 问答信息.
     * @author 28youth
     * @param  Request     $request
     * @param  int $user_id [用户id]
     * @return mixed
     */
    public function question(Request $request, UserModel $user)
    {
        $this->PlusData['current'] = 'question';
        if ($request->isAjax) {
            $cate = $request->query('cate', 1);
            switch ($cate) {
                case 1:
                    $params = [
                        'after' => $request->query('after', 0),
                        'type' => $request->query('type', 'all'),
                        'user_id' => $request->query('user_id'),
                    ];
                    $questions = createRequest('GET', '/api/v2/user/questions', $params);
                    $question = clone $questions;
                    $after = $question->pop()->id ?? 0;
                    $data['data'] = $questions;
                    $html = view('pcview::templates.question', $data, $this->PlusData)->render();

                    break;
                case 2:
                    $params = [
                        'after' => $request->query('after', 0),
                        'type' => $request->query('type', 'all'),
                        'user_id' => $request->query('user_id'),
                    ];
                    $answers = createRequest('GET', '/api/v2/user/question-answer', $params);
                    $answer = clone $answers;
                    $after = $answer->pop()->id ?? 0;
                    $data['datas'] = $answers;
                    $html = view('pcview::templates.answer', $data, $this->PlusData)->render();
                    break;
                case 3:
                    $params = [
                        'offset' => $request->query('offset', 0),
                        'limit' => $request->query('limit', 10),
                        'user_id' => $request->query('user_id'),
                    ];
                    $watches = createRequest('GET', '/api/v2/user/question-watches', $params);
                    $data['data'] = $watches;
                    $after = 0;
                    $html = view('pcview::templates.question', $data, $this->PlusData)->render();
                    break;
                case 4:
                    $params = [
                        'after' => $request->query('after', 0),
                        'type' => $request->query('type', 'follow'),
                        'user_id' => $request->query('user_id'),
                    ];
                    $topics = createRequest('GET', '/api/v2/user/question-topics', $params);
                    $topics->map(function($item){
                        $item->has_follow = true;
                    });
                    $topic = clone $topics;
                    $after = $topic->pop()->id ?? 0;
                    $data['data'] = $topics;
                    $html = view('pcview::templates.topic', $data, $this->PlusData)->render();
                    break;
            }

            return response()->json([
                'data' => $html,
                'after' => $after
            ]);
        }
        $user = $user->id ? $user : $request->user();
        $user->follower = $user->hasFollower($request->user()->id);
        $userInfo = createRequest('GET', '/api/v2/user');
        $currency_sum = $userInfo->currency->sum ?? 0;
        $user->currency_sum = $currency_sum;
        
        return view('pcview::profile.question', compact('user'), $this->PlusData);
    }
}
