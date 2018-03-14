@section('title') 充值 @endsection

@extends('pcview::layouts.default')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/pc/css/account.css')}}"/>
@endsection

@section('content')

<div class="pay-box">
{{-- <form action="/account/payway" method="get" target="_blank" accept-charset="utf-8"> --}}
    <div class="pay-title">
        <h1 class="title"> 充值 </h1>
        <span id="open">
            <svg class="icon" aria-hidden="true"><use xlink:href="#icon-doubt"></use></svg>
            用户充值协议
        </span>
    </div>
    <div class="pay-form">
        <p class="tcolor">充值比率</p>
        <p><font color="#FF9400">1元 = {{$currency['recharge-ratio'] * 100}}积分</font></p>
        <p class="tcolor">设置充值金额</p>
        <div class="pay-curr">
            @if($currency['recharge-options'])
                @foreach ($currency['recharge-options'] as $item)
                    <label class="opt" for="sum{{$item / 100}}">¥{{$item / 100}}<input class="hide" id="sum{{$item / 100}}" type="radio" name="sum" value="{{$item}}"></label>
                @endforeach
            @endif
        </div>

        <p><input min="1" oninput="value=moneyLimit(value)" onkeydown="if(!isNumber(event.keyCode)) return false;" type="number" class="custom-sum" name="custom" placeholder="自定义充值金额"></p>

        <p class="tcolor">选择充值方式</p>
        <div class="pay-way">
            <img src="{{ asset('assets/pc/images/pay_pic_zfb_on.png') }}"/>
            <input class="hide" id="alipay" type="radio" name="payway" value="alipay_pc_direct" checked>
        </div>

        <button type="submit" class="pay-btn" id="J-pay-btn">充值</button>
    </div>
{{-- </form> --}}
</div>
{{-- 充值协议 --}}

<a id="payurla" href="" target="_blank"><b id="payurlc"></b></a>
@endsection

@section('scripts')
<script src="{{ asset('assets/pc/js/pingpp.js')}}"></script>
<script type="text/javascript">
var popInterval;
$('.pay-curr label').on('click', function(){
    $('.pay-curr label').removeClass('active');
    $(this).addClass('active');

    $('input[name="custom"]').val('');
});

// 用户充值协议
$('#open').on('click', function () {
    var html = '<div class="out">';
    html += '<div class="agreement">';
    html += '  <div class="title">';
    html += '  <h3>用户充值协议</h3>';
    html += '  </div>';
    html += '    <div class="agreement-info">';
    html += '<p class="info">{{$currency['recharge-rule']}}</p>';
    html += '</div>';
    html += ' </div>';
    html += '</div>';
    ly.loadHtml(html,'','520','440');
});

$('input[name="custom"]').on('focus, change, keyup', function(){
    $('.pay-sum label').removeClass('active');
    $('[name="sum"]').removeAttr('checked');
})

$('.pay-way label').on('click', function(){
    $('.pay-way label').removeClass('active');
    $(this).addClass('active');
})

$('#J-pay-btn').on('click', function(){
    $(this).attr("disabled", true);
    var sum = $('[name="sum"]:checked').val();
    var payway = $('[name="payway"]:checked').val();
    var custom = $('[name="custom"]').val();
    var params = {
        type: payway,
        amount: sum ? sum : custom * 100,
        extra: {
            success_url: "{{ route('pc:currencypay') }}"
        }
    };
    axios.post('/api/v2/currency/recharge', params)
      .then(function (response) {
        var res = JSON.stringify(response.data.charge);
        var surl = TS.SITE_URL + '/account/gateway?res=' + window.encodeURIComponent(res);
        $("#payurla").attr("href", surl);
        $("#payurlc").trigger("click");
        payPop(response.data.id);
      })
      .catch(function (error) {
        showError(error.response.data);
      });
});

function payPop(id){
    popInterval = setInterval("checkStatus("+id+")", 3000);
    var html = '<div class="tip">'+
                    '<p>请您在新打开的支付页面完成付款</p>'+
                    '<p>付款前请不要关闭此窗口</p>'+
                '</div><div class="msg">完成付款后请根据您的情况点击下面的按钮。</div>';
    layer.confirm(html, {
      move: false,
      id: 'pay_tip_pop',
      title: '充值提示',
      btn: ['支付成功','遇到问题'],
      cancel: function(){
        clearInterval(popInterval);
      }
    }, function(){
        checkStatus(id, 1);
    }, function(){
        return false;
    });
    $('#J-pay-btn').attr("disabled", false);
}

function checkStatus(id, type) {
    type = type || 0;
    if (!id) { return; }
    axios.get('/api/v2/currency/orders/'+id)
      .then(function (response) {
        if (response.data.status == 1) {
            window.location.href = TS.SITE_URL + '/success?status=1&url={{route('pc:currencypay')}}&time=3&message=充值成功';
        }
        if (type == 1) {
            window.location.href = TS.SITE_URL + '/success?status=0&url={{route('pc:currencypay')}}&time=3&message=充值失败或正在处理中&content=操作失败';
        }
      })
      .catch(function (error) {
        showError(error.response.data);
      });
}
</script>
@endsection