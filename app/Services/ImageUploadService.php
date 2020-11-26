<?php


namespace App\Services;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Intervention\Image\Exception\NotWritableException;
use Intervention\Image\Image as ImageObject;
use Intervention\Image\ImageManagerStatic as ImageManager;
use App\ImageUpload;

/**
 * Class ImageUploadService
 * @package App\Services
 */
class ImageUploadService
{
    /**
     * define image full size
     */
    public const IMAGE_FULL_SIZE = 0;
    /**
     * define image small size
     */
    public const IMAGE_SMALL_SIZE = 600;
    /**
     * defind image thumbnail size
     */
    public const IMAGE_THUMBNAIL_SIZE = 300;

    /** @var UploadedFile */
    private $image;
    /** @var array|string|null */
    private $imageName;
    /** @var Collection */
    private $localPaths;
    /** @var Collection */
    private $cdnUrls;
    /** @var string */
    private $error;

    /**
     * ImageUploadService constructor.
     */
    public function __construct()
    {
        $this->localPaths = collect();
        $this->cdnUrls = collect();
    }

    /**
     * @return string
     */
    public function getImageName(): string
    {
        return $this->imageName;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->error !== null;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param Request $request
     * @return ImageUploadService
     */
    public function uploadToAWS(Request $request): ImageUploadService
    {
        if ($request->hasFile('upload_image')) {
            $this->image = $request->file('upload_image');
        }

        $this->imageName = $request->input('image_name');

        return $this;
    }

    /**
     * @param array $sizes
     * @return ImageUploadService
     */
    public function save(array $sizes = []): ImageUploadService
    {
        $imageObject = ImageManager::make($this->image);

        // Add the default size to the beginning so that it's processed first
        collect($sizes)->prepend(self::IMAGE_FULL_SIZE)
            ->each(function ($size) use ($imageObject) {
                if ($size > 0) {
                    $imageObject = $this->resize($imageObject, $size);
                }
                $this->saveLocal($this->image, $imageObject);
            });

        return $this;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param ImageObject $imageObject
     * @param null $newWidth
     * @return ImageUploadService
     */
    protected function saveLocal(UploadedFile $uploadedFile, ImageObject $imageObject, $newWidth = null): ImageUploadService
    {
        if ($newWidth) {
            $imageObject = $this->resize($imageObject, $newWidth);
        }
        $size = $imageObject->getWidth() . 'x' . $imageObject->getHeight();
        $timestamp = time();
        $ext = $uploadedFile->getClientOriginalExtension();
        $filename = str_replace(".{$ext}", "_{$size}_{$timestamp}.{$ext}", $uploadedFile->getClientOriginalName());
        $destinationPath = public_path('/images') . '/' . $filename;

        try {
            $imageObject->save($destinationPath);

            $this->localPaths->push([
                'path' => $destinationPath,
                'filename' => $filename,
                'size' => $size
            ]);

        } catch (NotWritableException $e) {
            $this->setError($e->getMessage());
        }

        return $this;
    }

    /**
     * @param ImageObject $imageObject
     * @param int $newWidth
     * @return ImageObject
     */
    protected function resize(ImageObject $imageObject, int $newWidth): ImageObject
    {
        $defaultWidth = $imageObject->getWidth();
        $defaultHeight = $imageObject->getHeight();
        $ratio = $newWidth / $defaultWidth;
        $newHeight = $defaultHeight * $ratio;
        $imageObject->resize($newWidth, $newHeight);
        return $imageObject;
    }

    /**
     * @return ImageUploadService
     */
    public function uploadToS3Bucket(): ImageUploadService
    {
        if ($this->localPaths->isEmpty()) {
            return $this;
        }

        $s3Client = $this->getS3Client();
        $this->localPaths->each(function (array $pathParts) use ($s3Client) {
            $result = $s3Client->putObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $pathParts['filename'],
                'SourceFile' => $pathParts['path']
            ]);
            if ($result->hasKey('ObjectURL')) {
                $this->cdnUrls->push([
                    'url' => $this->getCdnUrl($result->get('ObjectURL')),
                    'size' => $pathParts['size']
                ]);
            }
        });

        return $this;
    }

    /**
     * @return ImageUploadService
     */
    public function persistData(): ImageUploadService
    {
        if ($this->cdnUrls->isEmpty()) {
            return $this;
        }

        $this->cdnUrls->each(function (array $urlArray) {
            $image = new ImageUpload();

            $image->image_name = $this->imageName;
            $image->size = $urlArray['size'];
            $image->cdn_url = $urlArray['url'];

            $image->save();
        });

        return $this;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getImageData(Request $request): array
    {
        $imageName = $request->query('key');

        return ImageUpload::query()->where('image_name', '=', $imageName)
            ->get()
            ->toArray();
    }

    /**
     * @return S3Client
     */
    protected function getS3Client(): S3Client
    {
        return new S3Client([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);
    }

    /**
     * @param string $objectUrl
     * @return string
     */
    protected function getCdnUrl(string $objectUrl): string
    {
        $pathInfo = pathinfo($objectUrl);

        return 'https://' . env('CLOUDFRONT_DOMAIN') . '/' . $pathInfo['basename'];
    }

    /**
     * @param string $error
     */
    protected function setError(string $error): void
    {
        $this->error = $error;
    }
}
