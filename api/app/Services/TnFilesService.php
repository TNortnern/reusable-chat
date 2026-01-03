<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TnFilesService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $cdnUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.tn_files.base_url', 'https://tn-files.up.railway.app');
        $this->apiKey = config('services.tn_files.api_key');
        $this->cdnUrl = config('services.tn_files.cdn_url', 'https://hastets.b-cdn.net');
    }

    /**
     * Upload a file to tn-files service.
     *
     * @param UploadedFile $file The file to upload
     * @param string $path The path prefix (e.g., "workspaces/{id}/attachments")
     * @return array{success: bool, url?: string, filename?: string, error?: string}
     */
    public function upload(UploadedFile $file, string $path = ''): array
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'error' => 'TN_FILES_API_KEY not configured',
            ];
        }

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;

        // Ensure path ends with / if not empty
        $uploadPath = $path ? rtrim($path, '/') . '/' : '';

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->attach(
                'files',
                file_get_contents($file->getRealPath()),
                $filename
            )->post("{$this->baseUrl}/api/files/upload", [
                'path' => $uploadPath,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // tn-files returns results array with uploaded files
                $uploadedFile = $data['results'][0] ?? null;

                if ($uploadedFile && $uploadedFile['success']) {
                    return [
                        'success' => true,
                        'url' => $uploadedFile['url'],
                        'filename' => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ];
                }

                return [
                    'success' => false,
                    'error' => 'Upload response missing file data',
                ];
            }

            return [
                'success' => false,
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a file from tn-files service.
     *
     * @param string $path Full path to the file
     * @return array{success: bool, error?: string}
     */
    public function delete(string $path): array
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'error' => 'TN_FILES_API_KEY not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->delete("{$this->baseUrl}/api/files", [
                'path' => $path,
            ]);

            return [
                'success' => $response->successful(),
                'error' => $response->successful() ? null : $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get the CDN URL for a file path.
     */
    public function getCdnUrl(string $path): string
    {
        return rtrim($this->cdnUrl, '/') . '/' . ltrim($path, '/');
    }
}
