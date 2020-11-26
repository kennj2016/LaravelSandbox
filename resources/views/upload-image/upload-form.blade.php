@extends('upload-image.app')

@section('content')
    <div class="col-md-6">
        <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="image_name">Image Name:</label>
                <input type="text" class="form-control" id="image_name" name="image_name">
            </div>
            <div class="form-group">
                <label for="upload_image">Select Image:</label>
                <input type="file" class="form-control" id="upload_image" name="upload_image">
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
@endsection
