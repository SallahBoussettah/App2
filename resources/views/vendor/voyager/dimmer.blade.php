<div class="panel widget center bgimage" style="margin-bottom:0;overflow:hidden;background-color:{{ $color }};">
    <div class="dimmer" style="background: rgba(45,53,61,.3);"></div>
    <div class="panel-content">
        @if (isset($icon))<i class='{{ $icon }}'></i>@endif
        <h4>{!! $title !!}</h4>
        <p>{!! $text !!}</p>
        <a href="{{ $button['link'] }}" class="btn btn-primary">{!! $button['text'] !!}</a>
    </div>
</div>
