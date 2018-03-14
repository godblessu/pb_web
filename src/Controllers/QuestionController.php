<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentPc\Controllers;

use Illuminate\Http\Request;
use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\createRequest;

class QuestionController extends BaseController
{
    /**
     * 问答
     * @author 28youth
     * @param  Request $request
     * @return mixed
     */
    public function question(Request $request)
    {
        $this->PlusData['current'] = 'question';
        if ($request->isAjax){
            $params = [
                'offset' => $request->query('offset', 0),
                'limit' => $request->query('limit', 0),
                'type' => $request->query('type', 'all'),
            ];
            $question['data'] = createRequest('GET', '/api/v2/questions', $params);
            if ($params['type'] == 'excellent') {
                $question['data']->map(function ($item) {
                    $item->excellent_show = false;
                });
            }
            $html = view('pcview::templates.question', $question, $this->PlusData)->render();

            return response()->json([
                'status' => true,
                'data' => $html
            ]);
        }

        return view('pcview::question.index', [], $this->PlusData);
    }

    /**
     * 话题
     * @author 28youth
     * @param  Request $request
     * @return mixed
     */
    public function topic(Request $request)
    {
        if ($request->isAjax){
            $cate = $request->query('cate', 1);
            switch ($cate) {
                case 1:
                    $params = [
                        'offset' => $request->query('offset', 0),
                        'limit' => $request->query('limit', 10),
                        'follow' => $request->query('follow', 1),
                    ];
                    $data['data'] = createRequest('GET', '/api/v2/question-topics', $params);
                    $after = '';
                    break;
                case 2:
                    $params = [
                        'after' => $request->query('after', 0),
                        'limit' => $request->query('limit', 10),
                    ];
                    $questions = createRequest('GET', '/api/v2/user/question-topics', $params);
                    $questions->map(function($item){
                        $item->has_follow = true;
                    });
                    $question = clone $questions;
                    $after = $question->pop()->id ?? 0;
                    $data['data'] = $questions;
                    break;
            }
            $html = view('pcview::templates.topic', $data, $this->PlusData)->render();

            return response()->json([
                'status' => true,
                'after' => $after,
                'data' => $html
            ]);
        }

        return view('pcview::question.topic', [], $this->PlusData);
    }

    /**
     * 话题详情.
     * @author ZsyD
     * @param Request $request
     * @param int $topic [话题id]
     * @return mixed
     */
    public function topicInfo(Request $request, int $topic)
    {
        $data['topic'] = createRequest('GET', '/api/v2/question-topics/'.$topic );
        $data['experts'] = createRequest('GET', '/api/v2/question-topics/'.$topic . '/experts');

        return view('pcview::question.topic_info', $data, $this->PlusData);
    }

    /**
     * 问题详情
     * @author ZsyD
     * @param  int    $question_id [问题id]
     * @return mixed
     */
    public function read(int $question_id)
    {
        $this->PlusData['current'] = 'question';

        $question = createRequest('GET', '/api/v2/questions/'.$question_id);
        $data['question'] = $question;

        return view('pcview::question.read', $data, $this->PlusData);

    }

    /**
     * 回答详情
     * @author ZsyD
     * @param  int    $answer [回答id]
     * @return mixed
     */
    public function answer(int $answer)
    {
        $answer = createRequest('GET', '/api/v2/question-answers/'.$answer );
        $answer->collect_count = $answer->collectors->count();
        $answer->rewarders = $answer->rewarders->reverse();
        $data['answer'] = $answer;
        $data['answer']->user->hasFollower = $data['answer']['user']->hasFollower($this->PlusData['TS']['id']);
        return view('pcview::question.answer', $data, $this->PlusData);
    }

    /**
     * [回答评论列表]
     * @author ZsyD
     * @param  Request $request
     * @param  int     $answer  [回答id]
     * @return \Illuminate\Http\JsonResponse
     */
    public function answerComments(Request $request, int $answer)
    {
        $params = [
            'after' => $request->query('after', 0),
            'limit' => $request->query('limit', 10),
        ];
        $comments = createRequest('GET', '/api/v2/question-answers/'.$answer.'/comments', $params);
        $comment = clone $comments;
        $after = $comment->pop()->id ?? 0;
        $data['comments'] = $comments;
        $data['top'] = false;

        $html = view('pcview::templates.comment', $data, $this->PlusData)->render();

        return response()->json([
            'status' => true,
            'after' => $after,
            'data' => $html
        ]);
    }

    /**
     * 创建/修改问题
     * @author ZsyD
     * @param  Request     $request
     * @param  int|integer $question_id [问题id]
     * @return [type]                   [description]
     */
    public function createQuestion(Request $request, int $question_id = 0)
    {
        if ($topic_id = $request->query('topic_id')) {
            $data['topic'] = createRequest('GET', '/api/v2/question-topics/'.$topic_id );
        }

        if ($question_id > 0) {
            $data['question'] = createRequest('GET', '/api/v2/questions/'.$question_id );
        }

        $data['topics'] = createRequest('GET', '/api/v2/question-topics');

        return view('pcview::question.create_question', $data, $this->PlusData);
    }

    /**
     * 邀请用户
     * @author ZsyD
     * @param  Request $request
     * @return mixed
     */
    public function getUsers(Request $request)
    {
        $ajax = $request->input('ajax');
        $params['limit'] = $request->input('limit') ?: 10;
        $params['topics'] = is_array($request->input('topics')) ? implode(',', $request->input('topics')) : $request->input('topics') ?: '';
        $params['keyword'] = $request->input('keyword') ?: '';
        $data['topics'] = $params['topics'];
        if ($ajax == 1) {
            $search = $request->input('search');
            if ($search == 1) {
                $url = '/api/v2/user/search';
            } else {
                $url = '/api/v2/question-experts';
            }
            $data['users'] = createRequest('GET', $url, $params);
            $return = view('pcview::question.user_list', $data)
                ->render();

            return response()->json([
                'status'  => true,
                'data' => $return,
            ]);
        } else {
            return view('pcview::question.users', $data, $this->PlusData);
        }
    }

    /**
     * 回答列表
     * @author ZsyD
     * @param  Request $request
     * @param  int     $question_id [问题id]
     * @return mixed
     */
    public function getAnswers(Request $request, int $question_id)
    {
        $params['limit'] = $request->input('limit') ?: 10;
        $params['offset'] = $request->input('offset') ?: 0;
        $params['order_type'] = $request->input('order_type') ?: 'time';
        $data['answers'] = createRequest('GET', '/api/v2/questions/'.$question_id.'/answers', $params);
        if ($params['offset'] == 0) {
            $question = createRequest('GET', '/api/v2/questions/'.$question_id );
            if (!empty($question['adoption_answers'])) { // 采纳回答
                $question['adoption_answers']->each(function ($item, $key) use ($data) {
                    $data['answers']->prepend($item);
                });
            }
            if (!empty($question['invitation_answers'])) { // 悬赏人回答
                $question['invitation_answers']->each(function ($item, $key) use ($data) {
                    $data['answers']->prepend($item);
                });
            }
        }
        $return = view('pcview::question.question_answer', $data, $this->PlusData)
            ->render();

        return response()->json([
            'status'  => true,
            'data' => $return,
        ]);
    }

    /**
     * 问题评论列表
     * @author ZsyD
     * @param  Request $request
     * @param  int     $question [问题id]
     * @return \Illuminate\Http\JsonResponse
     */
    public function questionComments(Request $request, int $question)
    {
        $params = [
            'after' => $request->query('after', 0),
            'limit' => $request->query('limit', 10),
        ];
        $comments = createRequest('GET', '/api/v2/questions/'.$question.'/comments', $params);
        $comment = clone $comments;
        $after = $comment->pop()->id ?? 0;
        $data['comments'] = $comments;
        $data['top'] = false;
        $html = view('pcview::templates.comment', $data, $this->PlusData)->render();

        return response()->json([
            'status' => true,
            'after' => $after,
            'data' => $html
        ]);
    }

    /**
     * 话题-更多专家列表
     * @author ZsyD
     * @param Request $request
     * @param int $topic [话题id]
     * @return mixed
     */
    public function topicExpert(Request $request, int $topic)
    {
        if ($request->isAjax) {
            $limit = $request->input('limit') ?: 18;
            $after = $request->input('after') ?: 0;
            $params = [
                'limit' => $limit,
                'after' => $after,
            ];
            $data['users'] = createRequest('GET', '/api/v2/question-topics/'.$topic.'/experts', $params);
            $after = $data['users']->pop()->id ?? 0;

            $html =  view('pcview::templates.user', $data, $this->PlusData)->render();

            return response()->json([
                'status'  => true,
                'data' => $html,
                'after' => $after
            ]);
        }

        $this->PlusData['current'] = 'question';
        $data['topic'] = $topic;

        return view('pcview::question.topic_experts', $data, $this->PlusData);
    }

    /**
     * 话题-问题列表
     * @author ZsyD
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topicQuestion(Request $request)
    {
        $this->PlusData['current'] = 'question';
        $topic_id = $request->input('topic_id');
        $params = [
            'offset' => $request->input('offset', 0),
            'limit' => $request->input('limit', 10),
            'type' => $request->input('type', 'all'),
        ];
        $question['data'] = createRequest('GET', '/api/v2/question-topics/'.$topic_id.'/questions', $params);
        if ($params['type'] == 'excellent') {
            $question['data']->map(function ($item) {
                $item->excellent_show = false;
            });
        }
        $html = view('pcview::templates.question', $question, $this->PlusData)->render();

        return response()->json([
            'status' => true,
            'data' => $html
        ]);
    }

    /**
     * 答案编辑
     * @author ZsyD
     * @param  Request $request
     * @param  int     $answer  [答案id]
     * @return mixed
     */
    public function editAnswer(Request $request, int $answer)
    {
        $answer = createRequest('GET', '/api/v2/question-answers/'.$answer );
        $data['answer'] = $answer;

        return view('pcview::question.answer_edit', $data, $this->PlusData);
    }
}