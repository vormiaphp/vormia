{{-- Extend the Layout --}}
@extends("$theme_dir.errors.error")

{{-- Content --}}
@section('content')
    <h1 class="error-page mt-5"><span>450!</span></h1>
    <h4 class="mb-4 mt-5">NOT ALLOWED</h4>
    <p class="mb-4 w-75 mx-auto">Sorry, you are not allowed to access/view this page.</p>
@endsection
