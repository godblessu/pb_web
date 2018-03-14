@php
use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\getTime;
use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\formatContent;
use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\getUserInfo;
use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\getAvatar;
@endphp

@if(!$feeds->isEmpty())
@foreach($feeds as $key => $post)
<div class="feed_item" id="feed_{{$post->id}}" data-amount="{{ $post->paid_node['amount'] }}" data-node="{{ $post->paid_node['node'] }}">
    <div class="feed_title">
        <a class="avatar_box" href="{{ route('pc:mine', $post->user->id) }}">
            <img class="avatar" src="{{ getAvatar($post->user, 50) }}" width="50" />
            @if($post->user->verified)
            <img class="role-icon" src="{{ $post->user->verified['icon'] or asset('assets/pc/images/vip_icon.svg') }}">
            @endif
        </a>

        <a href="javascript:;">
            <span class="feed_uname font14">{{ $post->user->name }}</span>
        </a>

        <a class="date" @if($post->paid_node && $post->paid_node['paid'] == false) href="javascript:;"  onclick="weibo.payText(this, '{{ route('pc:feedread', $post->id) }}')" @else href="{{ route('pc:feedread', ['feedid' => $post->id]) }}" @endif>
            <span class="feed_time font12">{{ getTime($post->created_at) }}</span>
            <span class="feed_time font12 hide">查看详情</span>
        </a>

        @if(isset($post->pinned) && $post->pinned)
        <a class="pinned" href="javascript:;">
            <span class="font12">置顶</span>
        </a>
        @endif
    </div>

    <div class="feed_body">
        {{-- 文字付费 --}}
        @if ($post->paid_node && $post->paid_node['paid'] == false)
        <p class="feed_text fuzzy" onclick="weibo.payText(this)">@php for ($i = 0; $i < 200; $i ++) {echo 'T';} @endphp</p>
        @else
        <p class="feed_text">{!! formatContent($post->feed_content) !!}</p>
        @endif

        @include('pcview::templates.feed_images')
    </div>

    <div class="feed_bottom">
        <div class="feed_datas">
            <span class="digg" id="J-likes{{$post->id}}" rel="{{$post->like_count}}" status="{{(int) $post->has_like}}">
                @if($post->has_like)
                <a href="javascript:void(0)" onclick="liked.init({{$post->id}}, 'feeds', 1)">
                    <svg class="icon" aria-hidden="true"><use xlink:href="#icon-likered"></use></svg> <font>{{$post->like_count}}</font>
                </a>
                @else
                <a href="javascript:;" onclick="liked.init({{$post->id}}, 'feeds', 1)">
                    <svg class="icon" aria-hidden="true"><use xlink:href="#icon-like"></use></svg> <font>{{$post->like_count}}</font>
                </a>
                @endif
            </span>
            <span class="comment J-comment-show">
                <svg class="icon" aria-hidden="true"><use xlink:href="#icon-comment"></use></svg> <font class="cs{{$post->id}}">{{$post->feed_comment_count}}</font>
            </span>
            <span class="view">
                <svg class="icon" aria-hidden="true"><use xlink:href="#icon-chakan"></use></svg> {{$post->feed_view_count}}
            </span>
            <span class="options" onclick="options(this)">
                <svg class="icon icon-more" aria-hidden="true"><use xlink:href="#icon-more"></use></svg>
            </span>
            <div class="options_div">
                <div class="triangle"></div>
                <ul>
                    <li id="J-collect{{$post->id}}" rel="0" status="{{(int) $post->has_collect}}">
                        @if($post->has_collect)
                        <a href="javascript:;" onclick="collected.init({{$post->id}}, 'feeds', 0);" class="act">
                            <svg class="icon" aria-hidden="true"><use xlink:href="#icon-collect"></use></svg>
                            <span>已收藏</span>
                        </a>
                        @else
                        <a href="javascript:;" onclick="collected.init({{$post->id}}, 'feeds', 0);">
                          <svg class="icon" aria-hidden="true"><use xlink:href="#icon-collect"></use></svg>
                          <span>收藏</span>
                        </a>
                        @endif
                    </li>
                    @if($post->user_id == $TS['id'])
                    <li>
                        <a href="javascript:;" onclick="weibo.pinneds({{$post->id}});">
                            <svg class="icon" aria-hidden="true"><use xlink:href="#icon-pinned2"></use></svg>申请置顶
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;" onclick="weibo.delFeed({{$post->id}});">
                            <svg class="icon" aria-hidden="true"><use xlink:href="#icon-delete"></use></svg>删除
                        </a>
                    </li>
                    @else
                    <li>
                        <a href="javascript:;" onclick="reported.init('{{$post->id}}', 'feed');">
                            <svg class="icon" aria-hidden="true"><use xlink:href="#icon-report"></use></svg>
                            <span>举报</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- 评论 --}}
        @include('pcview::widgets.comments', ['id' => $post->id, 'comments_count' => $post->comments->count(), 'comments_type' => 'feeds', 'url' => Route('pc:feedread', $post->id), 'position' => 1, 'comments_data' => $post->comments, 'top' => 1])

        <div class="feed_line"></div>
    </div>
</div>
<script type="text/javascript">
    var images = JSON.parse('{!!$post->images!!}'), data = new Array();
    if(images){
        for (var i in images) {
            var size = images[i].size.split('x');
            var img = {
                id: 'img' + i,
                img: TS.SITE_URL + '/api/v2/files/' + images[i].file + '?token=' + TS.TOKEN,
                tinyimg: TS.SITE_URL + '/api/v2/files/' + images[i].file + '?w=58&h=58&token=' + TS.TOKEN,
                width: size[0],
                height: size[1]
            };
            data.push(img);
        }
    }
    $('#feed_photos_{{$post->id}}').PicShow({
        data: data,
        bigWidth: {{ $conw or 635}},
        bigHeight: {{ $conh or 400}}
    });
</script>
@endforeach
@if (isset($space) && $space)
    @include('pcview::widgets.ads', ['space' => 'pc:feeds:list', 'type' => 3])
@endif
@endif
