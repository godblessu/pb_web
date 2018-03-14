@section('title')
动态
@endsection

@extends('pcview::layouts.default')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/pc/css/feed.css') }}"/>
@endsection

@section('content')
    {{-- 左侧菜单 --}}
    @include('pcview::layouts.partials.leftmenu')

    <div class="feed_cont">
        @if (!empty($TS))
        {{-- 动态发布 --}}
        <div class="feed_post">
            <textarea class="post_textarea" placeholder="说说新鲜事" id="feed_content" amount="" onkeyup="checkNums(this, 255, 'nums');"></textarea>
            <div class="post_extra">
                <span class="font14" id="feed_pic">
                    <svg class="icon" aria-hidden="true"><use xlink:href="#icon-img"></use></svg>
                    图片
                </span>

                <a href="javascript:;" class="post_button" onclick="weibo.postFeed()">分享</a>

                {{-- 付费免费 --}}
                <div data-value="free" class="zy_select t_c gap12 feed_select" id="feed_select">
                    <span>免费</span>
                    <ul>
                        <li data-value="pay">付费</li>
                        <li class="active" data-value="free">免费</a></li>
                    </ul>
                    <i></i>
                </div>
            <span class="dy_cs">可输入<span class="nums" style="color: rgb(89, 182, 215);">255</span>字</span>
            </div>
        </div>
        @endif

        {{-- 动态列表 --}}
        <div class="feed_content">
            <div class="feed_menu">
                <a href="javascript:;" data-type="new" class="font16 @if ($type == 'new')selected @endif">最新</a>
                <a href="javascript:;" data-type="hot" class="font16 @if ($type == 'hot')selected @endif">热门</a>
                @if (!empty($TS))
                <a href="javascript:;" data-type="follow" class="font16 @if ($type == 'follow')selected @endif">关注的</a>
                @endif
            </div>
            <div id="feeds_list"></div>
        </div>
    </div>


    <div class="right_container">
        {{-- 签到 --}}
        @if (!empty($TS))
        @include('pcview::widgets.checkin')
        @endif

        {{-- 推荐用户 --}}
        @include('pcview::widgets.recusers')

        {{-- 动态首页右侧广告位 --}}
        @include('pcview::widgets.ads', ['space' => 'pc:feeds:right', 'type' => 1])

        {{-- 收入达人排行榜 --}}
        @include('pcview::widgets.incomerank')
    </div>
@endsection

@section('scripts')
<script src="{{ asset('assets/pc/js/module.picshow.js') }}"></script>
<script src="{{ asset('assets/pc/js/module.weibo.js') }}"></script>
<script src="{{ asset('assets/pc/js/jquery.uploadify.js') }}"></script>
<script src="{{ asset('assets/pc/js/md5.min.js') }}"></script>
<script type="text/javascript">
$(function(){
    // 加载微博
    var params = {
        type: '{{ $type }}',
        isAjax: true,
    };

    scroll.init({
        container: '#feeds_list',
        loading: '.feed_content',
        url: '/feeds',
        params: params,
        loadtype: 1
    });

    
    // 切换分类
    $('.feed_menu a').on('click', function() {
        var type = $(this).data('type');
        // 清空数据
        $('#feeds_list').html('');

        // 加载相关微博
        var params = {
            type: type,
            isAjax: true,
        };
        scroll.init({
            container: '#feeds_list',
            loading: '.feed_content',
            url: '/feeds',
            params: params
        });

        // 修改样式
        $('.feed_menu a').removeClass('selected');
        $(this).addClass('selected');
    });

    // 发布微博
    var up = $('.post_extra').Huploadify({
        auto:true,
        multi:true,
        newUpload:true,
        buttonText:'',
        onUploadSuccess: weibo.afterUpload
    });
});
</script>
@endsection
