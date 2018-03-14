@section('title')创建圈子@endsection

@extends('pcview::layouts.default')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/pc/css/global.css') }}">
<link rel="stylesheet" href="{{ asset('assets/pc/css/group.css') }}">
@endsection

@section('content')
<div class="p-addgroup">
    <div class="g-mn">
        <h1 class="u-tt">创建圈子</h1>
        <div class="m-form">
            <div class="formitm">
                <input class="chools-cover" id="J-upload-cover" type="file" name="file">
                <img class="cover" id="J-preview-cover" src="{{ asset('assets/pc/images/default_group_cover.png') }}">
                <input id="avatar_id" type="hidden" name="avatar" value="" />
            </div>
            <div class="formitm">
                <label class="lab">圈子名称</label>
                <input class="ipt" name="name" type="text"  placeholder="最多 20 个字"/>
            </div>
            <div class="formitm">
                <label class="lab">圈子简介</label>
                <textarea class="txt" name="summary" rows="4" placeholder="最多 255 个字"></textarea>
            </div>
            <div class="formitm">
                <label class="lab">圈子分类</label>
                <div data-value="{{ $cates[0]['id'] }}" class="zy_select t_c gap12" id="categrey">
                    <span>{{ $cates[0]['name'] }}</span>
                    <ul>
                        @foreach ($cates as $key => $cate)
                            <li @if($key == 0) class="active" @endif data-value="{{$cate->id}}">{{$cate->name}}</li>
                        @endforeach
                    </ul>
                    <i></i>
                    <input id="cate" type="hidden" value="user" />
                </div>
            </div>
            <div class="formitm">
                <label class="lab">圈子标签</label>
                <div class="tags-box ipt">
                    <div class="choos-tags" id="J-tag-box">
                        @foreach ($tags as $tag)
                            <dl>
                                <dt>{{ $tag->name }}</dt>
                                @foreach ($tag->tags as $item)
                                    <dd data-id="{{$item->id}}">{{$item->name}}</dd>
                                @endforeach
                            </dl>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="formitm">
                <label class="lab">订阅模式</label>
                <div class="form-control f-ib" id="J-submodel">
                    <input class="regular-radio f-dn" id="radio-free" name="modes" type="radio" value="1" checked />
                    <label class="radio" for="radio-free"></label>免费&nbsp;&nbsp;
                    <input class="regular-radio f-dn" id="radio-pay" name="modes" type="radio" value="2"/>
                    <label class="radio" for="radio-pay"></label>付费
                </div>
            </div>
            <div class="formitm auth-box">
                <div class="j-sub0">
                    <div class="form-control f-mb20">
                        <input class="regular-radio f-dn" id="radio-open" name="mode" type="radio" value="public" checked />
                        <label class="radio" for="radio-open"></label>公开圈子<span class="f-ml20 s-fc4">加入圈子即可发帖</span>
                    </div>
                    <div class="form-control">
                        <input class="regular-radio f-dn" id="radio-private" name="mode" type="radio" value="private" />
                        <label class="radio" for="radio-private"></label>封闭圈子<span class="f-ml20 s-fc4">未通过加入申请的人不能进入圈子</span>
                    </div>
                </div>
                <div class="form-control f-dn j-sub1">
                    <label class="lab">设置入圈金额</label>
                    <input min="1" oninput="value=moneyLimit(value)" class="iptline" name="money" type="text"/>&nbsp;&nbsp;<span class="s-fc4">积分</span>
                </div>
            </div>
            <div class="formitm">
                <label class="lab">分享设置</label>
                <span class="f-mr20">
                    <input class="regular-radio f-dn" id="radio-yes" name="allow_feed" type="radio" value="1" checked />
                    <label class="radio" for="radio-yes"></label>帖子可分享至动态
                </span>
                <span class="f-mr20">
                    <input class="regular-radio f-dn" id="radio-no" name="allow_feed" type="radio" value="0" />
                    <label class="radio" for="radio-no"></label>帖子不可分享至动态
                </span>
            </div>
            <div class="formitm">
                <label class="lab">圈子位置</label>
                <input class="ipt" name="location" type="text" placeholder="输入所在地区" />
            </div>
            <div class="formitm">
                <label class="lab">圈子公告</label>
                <textarea class="txt" name="notice" rows="6" placeholder="编辑自己的圈子公告或规则（选填）"></textarea>
            </div>
            <div class="formitm">
                <label class="lab">&nbsp;</label>
                <input class="iptck" type="checkbox" name="protocol" checked><span>我已阅读并遵守ThinkSNS+的圈子创建协议</span>
            </div>
            <p class="tooltips">提交后，我们将在2个工作日内给予反馈，谢谢合作！</p>
            <div class="f-tac">
                <input type="hidden" name="latitude" value="" />
                <input type="hidden" name="longitude" value="" />
                <input type="hidden" name="geo_hash" value="" />
                <button class="btn btn-primary btn-lg" id="J-create-group" type="button">提 交</button>
            </div>
        </div>
    </div>
</div>
@endsection
<script src='//webapi.amap.com/maps?v=1.4.2&key=e710c0acaf316f2daf2c1c4fd46390e3'></script>
<script src="//webapi.amap.com/ui/1.0/main.js?v=1.0.11"></script>
@section('scripts')
<script src="{{ asset('assets/pc/js/geohash.js')}}"></script>
<script src="{{ asset('assets/pc/js/md5.min.js')}}"></script>
<script>

$('#J-submodel label').on('click', function(e){
    var val = $('#'+$(this).attr('for')).val();
    if (val == '2') {
        $('.j-sub0').hide();
        $('.j-sub1').show();
    } else {
        $('.j-sub0').show();
        $('.j-sub1').hide();
    }
});

var selBox = $('.tags-box');
$('#J-tag-box dd').on('click', function(e){
    e.stopPropagation();
    var tid = $(this).data('id');
    var name = $(this).text();
    if (selBox.find('span').hasClass('tid'+tid)) {
        noticebox('标签已存在', 0); return;
    }

    if (selBox.find('span').length > 4) {
        noticebox('标签最多五个', 0); return;
    }
    selBox.append('<span class="tid'+tid+'" data-id="'+tid+'">'+name+'</span>');
});
selBox.on('click', 'span', function(){
    $(this).remove();
});

$('#J-upload-cover').on('change', function(e){
    var file = e.target.files[0];
    var a = new FileReader();
    a.onload = function(e) {
        var data = e.target.result;
        $('#J-preview-cover').attr('src', data);
    };
    a.readAsDataURL(file);
});

$('#J-create-group').on('click', function(){
    var protocol = $('[name="protocol"]:checked').val();
    var categrey = $('#categrey').data('value');
    var modeType = $('[name="modes"]:checked').val();
    var POST_URL = '/api/v2/plus-group/categories/' + categrey + '/groups';
    var formData = new FormData();
        var attrs = {
            avatar: $('#J-upload-cover')[0].files[0],
            name: $('[name="name"]').val(),
            summary: $('[name="summary"]').val(),
            notice: $('[name="notice"]').val(),
            location: $('[name="location"]').val(),
            latitude: $('[name="latitude"]').val(),
            longitude: $('[name="longitude"]').val(),
            geo_hash: $('[name="geo_hash"]').val(),
            allow_feed: $('[name="allow_feed"]:checked').val(),
        };
        if (!categrey) {
            noticebox('请选择圈子分类', 0);return;
        }
        if (!attrs.avatar || attrs.avatar === undefined) {
            noticebox('请选上传圈子头像', 0);return;
        }
        if (!attrs.name || attrs.name.length > 20) {
            noticebox('圈子名称长度为1 ~ 20个字', 0)
            ;return;
        }
        if (attrs.summary.length > 255) {
            noticebox('圈子简介不能超过255个字', 0);
            return;
        }
        if (attrs.notice.length > 2000) {
            noticebox('圈子公告不能大于2000个字', 0);
            return;
        }
        if ($('.tags-box span').length < 1) {
            noticebox('请选择圈子标签', 0);
            return;
        }
        if (!attrs.location || !attrs.latitude || !attrs.longitude) {
            noticebox('请选择圈子位置', 0);
            return;
        }
        if (attrs.notice.length > 2000) {
            noticebox('圈子公告不能超过2000字', 0);
            return;
        }
        _.forEach(attrs, function(v, k) {
            formData.append(k, v);
        });
        if (modeType == '1') {
            formData.append('mode', $('[name="mode"]:checked').val());
        } else {
            formData.append('mode', 'paid');
            if (!$('[name="money"]').val()) {
                noticebox('请设置付费金额', 0);return;
            }
            formData.append('money', $('[name="money"]').val());
        }
        $('.tags-box span').each(function(){
            formData.append('tags[][id]', $(this).data('id'));
        });
        if (protocol !== undefined) {
            axios.post(POST_URL, formData)
            .then(function (response) {
                noticebox('发布成功，请等待审核', 1, '/group');
            })
            .catch(function (error) {
                showError(error.response.data);
            });
        } else {
            noticebox('请勾选同意ThinkSNS+的圈子创建协议', 0);
        }
});
$('[name="location"]').on('click', function(){
    var _this = this;
    getMaps(function(poi){
        $('[name="latitude"]').val(poi.location.lat);
        $('[name="longitude"]').val(poi.location.lng);
        $('[name="geo_hash"]').val(encodeGeoHash(poi.location.lat, poi.location.lng));
        $(_this).val(poi.district+poi.address);
    });
})
</script>
@endsection