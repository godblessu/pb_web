@php
    use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\getTime;
@endphp

@if ($type == 2)
    {{-- 交易记录 --}}
    @if(!$records->isEmpty())
    <div class="wallet-table">
        <table class="table tborder" border="0" cellspacing="0" cellpadding="0">
            @if ($loadcount == 1)
            <thead>
                <tr>
                    <th width="20%">交易时间</th>
                    <th width="60%">交易名称</th>
                    <th width="20%">交易金额</th>
                </tr>
            </thead>
            @endif
            <tbody>
                @foreach ($records as $item)
                <tr>
                    <td width="20%">{{ getTime($item->created_at, 0, 0) }}</td>
                    <td width="60%"><p class="ptext">{{ $item->subject }}</p></td>
                    <td width="20%">
                        <font color="#FF9400">{{ $item->action==1 ? '+'.($item->amount*($config['bootstrappers']['wallet:ratio'])) : '-'.($item->amount*($config['bootstrappers']['wallet:ratio'])) }}</font>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@elseif ($type == 3)
    {{-- 提现记录 --}}
    @if(!$records->isEmpty())
    <div class="wallet-table">
        <table class="table tborder" border="0" cellspacing="0" cellpadding="0">
            @if ($loadcount == 1)
            <thead>
                <tr>
                    <th width="20%">申请时间</th>
                    <th width="50%">备注</th>
                    <th width="15%">提现金额</th>
                    <th width="15%">状态</th>
                </tr>
            </thead>
            @endif
            <tbody>
                @foreach ($records as $item)
                <tr>
                    <td width="20%">{{ getTime($item->created_at, 0, 0) }}</td>
                    <td width="50%"><p class="ptext">{{ $item->remark }}</p></td>
                    <td width="15%"><font color="#FF9400">{{ $item->value*($config['bootstrappers']['wallet:ratio']) }}</font></td>
                    <td width="15%">
                        @if ($item->status == 0) 待审批 @endif
                        @if ($item->status == 1) 已审批 @endif
                        @if ($item->status == 2) 拒绝 @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@endif