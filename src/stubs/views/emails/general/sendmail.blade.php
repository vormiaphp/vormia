@include('emails.partials.header', ['logo' => $logo, 'subject' => $subject])

<p class="header">{{ $title }}</p>

<p class="sub-header">{!! $subtitle !!}</p>

<div class="box">{!! $body !!}</div>

{!! $outro !!}

@if ($btn['link'])
	<a href="{!! $btn['link'] !!}" class="button">{!! $btn['title'] !!}</a>
@endif

@if (!is_null($btn_extra))
	{!! $btn_extra !!}
@endif

@include('emails.partials.footer', ['unsubscribe_url' => '#'])
