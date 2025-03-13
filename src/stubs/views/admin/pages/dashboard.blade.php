@extends("$theme_dir.layouts.main")

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8 col-sm-12">
                            <h4 class="card-title">All Customers </h4>
                            <p class="card-title-desc">
                                This is a list of your
                                <strong><code>customers</code></strong>.
                            </p>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-12">
                            <!-- Notification -->
                            {!! $notify !!}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8 col-sm-12">
                            <h4 class="card-title">All Invoices </h4>
                            <p class="card-title-desc">
                                This is a list of generated
                                <strong><code>invoices</code></strong>.
                            </p>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-12">
                            <!-- Notification -->
                            {!! $notify !!}
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection
