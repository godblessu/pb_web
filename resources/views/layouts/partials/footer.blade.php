<div class="footer">
    <div class="footer_cont">
        @if (!$config['nav_bottom']->isEmpty())
        <ul>
            @foreach ($config['nav_bottom'] as $nav)
            <li>
                <a target="{{ $nav->target }}" href="{{ $nav->url }}">{{ $nav->name}} </a>
            </li>
            @endforeach
        </ul>
     
    </div>
</div>
