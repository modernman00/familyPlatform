<div class="alert {!! $alertType !!}" style="background-color: {!! $color  !!}; padding-left: 100px;">
    <div>{!! $title !!}</div>
    {!! $slot !!}
</div>

{{--  USAGE  --}}
{{--  
@component('TestComponent.alert',array('title'=>'COMPONENT #1','color'=>"red"))
    <strong>Whoops!</strong> Something went wrong! (the code is right btw)
@endcomponent  --}}

