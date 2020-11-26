<?php

namespace App\Http\Controllers;

use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View as ReturnView;
use Illuminate\Support\Facades\View;

/**
 * Class ImageUploadController
 * @package App\Http\Controllers
 */
class ImageUploadController extends Controller
{
    /**
     * @return ReturnView
     */
    public function viewUploadForm(): ReturnView
    {
        return View::make('upload-image.upload-form', ['formAction' => '/upload-image']);
    }

    /**
     * @param ImageUploadService $imageService
     * @param Request $request
     * @return string
     * @throws \Illuminate\Validation\ValidationException
     */
    public function uploadImage(ImageUploadService $imageService, Request $request): string
    {
        $this->validate($request, [
            'upload_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imageService->uploadToAWS($request)
            ->save([
                ImageUploadService::IMAGE_SMALL_SIZE,
                ImageUploadService::IMAGE_THUMBNAIL_SIZE
            ])->uploadToS3Bucket()
            ->persistData();

        if ($imageService->hasErrors()) {
            return redirect()->route('upload-error', ['error' => base64_encode($imageService->getError())]);
        }

        $keyName = $imageService->getImageName();

        return redirect()->route('upload-image.view-images', ['key' => $keyName]);
    }

    /**
     * @param Request $request
     * @return ReturnView
     */
    public function getImageUploadError(Request $request): ReturnView
    {
        return View::make('upload-image.upload-error', ['errorMessage' => base64_decode($request->query('error'))]);
    }

    /**
     * @param ImageUploadService $imageService
     * @param Request $request
     * @return ReturnView
     */
    public function getImage(ImageUploadService $imageService, Request $request): ReturnView
    {
        $imageData = $imageService->getImageData($request);

        return View::make('upload-image.view-images', [
            'keyName' => $request->query('key'),
            'images' => $imageData
        ]);
    }
}
