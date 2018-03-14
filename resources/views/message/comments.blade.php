@php
    use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\getTime;
    use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\getAvatar;
@endphp

@if (!$comments->isEmpty())
    @foreach($comments as $comment)
        <dl class="message_one">
            <dt><img src="{{ getAvatar($comment['user'], 40) }}"></dt>
            <dd>
                <div class="one_title"><a href="/profile/{{$comment['user']['id']}}">{{$comment['user']['name']}}</a>{{$comment['source_type']}}</div>
                <div class="one_date">{{ getTime($comment['created_at']) }}</div>
                <a href="{{ $comment['commentable'] ? $comment['source_url'] : 'javascript:;' }}" class="one_cotent">
                    <div class="content-comment">{{$comment['body']}}</div>
                    <div class="feed-content">
                        @if(isset($comment['source_img']))
                            <div class="con-img">
                                <img src="{{$comment['source_img']}}?w=35&h=35">
                            </div>
                        @endif
                        <div class="con-con">
                            @if($comment['commentable'])
                                {!! $comment['source_content'] !!}
                            @else
                                <div class="con-con">内容已被删除</div>
                            @endif
                        </div>
                    </div>
                </a>
            </dd>
        </dl>
    @endforeach
@else
    暂无更多
@endif