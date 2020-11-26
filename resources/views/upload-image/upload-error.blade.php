@extends('upload-image.app')

@section('content')
    <h3>Error</h3>
    <div class="col-md-12">
        <h4>Error uploaded:</h4>
        <pre>{!! $errorMessage !!}</pre>
    </div>
@endsection
