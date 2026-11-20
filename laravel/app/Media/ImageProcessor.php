<?php

namespace App\Media;

use GdImage;
use Illuminate\Validation\ValidationException;

/**
 * Decodes an uploaded JPEG/PNG/WebP with GD, applies EXIF rotation and writes
 * a re-encoded WebP original (capped in size) plus responsive WebP widths.
 * Requires only the GD extension with WebP support.
 */
class ImageProcessor
{
    /**
     * @return array{path:string, width:int, height:int, variants:array<string,string>}
     */
    public function process(string $source, string $diskRoot, string $base): array
    {
        if (! function_exists('imagewebp')) {
            throw ValidationException::withMessages(['file' => 'The server cannot create WebP images (PHP GD with WebP support is required).']);
        }
        $info = @getimagesize($source);
        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw ValidationException::withMessages(['file' => 'This image could not be read. Use a JPEG, PNG or WebP file.']);
        }
        [$width, $height] = $info;
        if ($width < 1 || $height < 1 || $width * $height > (int) config('cms.media.image_max_pixels')) {
            throw ValidationException::withMessages(['file' => 'The image dimensions are too large. Please resize it to under about 6000 pixels wide and try again.']);
        }

        $image = $this->decode($source, $info[2]);
        $image = $this->orient($image, $source, $info[2]);
        $width = imagesx($image);
        $height = imagesy($image);

        $quality = (int) config('cms.media.webp_quality', 80);
        $max = (int) config('cms.media.image_max_dimension', 2400);
        $scale = min(1, $max / max($width, $height));
        $originalWidth = (int) round($width * $scale);
        $originalHeight = (int) round($height * $scale);
        $original = $scale < 1 ? $this->resize($image, $originalWidth, $originalHeight) : $image;

        $path = $base.'.webp';
        $this->write($original, $diskRoot.$path, $quality);

        $variants = [];
        foreach (config('cms.media.image_widths') as $target) {
            if ($target >= $originalWidth) {
                break;
            }
            $variant = $this->resize($original, $target, (int) round($originalHeight * $target / $originalWidth));
            $variantPath = "{$base}-{$target}.webp";
            $this->write($variant, $diskRoot.$variantPath, $quality);
            $variants[(string) $target] = $variantPath;
            imagedestroy($variant);
        }
        $variants[(string) $originalWidth] = $path;

        if ($original !== $image) {
            imagedestroy($original);
        }
        imagedestroy($image);

        return ['path' => $path, 'width' => $originalWidth, 'height' => $originalHeight, 'variants' => $variants];
    }

    private function decode(string $source, int $type): GdImage
    {
        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => @imagecreatefromwebp($source),
        };
        if (! $image instanceof GdImage) {
            throw ValidationException::withMessages(['file' => 'This image appears to be damaged and could not be processed.']);
        }
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    private function orient(GdImage $image, string $source, int $type): GdImage
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }
        $orientation = (int) (@exif_read_data($source)['Orientation'] ?? 1);
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };
        if ($rotated instanceof GdImage) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }

    private function resize(GdImage $image, int $width, int $height): GdImage
    {
        $target = imagecreatetruecolor(max(1, $width), max(1, $height));
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        return $target;
    }

    private function write(GdImage $image, string $path, int $quality): void
    {
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        if (! imagewebp($image, $path, $quality)) {
            throw ValidationException::withMessages(['file' => 'The image could not be saved. Please try again.']);
        }
    }
}
