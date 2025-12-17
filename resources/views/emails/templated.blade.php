@if($type === 'markdown')
    {!! \Illuminate\Mail\Markdown::parse($body) !!}
@else
    {!! $body !!}
@endif