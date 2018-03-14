@php
    use function Zhiyi\Component\ZhiyiPlus\PlusComponentPc\getAvatar;
@endphp
@if(isset($config['bootstrappers']['site']['reward']['status']) && $config['bootstrappers']['site']['reward']['status'])
    <div class="reward_cont">
        <p><button class="btn btn-warning btn-lg" onclick="rewarded.show({{ $rewards_id }}, '{{ $rewards_type }}')">打 赏</button></p>
        <p class="reward_info tcolor">
            <font color="#F76C6A">{{ $rewards_info['count'] }} </font>次打赏，共
            <font color="#F76C6A">{{ $rewards_info['amount'] * ($config['bootstrappers']['wallet:ratio']) }} </font>{{ $config['bootstrappers']['site']['gold_name']['name'] }}
        </p>

        {{-- 打賞 --}}
        @if (!$rewards_data->isEmpty())
            <div class="reward_user">
                @foreach ($rewards_data as $key => $reward)
                    @if ($key < 10)
                        <a href="{{ route('pc:mine', $reward->user->id) }}" class="user_item">
                            <img class="lazy round" src="{{ getAvatar($reward->user, 42) }}" width="42" />
                            @if ($reward->user->verified)
                                <img class="verified_icon" src="{{ $post->user->verified['icon'] or asset('assets/pc/images/vip_icon.svg') }}">
                            @endif
                        </a>
                    @endif
                @endforeach
                <span class="more_user" onclick="rewarded.list({{ $rewards_id }}, '{{ $rewards_type }}')"></span>
            </div>
        @endif
    </div>
@endif