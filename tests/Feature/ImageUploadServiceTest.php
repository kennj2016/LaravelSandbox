<?php

namespace Tests\Feature;

use Faker\Factory;
use Tests\TestCase;
use Aws\S3\S3Client;

class ImageUploadServiceTest extends TestCase
{
    /**
     * @var mixed
     */
    private $awsDefaultRegion;
    /**
     * @var mixed
     */
    private $awsBucket;
    /**
     * @var mixed
     */
    private $awsAccessKeyId;
    /**
     * @var mixed
     */
    private $awsSecretAccessKey;
    /**
     * @var mixed
     */
    private $cloudfrontDomain;

    public function setUp(): void
    {
        $this->awsDefaultRegion = env('AWS_DEFAULT_REGION');
        $this->awsBucket = env('AWS_BUCKET');
        $this->awsAccessKeyId = env('AWS_ACCESS_KEY_ID');
        $this->awsSecretAccessKey = env('AWS_SECRET_ACCESS_KEY');
        $this->cloudfrontDomain = env('CLOUDFRONT_DOMAIN');

        parent::setUp();
    }

    public function testCanUploadTextFileToS3(): void
    {
        $faker = Factory::create();

        $s3 = $this->getS3Client();

        $filename = $faker->colorName . '.txt';

        $result = $s3->putObject([
            'Bucket' => $this->awsBucket,
            'Key'    => $filename,
            'Body'   => $faker->realText(),
        ]);

        self::assertStringNotContainsString($result, 'The specified bucket does not exist');
        self::assertStringContainsString($filename, $result['ObjectURL']);
    }

    protected function getS3Client(): S3Client
    {
        return new S3Client([
            'region' => $this->awsDefaultRegion,
            'version' => 'latest',
            'credentials' => [
                'key' => $this->awsAccessKeyId,
                'secret' => $this->awsSecretAccessKey,
            ]
        ]);
    }
}
