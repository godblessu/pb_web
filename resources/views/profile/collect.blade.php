@section('title') {{ $user->name }} 的个人主页@endsection

@extends('pcview::layouts.default')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/pc/css/feed.css') }}"/>
<link rel="stylesheet" href="{{ asset('assets/pc/css/profile.css') }}"/>
<link rel="stylesheet" href="{{ asset('assets/pc/css/question.css') }}"/>
@endsection

@section('content')

{{-- 个人中心头部导航栏 --}}
@include('pcview::profile.navbar')

<div class="profile_body">
    <div class="left_container">
        {{-- 收藏列表 --}}
        <div class="profile_content">
            <div class="profile_menu J-menu">
                <a class="active" href="javascript:;" cid="1">动态</a>
                <a href="javascript:;" cid="2">文章</a>
                <a href="javascript:;" cid="3">回答</a>
            </div>
            <div id="content_list" class="profile_list"></div>
        </div>
    </div>

    <div class="right_box">
        @include('pcview::widgets.recusers')
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('assets/pc/js/module.profile.js') }}"></script>
<script src="{{ asset('assets/pc/js/module.weibo.js') }}"></script>
<script src="{{ asset('assets/pc/js/module.picshow.js') }}"></script>
<script>
$(function(){
    scroll.init({
        container: '#content_list',
        loading: '.profile_content',
        url: '/profile/collect',
        paramtype: 1,
        params: {cate: 1, limit: 10, isAjax: true}
    });
})

$('.J-menu > a').on('click', function(){
    var cate = $(this).attr('cid');

    $('#content_list').html('').hide();
    scroll.init({
        container: '#content_list',
        loading: '.profile_content',
        url: '/profile/collect',
        params: {cate: cate, limit: 10, isAjax: true }
    });

    $('.J-menu a').removeClass('active');
    $(this).addClass('active');
});
</script>
@endsection