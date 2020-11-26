@extends('upload-image.app')

@section('content')
    <h3>{!! $keyName !!}</h3>
    <div class="col-md-12">
        @foreach($images as $image)
            <div>
                <h4>{!! $image['size'] !!}</h4>
            </div>
            <div>
                <img src="{!! $image['cdn_url'] !!}"/>
            </div>
        @endforeach
    </div>
@endsection
