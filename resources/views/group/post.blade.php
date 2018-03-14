@section('title') {{ $post->title }} @endsection

@php
    use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\getTime;
    use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\getAvatar;
    use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\formatMarkdown;
@endphp

@extends('pcview::layouts.default')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/pc/markdown/pluseditor.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/pc/css/feed.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/pc/css/group.css') }}"/>
@endsection

@section('content')
<div class="p-post">
    <div class="left_container">
        <div class="feed_left">
            <dl class="user-box clearfix">
                <dt class="fl">
                    <a class="avatar_box" href="{{ route('pc:mine', $post->user_id) }}">
                    <img class="round" src="{{ getAvatar($post->user, 50) }}" width="50" class="avatar">
                    @if($post->user->verified)
                    <img class="role-icon" src="{{ $post->user->verified['icon'] or asset('assets/pc/images/vip_icon.svg') }}">
                    @endif
                    </a>
                </dt>
                <dd class="fl ml20 body-box">
                    <span>{{ $post->user->name }}</span>
                    <div class="ucolor mt10 font14">
                        <span>发布时间  {{ getTime($post->created_at) }}</span>
                        <span class="ml20">浏览量  {{ $post->views_count}}</span>
                    </div>
                </dd>
                @if (isset($post->group->joined))
                <dd class="fr mt20 relative">
                    <span class="options" onclick="options(this)">
                        <svg class="icon icon-more" aria-hidden="true"><use xlink:href="#icon-more"></use></svg>
                    </span>
                    <div class="options_div">
                        <div class="triangle"></div>
                        <ul>
                        @if (isset($post->group->joined) && in_array($post->group->joined->role, ['administrator', 'founder']))
                        <li>
                            <a href="javascript:;" onclick="post.pinnedPost('{{$post->id}}');">
                                <svg class="icon" aria-hidden="true"><use xlink:href="#icon-pinned"></use></svg>
                                <span>置顶帖子</span>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;" onclick="post.delPost('{{$post->group_id}}', '{{$post->id}}', 'read');">
                                <svg class="icon" aria-hidden="true"><use xlink:href="#icon-delete"></use></svg>
                                <span>删除</span>
                            </a>
                        </li>
                        @else
                            @if($post->user_id == $TS['id'])
                                @if (!$post->pinned && isset($post->group->joined) && !$post->group->joined->disabled)
                                <li>
                                    <a href="javascript:;" onclick="post.pinnedPost('{{$post->id}}', 'pinned');">
                                        <svg class="icon" aria-hidden="true"><use xlink:href="#icon-pinned2"></use></svg>
                                        <span>申请置顶</span>
                                    </a>
                                </li>
                                @endif
                                <li>
                                    <a href="javascript:;" onclick="post.delPost('{{$post->group_id}}', '{{$post->id}}', 'read');">
                                        <svg class="icon" aria-hidden="true"><use xlink:href="#icon-delete"></use></svg>
                                        <span>删除</span>
                                    </a>
                                </li>
                            @else
                                @if (isset($post->group->joined) && !$post->group->joined->disabled)
                                <li>
                                    <a href="javascript:;" onclick="reported.init('{{$post->id}}', 'posts');">
                                        <svg class="icon" aria-hidden="true"><use xlink:href="#icon-report"></use></svg>
                                        <span>举报</span>
                                    </a>
                                </li>
                                @endif
                            @endif
                        @endif
                        </ul>
                    </div>
                </dd>
                @endif
            </dl>
            <h3 class="u-tt">{{$post->title}}</h3>
            <div class="detail_body markdown-body">

            {!! formatMarkdown($post->body) !!}
            </div>
            <div class="detail_share">
                <span id="J-collect{{ $post->id }}" rel="{{ $post->collectors->count() }}" status="{{(int) $post->collected}}">
                    @if($post->collected)
                    <a class="act" href="javascript:;" onclick="collected.init({{$post->id}}, 'group', 0);">
                        <svg class="icon" aria-hidden="true"><use xlink:href="#icon-collect"></use></svg>
                        <font class="cs">{{ $post->collectors->count() }}</font> 人收藏
                    </a>
                    @else
                    <a href="javascript:;" onclick="collected.init({{$post->id}}, 'group', 0);">
                        <svg class="icon" aria-hidden="true"><use xlink:href="#icon-collect"></use></svg>
                        <font class="cs">{{ $post->collectors->count() }}</font> 人收藏
                    </a>
                    @endif
                </span>
                <span class="digg" id="J-likes{{$post->id}}" rel="{{$post->likes_count}}" status="{{(int) $post->liked}}">
                    @if($post->liked)
                    <a class="act" href="javascript:void(0)" onclick="liked.init({{$post->id}}, 'group', 0)">
                        <svg class="icon" aria-hidden="true"><use xlink:href="#icon-like"></use></svg>
                        <font>{{$post->likes_count}}</font> 人喜欢
                    </a>
                    @else
                    <a href="javascript:;" onclick="liked.init({{$post->id}}, 'group', 0)">
                        <svg class="icon" aria-hidden="true"><use xlink:href="#icon-like"></use></svg>
                        <font>{{$post->likes_count}}</font> 人喜欢
                    </a>
                    @endif
                </span>

                {{-- 第三方分享 --}}
                <div class="detail_third_share">
                    分享至：
                    @php
                        // 设置第三方分享图片，若未付费则为锁图。
                        if ($post->images->count() > 0) {
                            $share_pic = getenv('APP_URL') . '/api/v2/files/' . $post->images[0]['id'];
                        } else {
                            $share_pic = '';
                        }
                    @endphp
                    @include('pcview::widgets.thirdshare' , ['share_url' => route('pc:feedread', ['post_id' => $post->id]), 'share_title' => addslashes($post->content), 'share_pic' => $share_pic])
                </div>
                @php $rewards = ['count' => $post->reward_number, 'amount' => $post->reward_amount]; @endphp
                {{-- 打賞 --}}
                @include('pcview::widgets.rewards' , ['rewards_data' => $post->rewards, 'rewards_type' => 'group-posts', 'rewards_id' => $post->id, 'rewards_info' => $rewards])
            </div>

            {{-- 评论 --}}
            @include('pcview::widgets.comments', ['id' => $post->id, 'group_id' => $post->group_id , 'comments_count' => $post->comments_count, 'comments_type' => 'group', 'loading' => '.feed_left', 'position' => 0])
        </div>
    </div>

    <div class="right_container g-side">
        <div class="right_about">
            <div class="info clearfix">
                <div class="auth_header">
                    <a href="#">
                        <img src="{{ getAvatar($post->user, 60) }}" class="avatar"/>
                    </a>
                </div>
                <div class="auth_info">
                    <div class="info_name">
                        <a href="#">{{ $post->user->name }}</a>
                    </div>
                    <p class="info_bio">{{ $post->user->bio or '暂无简介' }}</p>
                </div>
            </div>
            <ul class="auth_fans">
                <li><a href="{{ route('pc:follows', ['user_id' => $post->user->id, 'type' => 1]) }}">粉丝<span>{{ $post->user->extra->followers_count or 0}}</span></a></li>
                <li><a href="{{ route('pc:follows', ['user_id' => $post->user->id, 'type' => 2]) }}">关注<span>{{ $post->user->extra->followings_count or 0}}</span></a></li>
            </ul>
            <div class="m-act">
                @if ($post->group->joined)
                    <button
                        class="joinbtn joined"
                        gid="{{$post->group->id}}"
                        state="1"
                        mode="{{$post->group->mode}}"
                        money="{{$post->group->money}}"
                        onclick="grouped.init(this);"
                    >已加入</button>
                @else
                    <button
                        class="joinbtn"
                        gid="{{$post->group->id}}"
                        state="0"
                        mode="{{$post->group->mode}}"
                        money="{{$post->group->money}}"
                        onclick="grouped.init(this);"
                    >+加入</button>
                @endif
            </div>
        </div>
        <!-- 推荐用户 -->
        @include('pcview::widgets.recusers')
        <!-- 收入达人 -->
        @include('pcview::widgets.incomerank')
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/pc/js/module.group.js') }}"></script>
    <script type="text/javascript">
        $("img.lazy").lazyload({effect: "fadeIn"});
    </script>
@endsection
