<?php

declare(strict_types=1);

use App\Exceptions\ScrapingException;
use App\Services\WebpageMetadataScraperService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->scraper = new WebpageMetadataScraperService;
});

test('extractOpenGraphMetadata extracts og tags correctly', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta property="og:title" content="Challenge AgriTech Sahel 2026">
    <meta property="og:description" content="Financement d'amorçage pour les startups agricoles.">
    <meta property="og:image" content="https://example.com/images/banner.jpg">
</head>
<body></body>
</html>
HTML;

    $metadata = $this->scraper->extractOpenGraphMetadata($html, 'https://example.com/details');

    expect($metadata->title)->toBe('Challenge AgriTech Sahel 2026')
        ->and($metadata->description)->toBe("Financement d'amorçage pour les startups agricoles.")
        ->and($metadata->imageUrl)->toBe('https://example.com/images/banner.jpg')
        ->and($metadata->hasImage())->toBeTrue();
});

test('extractOpenGraphMetadata falls back to twitter tags and title tag', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Bourse Climat Afrique</title>
    <meta name="description" content="Appel à projets environnement.">
    <meta name="twitter:image" content="/assets/twitter-card.png">
</head>
<body></body>
</html>
HTML;

    $metadata = $this->scraper->extractOpenGraphMetadata($html, 'https://example.com/call');

    expect($metadata->title)->toBe('Bourse Climat Afrique')
        ->and($metadata->description)->toBe('Appel à projets environnement.')
        ->and($metadata->imageUrl)->toBe('https://example.com/assets/twitter-card.png');
});

test('extractOpenGraphMetadata resolves relative and scheme-relative URLs', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta property="og:image" content="//cdn.example.org/banner.png">
</head>
<body></body>
</html>
HTML;

    $metadata = $this->scraper->extractOpenGraphMetadata($html, 'https://sub.example.com/page');
    expect($metadata->imageUrl)->toBe('https://cdn.example.org/banner.png');

    $htmlRelative = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta property="og:image" content="subfolder/photo.webp">
</head>
<body></body>
</html>
HTML;

    $metadataRelative = $this->scraper->extractOpenGraphMetadata($htmlRelative, 'https://example.com/deep/page.html');
    expect($metadataRelative->imageUrl)->toBe('https://example.com/deep/subfolder/photo.webp');
});

test('scrapeFromUrl rejects invalid or non-http URLs', function () {
    expect(fn () => $this->scraper->scrapeFromUrl('not-a-valid-url'))
        ->toThrow(ScrapingException::class);

    expect(fn () => $this->scraper->scrapeFromUrl('ftp://example.com/file'))
        ->toThrow(ScrapingException::class);
});

test('scrapeFromUrl blocks access to localhost SSRF targets', function () {
    expect(fn () => $this->scraper->scrapeFromUrl('http://localhost/admin'))
        ->toThrow(ScrapingException::class);

    expect(fn () => $this->scraper->scrapeFromUrl('http://127.0.0.1:8000/secret'))
        ->toThrow(ScrapingException::class);
});

test('scrapeFromUrl fetches and parses HTML with Http fake', function () {
    Http::fake([
        'https://concours-innovation.africa/*' => Http::response(<<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta property="og:title" content="Fonds Vert CUBE 2026">
    <meta property="og:image" content="https://concours-innovation.africa/visuel.jpg">
</head>
<body></body>
</html>
HTML, 200),
    ]);

    $metadata = $this->scraper->scrapeFromUrl('https://concours-innovation.africa/postuler');

    expect($metadata->title)->toBe('Fonds Vert CUBE 2026')
        ->and($metadata->imageUrl)->toBe('https://concours-innovation.africa/visuel.jpg')
        ->and($metadata->hasImage())->toBeTrue();
});

test('downloadAndStoreCoverImage downloads remote image and saves to public disk', function () {
    Storage::fake('public');

    Http::fake([
        'https://example.com/banner.jpg' => Http::response('fake-binary-image-content', 200, [
            'Content-Type' => 'image/jpeg',
        ]),
    ]);

    $storedPath = $this->scraper->downloadAndStoreCoverImage('https://example.com/banner.jpg');

    expect($storedPath)->not->toBeNull()
        ->and($storedPath)->toStartWith('opportunities/covers/')
        ->and($storedPath)->toEndWith('.jpg');

    Storage::disk('public')->assertExists($storedPath);
});
