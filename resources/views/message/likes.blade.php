@php
    use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\getTime;
    use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\getAvatar;
@endphp

@if (!$likes->isEmpty())
    @foreach($likes as $like)
        <dl class="message_one">
            <dt><img src="{{ getAvatar($like['user'], 40) }}"></dt>
            <dd>
                <div class="one_title"><a href="/profile/{{$like['user']['id']}}">{{$like['user']['name']}}</a>{{$like['source_type']}}</div>
                <div class="one_date">{{ getTime($like['created_at']) }}</div>

                <a href="{{ $like['likeable'] ? $like['source_url'] : 'javascript:;'}}" class="one_cotent">
                    <div class="feed-content">
                        @if(isset($like['source_img']))
                            <div class="con-img">
                                <img src="{{$like['source_img']}}?w=35&h=35">
                            </div>
                        @endif
                        <div class="con-con">
                            @if($like['likeable'])
                                {!! $like['source_content'] !!}
                            @else
                                内容已被删除
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