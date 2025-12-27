<?php
declare(strict_types=1);

namespace App\Service\Storage;

use Aws\S3\S3Client;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use function Cake\Core\env;

/**
 * S3 storage adapter for AWS S3 and S3-compatible services
 *
 * Supports:
 * - AWS S3 (default)
 * - MinIO (set S3_ENDPOINT and S3_USE_PATH_STYLE=true)
 * - Other S3-compatible services (Backblaze B2, Wasabi, etc.)
 *
 * Environment variables:
 * - S3_BUCKET_NAME_ATTACHMENTS: Bucket name (required)
 * - S3_ACCESS_KEY_ID: AWS access key
 * - S3_SECRET_ACCESS_KEY: AWS secret key
 * - AWS_DEFAULT_REGION: AWS region (default: eu-central-1)
 * - S3_ENDPOINT: Custom endpoint for S3-compatible services
 * - S3_USE_PATH_STYLE: Use path-style URLs (required for MinIO)
 */
class S3Adapter implements StorageAdapterInterface
{
    private S3Client $client;
    private string $bucket;

    /**
     * @param array<string, mixed>|null $config Optional configuration override
     */
    public function __construct(?array $config = null)
    {
        $region = $config['region'] ?? env('AWS_DEFAULT_REGION', 'eu-central-1');
        $endpoint = $config['endpoint'] ?? env('S3_ENDPOINT');
        $usePathStyle = filter_var(
            $config['use_path_style'] ?? env('S3_USE_PATH_STYLE', false),
            FILTER_VALIDATE_BOOLEAN,
        );

        $clientConfig = [
            'version' => 'latest',
            'region' => $region,
            'credentials' => [
                'key' => $config['access_key'] ?? env('S3_ACCESS_KEY_ID', ''),
                'secret' => $config['secret_key'] ?? env('S3_SECRET_ACCESS_KEY', ''),
            ],
        ];

        if ($endpoint) {
            $clientConfig['endpoint'] = $endpoint;
            $clientConfig['use_path_style_endpoint'] = $usePathStyle;
        }

        $this->client = new S3Client($clientConfig);
        $this->bucket = $config['bucket'] ?? env('S3_BUCKET_NAME_ATTACHMENTS', '');

        if (empty($this->bucket)) {
            throw new RuntimeException(
                'S3 bucket not configured. Set S3_BUCKET_NAME_ATTACHMENTS environment variable.',
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function put(UploadedFileInterface $file, string $key, ?string $contentType = null): StoredFile
    {
        $contentType = $contentType ?? $file->getClientMediaType() ?? 'application/octet-stream';
        $stream = $file->getStream();
        $body = $stream->isSeekable() ? $stream : $stream->detach();

        $result = $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'Body' => $body,
            'ContentType' => $contentType,
            'ContentDisposition' => sprintf('attachment; filename="%s"', $file->getClientFilename() ?? 'download'),
        ]);

        return new StoredFile(
            key: $key,
            etag: trim($result['ETag'] ?? '', '"'),
            size: $file->getSize() ?? 0,
        );
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): StoredFileStream
    {
        $result = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);

        return new StoredFileStream(
            stream: $result['Body'],
            contentType: $result['ContentType'] ?? 'application/octet-stream',
            contentLength: (int)($result['ContentLength'] ?? 0),
            filename: $this->extractFilename($result['ContentDisposition'] ?? null),
        );
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): void
    {
        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function exists(string $key): bool
    {
        return $this->client->doesObjectExist($this->bucket, $key);
    }

    /**
     * Extract filename from Content-Disposition header
     */
    private function extractFilename(?string $contentDisposition): ?string
    {
        if ($contentDisposition && preg_match('/filename="([^"]+)"/', $contentDisposition, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
