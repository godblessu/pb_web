@if (!$data->isEmpty())
<ul>
    @foreach ($data as $key=>$post)
    <li class="m-blk ">
        <div class="m-cover">
            <a href="{{ route('pc:topicinfo', $post->id) }}">
                <img src="{{ $post->avatar or asset('assets/pc/images/default_picture.png') }}" width="120" height="120">
            </a>
        </div>
        <div class="m-entry">
            <a href="{{ route('pc:topicinfo', $post->id) }}">
                <p class="u-name f-toe">{{ $post->name }}</p>
            </a>
            <div class="m-col">
                关注 <span id="tf-count-{{ $post->id }}">{{ $post->follows_count }}</span>
                问题 <span>{{ $post->questions_count }}</span>
            </div>
            <div class="m-col1">
                @if ($post->has_follow)
                    <a href="javascript:;" tid="{{ $post->id }}" status="1" onclick="QT.follow(this)">已关注</a>
                @else
                    <a href="javascript:;" tid="{{ $post->id }}" status="0" onclick="QT.follow(this)">+关注</a>
                @endif
            </div>
        </div>
    </li>
    @endforeach
</ul>
@elseif(isset($search) && $search)
<script>
if (!$('.no_data_div').length) {
    var box = $('#content_list');
    box.append('<div class="no_data_div"><div class="no_data"><img src="{{ asset('assets/pc/images/pic_default_content.png') }}"><p><div class="search-button"><a href="javascript:;" onclick="QT.show()">向官方建议创建新话题</a></div></div></div>');
}
</script>
@endif