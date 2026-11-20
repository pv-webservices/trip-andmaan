<?php

namespace App\Media;

use App\Cms\ContentVersion;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use SplFileInfo;

/**
 * Validates and stores uploads. The file type is decided from the file's
 * content (finfo + format checks), never from its name; images are decoded
 * and re-encoded to WebP, so nothing executable can be stored. Stored names
 * are random and carry only a fixed extension for their type.
 */
class MediaUploader
{
    public function __construct(private readonly ImageProcessor $images) {}

    /**
     * @param  array{title?:string, alt?:string, caption?:string}  $meta
     */
    public function store(SplFileInfo $file, ?string $expectedType, array $meta = [], ?User $user = null, ?string $originalName = null): Media
    {
        [$type, $mime] = $this->detect($file, $expectedType);
        $directory = config('cms.media.directory').'/'.now()->format('Y/m');
        $stored = $this->storeFiles($file, $type, $mime, $directory);
        $name = $this->cleanName($originalName ?? $file->getFilename());

        return Media::query()->create([
            ...$stored,
            'type' => $type,
            'disk' => config('cms.media.disk'),
            'original_name' => $name,
            'title' => $meta['title'] ?? pathinfo($name, PATHINFO_FILENAME),
            'alt' => $meta['alt'] ?? null,
            'caption' => $meta['caption'] ?? null,
            'uploaded_by' => $user?->id,
        ]);
    }

    /**
     * Replace the file of an existing media item. Every place that uses it
     * changes, so callers must confirm the usages with the administrator first.
     */
    public function replace(Media $media, SplFileInfo $file, ?string $originalName = null): Media
    {
        [$type, $mime] = $this->detect($file, $media->type);
        $old = $media->allPaths();
        $directory = config('cms.media.directory').'/'.now()->format('Y/m');
        $stored = $this->storeFiles($file, $type, $mime, $directory);
        DB::transaction(fn () => $media->forceFill([...$stored, 'original_name' => $this->cleanName($originalName ?? $file->getFilename())])->save());
        Storage::disk($media->disk)->delete($old);
        ContentVersion::bump();

        return $media;
    }

    public function delete(Media $media): void
    {
        if ($media->usages()->exists()) {
            throw ValidationException::withMessages(['media' => 'This file is still used on the website. Replace or remove it in those places first.']);
        }
        $paths = $media->allPaths();
        $media->delete();
        Storage::disk($media->disk)->delete($paths);
    }

    /** @return array{0:string,1:string} [type, mime] */
    public function detect(SplFileInfo $file, ?string $expectedType = null): array
    {
        $path = $file->getRealPath();
        if (! $path || ! is_file($path)) {
            throw ValidationException::withMessages(['file' => 'The upload did not arrive. Please try again.']);
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: '';
        $type = collect(config('cms.media.types'))->search(fn ($mimes) => array_key_exists($mime, $mimes));
        if ($type === false) {
            throw ValidationException::withMessages(['file' => 'This file type is not allowed. Use JPEG, PNG or WebP images, MP4 or WebM videos, or PDF documents.']);
        }
        if ($expectedType && $type !== $expectedType) {
            throw ValidationException::withMessages(['file' => "Please choose a {$this->noun($expectedType)} file here."]);
        }
        $limit = (int) config("cms.media.{$type}_max_mb") * 1024 * 1024;
        if (filesize($path) > $limit) {
            throw ValidationException::withMessages(['file' => 'The file is larger than the '.config("cms.media.{$type}_max_mb").' MB limit for '.$this->noun($type).' files.']);
        }
        if ($type === 'document' && file_get_contents($path, false, null, 0, 5) !== '%PDF-') {
            throw ValidationException::withMessages(['file' => 'This does not look like a valid PDF.']);
        }
        if ($type === 'video' && ! $this->looksLikeVideo($path, $mime)) {
            throw ValidationException::withMessages(['file' => 'This does not look like a valid MP4 or WebM video.']);
        }

        return [$type, $mime];
    }

    /** @return array{path:string, mime:string, size:int, width:?int, height:?int, variants:?array} */
    private function storeFiles(SplFileInfo $file, string $type, string $mime, string $directory): array
    {
        $disk = Storage::disk(config('cms.media.disk'));
        $base = $directory.'/'.Str::lower(Str::ulid());

        if ($type === 'image') {
            $result = $this->images->process($file->getRealPath(), $disk->path(''), $base);

            return [
                'path' => $result['path'],
                'mime' => 'image/webp',
                'size' => $disk->size($result['path']),
                'width' => $result['width'],
                'height' => $result['height'],
                'variants' => $result['variants'],
            ];
        }

        $path = $base.'.'.config("cms.media.types.{$type}.{$mime}");
        $stream = fopen($file->getRealPath(), 'rb');
        $disk->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        return ['path' => $path, 'mime' => $mime, 'size' => $disk->size($path), 'width' => null, 'height' => null, 'variants' => null];
    }

    private function looksLikeVideo(string $path, string $mime): bool
    {
        $head = (string) file_get_contents($path, false, null, 0, 12);

        return $mime === 'video/webm'
            ? str_starts_with($head, "\x1A\x45\xDF\xA3")
            : substr($head, 4, 4) === 'ftyp';
    }

    private function cleanName(string $name): string
    {
        $name = preg_replace('/[^\pL\pN._ -]+/u', '', basename($name)) ?: 'upload';

        return mb_substr($name, 0, 180);
    }

    private function noun(string $type): string
    {
        return ['image' => 'image', 'video' => 'video', 'document' => 'PDF'][$type] ?? $type;
    }
}
