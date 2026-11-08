<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ScrapedOpportunityMetadata;
use App\Exceptions\ScrapingException;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebpageMetadataScraperService
{
    private const HTTP_TIMEOUT_SECONDS = 6;

    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 HubsAfricaBot/2.0';

    private const MAX_IMAGE_BYTES = 8388608; // 8 Mo

    /**
     * Interroge une URL externe et extrait les métadonnées Open Graph (image, titre, description).
     *
     * @throws ScrapingException
     */
    public function scrapeFromUrl(string $targetWebpageUrl): ScrapedOpportunityMetadata
    {
        $this->validatePublicUrl($targetWebpageUrl);

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT_SECONDS)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get($targetWebpageUrl);
        } catch (\Throwable $exception) {
            throw ScrapingException::unreachableHost($targetWebpageUrl, $exception->getMessage());
        }

        if (! $response->successful()) {
            throw ScrapingException::unreachableHost($targetWebpageUrl, "Code HTTP statut {$response->status()}");
        }

        $htmlContent = $response->body();

        return $this->extractOpenGraphMetadata($htmlContent, $targetWebpageUrl);
    }

    /**
     * Fonction pure : analyse le code HTML et extrait les balises Open Graph et Twitter.
     */
    public function extractOpenGraphMetadata(string $htmlContent, string $baseUrl): ScrapedOpportunityMetadata
    {
        if (trim($htmlContent) === '') {
            return new ScrapedOpportunityMetadata;
        }

        $previousLibXmlState = libxml_use_internal_errors(true);
        $document = new DOMDocument;

        // Préfixe pour forcer l'analyseur HTML en encodage UTF-8 sans avertissement de dépréciation
        @$document->loadHTML('<?xml encoding="UTF-8">'.$htmlContent, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previousLibXmlState);

        $xpath = new DOMXPath($document);

        $extractedImageUrl = $this->extractMetaContent($xpath, [
            '//meta[@property="og:image"]/@content',
            '//meta[@property="og:image:secure_url"]/@content',
            '//meta[@name="twitter:image"]/@content',
            '//meta[@name="twitter:image:src"]/@content',
            '//link[@rel="image_src"]/@href',
        ]);

        $extractedTitle = $this->extractMetaContent($xpath, [
            '//meta[@property="og:title"]/@content',
            '//meta[@name="twitter:title"]/@content',
            '//title/text()',
        ]);

        $extractedDescription = $this->extractMetaContent($xpath, [
            '//meta[@property="og:description"]/@content',
            '//meta[@name="twitter:description"]/@content',
            '//meta[@name="description"]/@content',
        ]);

        $normalizedImageUrl = $extractedImageUrl !== null
            ? $this->resolveAbsoluteUrl($extractedImageUrl, $baseUrl)
            : null;

        return new ScrapedOpportunityMetadata(
            imageUrl: $normalizedImageUrl,
            title: $this->cleanText($extractedTitle),
            description: $this->cleanText($extractedDescription),
        );
    }

    /**
     * Télécharge une image distante et la stocke de manière sécurisée sur le disque public.
     *
     * @return string|null Chemin relatif du fichier sur le disque public (ex: 'opportunities/covers/uuid.jpg')
     */
    public function downloadAndStoreCoverImage(string $remoteImageUrl): ?string
    {
        if (empty($remoteImageUrl)) {
            return null;
        }

        $this->validatePublicUrl($remoteImageUrl);

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT_SECONDS)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get($remoteImageUrl);
        } catch (\Throwable $exception) {
            Log::warning("Échec du téléchargement de l'image distante {$remoteImageUrl} : {$exception->getMessage()}");

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $imageBytes = $response->body();
        if (strlen($imageBytes) === 0 || strlen($imageBytes) > self::MAX_IMAGE_BYTES) {
            return null;
        }

        $contentType = (string) $response->header('Content-Type');
        $extension = $this->resolveExtensionFromContentType($contentType, $remoteImageUrl);

        if ($extension === null) {
            return null;
        }

        $storedFilename = 'opportunities/covers/'.Str::lower(Str::random(32)).'.'.$extension;

        Storage::disk('public')->put($storedFilename, $imageBytes);

        return $storedFilename;
    }

    /**
     * Résout une URL relative en URL absolue en fonction de l'URL de base.
     */
    public function resolveAbsoluteUrl(string $relativeOrAbsoluteUrl, string $baseUrl): ?string
    {
        $candidateUrl = trim($relativeOrAbsoluteUrl);

        if ($candidateUrl === '') {
            return null;
        }

        // URL absolue commençant par http:// ou https://
        if (preg_match('#^https?://#i', $candidateUrl)) {
            return $candidateUrl;
        }

        // URL avec scheme-relative (commençant par //)
        if (str_starts_with($candidateUrl, '//')) {
            $baseScheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';

            return "{$baseScheme}:{$candidateUrl}";
        }

        $parsedBase = parse_url($baseUrl);
        $scheme = $parsedBase['scheme'] ?? 'https';
        $host = $parsedBase['host'] ?? '';
        $port = isset($parsedBase['port']) ? ":{$parsedBase['port']}" : '';

        if (empty($host)) {
            return null;
        }

        // Chemin absolu commençant par /
        if (str_starts_with($candidateUrl, '/')) {
            return "{$scheme}://{$host}{$port}{$candidateUrl}";
        }

        // Chemin relatif
        $basePath = $parsedBase['path'] ?? '/';
        $directory = rtrim(dirname($basePath), '/\\');

        return "{$scheme}://{$host}{$port}{$directory}/{$candidateUrl}";
    }

    /**
     * Vérifie la validité de l'URL et protège contre les attaques SSRF (Server-Side Request Forgery).
     */
    private function validatePublicUrl(string $url): void
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw ScrapingException::invalidUrl($url);
        }

        $parsedUrl = parse_url($url);
        $scheme = strtolower($parsedUrl['scheme'] ?? '');
        $host = strtolower($parsedUrl['host'] ?? '');

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw ScrapingException::invalidUrl($url);
        }

        if (empty($host)) {
            throw ScrapingException::invalidUrl($url);
        }

        // Protection contre SSRF (adresses privées, locales, loopback)
        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            throw ScrapingException::privateHostForbidden($host);
        }

        // Résolution DNS pour bloquer les adresses IP privées
        $resolvedIp = gethostbyname($host);
        if ($resolvedIp !== $host && filter_var($resolvedIp, FILTER_VALIDATE_IP)) {
            $isPrivate = ! filter_var(
                $resolvedIp,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );

            if ($isPrivate) {
                throw ScrapingException::privateHostForbidden($host);
            }
        }
    }

    /**
     * Extrait la première valeur trouvée parmi une liste de requêtes XPath.
     *
     * @param  array<int, string>  $xpathQueries
     */
    private function extractMetaContent(DOMXPath $xpath, array $xpathQueries): ?string
    {
        foreach ($xpathQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes !== false && $nodes->length > 0) {
                $content = trim((string) $nodes->item(0)?->nodeValue);
                if ($content !== '') {
                    return $content;
                }
            }
        }

        return null;
    }

    /**
     * Détermine l'extension d'image autorisée à partir du Content-Type ou de l'URL.
     */
    private function resolveExtensionFromContentType(string $contentType, string $url): ?string
    {
        $normalizedType = strtolower(trim(explode(';', $contentType)[0]));

        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (isset($mimeMap[$normalizedType])) {
            return $mimeMap[$normalizedType];
        }

        $urlPath = parse_url($url, PHP_URL_PATH) ?? '';
        $pathExtension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));

        if (in_array($pathExtension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return $pathExtension === 'jpeg' ? 'jpg' : $pathExtension;
        }

        return null;
    }

    private function cleanText(?string $rawText): ?string
    {
        if ($rawText === null) {
            return null;
        }

        $cleaned = trim(preg_replace('/\s+/', ' ', html_entity_decode($rawText, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

        return $cleaned !== '' ? $cleaned : null;
    }
}
