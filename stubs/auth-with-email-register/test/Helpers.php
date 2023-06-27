<?php

declare(strict_types=1);

use App\Models\ApplicationKey;
use Illuminate\Http\Request;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Thuraaung\ApiHelpers\Http\Enums\Status;
use Thuraaung\SpaceStorage\Facades\SpaceStorage;

use function Pest\Laravel\mock;
use function Pest\Laravel\withHeaders;

function errorAssertJson()
{
    return
        fn (AssertableJson $json) => $json
            ->where('title', 'Validation Error!')
            ->where('status', Status::UNPROCESSABLE_CONTENT->value)
            ->has('errors')
            ->whereType('errors', 'array');
}

function validationJsonStructure($key): array
{
    return [
        'title',
        'errors' => [$key],
        'status',
    ];
}

function getImageUrl(int $width = 5, int $height = 5): string
{
    return fake()->imageUrl(width: $width, height: $height);
}

function mockProfileUpload(string $image = 'test.jpg'): void
{
    mock(SpaceStorage::class, function (MockInterface $mock) use ($image): void {
        $mock->shouldReceive('upload')
            ->once()
            ->andReturn("profiles/{$image}");
    });
}

function mockProfileUploadNotCall(): void
{
    mock(SpaceStorage::class, function (MockInterface $mock): void {
        $mock->shouldNotHaveBeenCalled();
    });
}

function createRequest(string $method, string $uri, HeaderBag $headers): Request
{
    $symfonyRequest = SymfonyRequest::create(
        uri: $uri,
        method: $method,
    );

    $symfonyRequest->headers = $headers;

    return Request::createFromBase($symfonyRequest);
}

function withAppKeyHeaders(bool $obsoleted = false): void
{
    $appKey = ApplicationKey::factory()->create(['obsoleted' => $obsoleted]);

    withHeaders([
        'app-id' => $appKey->app_id,
        'app-secrete' => $appKey->app_secrete,
    ]);
}


function withAuthorizationHeader(string $token): void
{
    withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ]);
}
