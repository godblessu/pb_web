@section('title') 充值 @endsection

@extends('pcview::layouts.default')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/pc/css/account.css')}}"/>
@endsection

@section('content')

<div class="pay-box">
{{-- <form action="/account/payway" method="get" target="_blank" accept-charset="utf-8"> --}}
    <h1 class="title"> 充值 </h1>
    <div class="pay-form">

        <p class="tcolor">设置充值金额</p>
        <div class="pay-sum">
            <label class="opt" for="sum10">¥10.00<input class="hide" id="sum10" type="radio" name="sum" value="1000"></label>
            <label class="opt active" for="sum50">¥50.00<input class="hide" id="sum50" type="radio" name="sum" value="5000" checked></label>
            <label class="opt" for="sum100">¥100.00<input class="hide" id="sum100" type="radio" name="sum" value="10000"></label>
        </div>

        <p><input min="1" oninput="value=moneyLimit(value)" class="custom-sum" type="text" name="custom" placeholder="自定义充值金额"></p>

        <p class="tcolor">选择充值方式</p>
        <div class="pay-way">
            <img src="{{ asset('assets/pc/images/pay_pic_zfb_on.png') }}"/>
            <input class="hide" id="alipay" type="radio" name="payway" value="alipay_pc_direct" checked>
        </div>

        <button type="submit" class="pay-btn" id="J-pay-btn">充值</button>
    </div>
{{-- </form> --}}
</div>
<a id="payurla" href="" target="_blank"><b id="payurlc"></b></a>
@endsection

@section('scripts')
<script src="{{ asset('assets/pc/js/pingpp.js')}}"></script>
<script type="text/javascript">
var popInterval;
$('.pay-sum label').on('click', function(){
    $('.pay-sum label').removeClass('active');
    $(this).addClass('active');

    $('input[name="custom"]').val('');
})

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
            success_url: "{{ route('pc:wallet') }}"
        }
    }

    axios.post('/api/v2/wallet/recharge', params)
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
    axios.get('/api/v2/wallet/charges/'+id+'?mode=retrieve')
      .then(function (response) {
        if (response.data.status == 1) {
            window.location.href = TS.SITE_URL + '/success?status=1&url={{route('pc:wallet')}}&time=3&message=充值成功';
        }
        if (type == 1) {
            window.location.href = TS.SITE_URL + '/success?status=0&url={{route('pc:wallet')}}&time=3&message=充值失败或正在处理中&content=操作失败';
        }
      })
      .catch(function (error) {
        showError(error.response.data);
      });
}
</script>
@endsection