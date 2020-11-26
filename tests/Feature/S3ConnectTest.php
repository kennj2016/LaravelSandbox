<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class S3ConnectTest extends TestCase
{
    public function testHasEnvironmentKeys(): void
    {
        $awsDefaultRegion = env('AWS_DEFAULT_REGION');
        self::assertNotEmpty($awsDefaultRegion);

        $awsBucket = env('AWS_BUCKET');
        self::assertNotEmpty($awsBucket);

        $awsAccessKeyId = env('AWS_ACCESS_KEY_ID');
        self::assertNotEmpty($awsAccessKeyId);

        $awsSecretAccessKey = env('AWS_SECRET_ACCESS_KEY');
        self::assertNotEmpty($awsSecretAccessKey);

        $cloudfrontDomain = env('CLOUDFRONT_DOMAIN');
        self::assertNotEmpty($cloudfrontDomain);
    }
}
