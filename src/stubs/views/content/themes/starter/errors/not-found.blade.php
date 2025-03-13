{{-- Extend the Layout --}}
@extends("$theme_dir.errors.error")

{{-- Content --}}
@section('content')
    <h1 class="error-page mt-5"><span>404!</span></h1>
    <h4 class="mb-4 mt-5">NOT FOUND</h4>
    <p class="mb-4 w-75 mx-auto">Sorry, the page was not found.</p>
@endsection
