
@php
    use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\formatList;
    use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\getUserInfo;
@endphp
@foreach ($datas as $data)
    <div class="qa-item">
        <div class="qa-body mb20 clearfix">
            @if(!$data->invited || !$data->question->look || (isset($TS) && $data->invited && ($data->could || $data->question->user_id == $TS['id'] || $data->user_id == $TS['id'])))
                <span class="answer-body">{!! str_limit(formatList($data->body), 250, '...') !!}</span>
                <a class="button button-plain button-more" href="{{ route('pc:answeread', $data->id) }}">查看详情</a>
            @else
                <span class="answer-body fuzzy" onclick="QA.look({{ $data->id }}, '{{ $config['bootstrappers']['question:onlookers_amount'] }}' , {{ $data->question_id }})">
                    @php for ($i = 0; $i < 250; $i ++) {echo 'T';} @endphp
                </span>
            @endif
        </div>
        <div class="qa-toolbar feed_datas font14">
            <a
                class="liked"
                href="javascript:;"
                id="J-likes{{$data->id}}"
                rel="{{ $data['likes_count'] }}"
                status="{{ (int) $data->liked }}"
                onclick="liked.init({{ $data->id }}, 'question', 1);"
            >
                <svg class="icon" aria-hidden="true">
                    @if($data->liked)
                        <use xlink:href="#icon-likered"></use>
                    @else
                        <use xlink:href="#icon-like"></use>
                    @endif
                </svg><font>{{ $data->likes_count }}</font> 点赞
            </a>
            <a class="gcolor comment J-comment-show" href="javascript:;">
                <svg class="icon" aria-hidden="true"><use xlink:href="#icon-comment"></use></svg>
                <font class="cs{{$data->id}}">{{$data->comments_count}}</font> 评论
            </a>
        </div>
        @include('pcview::widgets.comments', ['id' => $data->id, 'comments_count' => $data->comment_count, 'comments_type' => 'answer', 'url' => Route('pc:answeread', $data->id), 'position' => 1, 'comments_data' => $data->comments, 'top' => 1])
        <div class="feed_line"></div>
    </div>
@endforeach