<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentPc\Controllers;

use DB;
use Illuminate\Http\Request;
use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\createRequest;
use Zhiyi\Plus\Models\AdvertisingSpace;

class FeedController extends BaseController
{
    /**
     * 动态首页/列表
     * @author Foreach
     * @param  Request $request
     * @return mixed
     */
    public function feeds(Request $request)
    {
        if($request->isAjax){
            if ($request->query('feed_id')){ // 获取单条微博内容
                $feeds['feeds'] = collect();
                $feed = createRequest('GET', '/api/v2/feeds/'.$request->feed_id);
                $feeds['feeds']->push($feed);
                $feedData = view('pcview::templates.feeds', $feeds, $this->PlusData)->render();

                return response()->json([
                        'status'  => true,
                        'data' => $feedData
                ]);
            } else { // 获取微博列表
                $params = [
                    'type' => $request->query('type'),
                    'after' => $request->query('after') ?: 0
                ];
                $feeds = createRequest('GET', '/api/v2/feeds', $params);
                if (!empty($feeds['pinned']) && $params['type'] != 'follow') { // 置顶动态
                    $feeds['pinned']->reverse()->each(function ($item, $key) use ($feeds) {
                        $item->pinned = true;
                        $feeds['feeds']->prepend($item);
                    });
                }

                $feeds['space'] =  $this->PlusData['config']['ads_space']['pc:feeds:list'] ?? [];
                $feeds['page'] = $request->loadcount;

                $feed = clone $feeds['feeds'];
                $after = $feed->pop()->id ?? 0;
                $feedData = view('pcview::templates.feeds', $feeds, $this->PlusData)->render();

                return response()->json([
                        'status'  => true,
                        'data' => $feedData,
                        'after' => $after
                ]);
            }
        }

        // 渲染模板
        $data['type'] = $request->input('type') ?: 'new';

        $this->PlusData['current'] = 'feeds';
        return view('pcview::feed.index', $data, $this->PlusData);
    }

    /**
     * 动态详情
     * @author Foreach
     * @param  Request $request
     * @param  int     $feed_id [动态id]
     * @return mixed
     */
    public function read(Request $request, int $feed_id)
    {
        $feed = createRequest('GET', '/api/v2/feeds/'.$feed_id);
        $feed->collect_count = $feed->collection->count();
        $feed->rewards = $feed->rewards->filter(function ($value, $key) {
            return $key < 10;
        });
        $data['feed'] = $feed;
        $data['user'] = $feed->user;

        $this->PlusData['current'] = 'feeds';
        return view('pcview::feed.read', $data, $this->PlusData);
    }

    /**
     * 动态评论列表
     * @author Foreach
     * @param  Request $request
     * @param  int     $feed_id [动态id]
     * @return \Illuminate\Http\JsonResponse
     */
    public function comments(Request $request, int $feed_id)
    {
        $params = [
            'after' => $request->query('after') ?: 0
        ];

        $comments = createRequest('GET', '/api/v2/feeds/'.$feed_id.'/comments', $params);
        $comment = clone $comments['comments'];
        $after = $comment->pop()->id ?? 0;

        if ($comments['pinneds'] != null) {

            $comments['pinneds']->each(function ($item, $key) use ($comments) {
                $item->top = 1;
                $comments['comments']->prepend($item);
            });
        }
        $commentData = view('pcview::templates.comment', $comments, $this->PlusData)->render();

        return response()->json([
            'status'  => true,
            'data' => $commentData,
            'after' => $after
        ]);
    }
}
