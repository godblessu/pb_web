@php $route = route::currentrouteName(); @endphp
{{-- 左侧导航 --}}
<div class="left_menu">
    <ul>
        <li>
            <a href="{{ route('pc:feeds') }}" class="@if ($route == 'pc:feeds')selected @endif">
               <use xlink:href="#icon-dynamic"></use></svg>全部动态
            </a>
        </li>
        <li>
            <a href="{{ route('pc:mine') }}">
                <use xlink:href="#icon-mydynamic"></use></svg>我的动态
            </a>
        </li>
        <li>
            <a href="{{ route('pc:follows', ['user_id' => $TS['id'], 'type' => 1]) }}">
                <use xlink:href="#icon-myfans"></use></svg>我的粉丝
            </a>
        </li>
        <li>
            <a href="{{ route('pc:follows', ['user_id' => $TS['id'], 'type' => 2]) }}">
                <use xlink:href="#icon-attention"></use></svg>关注的人
            </a>
        </li>
        <li>
            <a href="{{ route('pc:rank')}}">
                <use xlink:href="#icon-rank"></use></svg>排行榜
            </a>
        </li>
        <li>
            <a href="{{ route('pc:profilecollect') }}">
                <use xlink:href="#icon-collection"></use></svg>收藏的
            </a>
        </li>
        <li>
            <a href="{{ route('pc:account') }}">
                <use xlink:href="#icon-sets"></use></svg>设置
            </a>
        </li>

    </ul>
</div>
