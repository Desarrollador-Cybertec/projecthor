<?php

declare(strict_types=1);

namespace App\Support\Images;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Generates JPEG thumbnails for stored images using GD.
 */
class ImageThumbnailer
{
    public function __construct(
        private int $maxWidth = 640,
        private int $quality = 80,
    ) {}

    /**
     * Create a thumbnail next to the source image on the same disk.
     * Returns the thumbnail path or null when the source is not a
     * supported raster image (e.g. SVG).
     */
    public function make(string $path, ?string $disk = null): ?string
    {
        $disk ??= config('filesystems.default');
        $contents = Storage::disk($disk)->get($path);

        if ($contents === null) {
            return null;
        }

        $source = @imagecreatefromstring($contents);

        if ($source === false) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        $targetWidth = min($width, $this->maxWidth);
        $targetHeight = (int) round($height * ($targetWidth / $width));

        $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($thumbnail, 255, 255, 255);
        imagefill($thumbnail, 0, 0, $white);
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagejpeg($thumbnail, null, $this->quality);
        $encoded = (string) ob_get_clean();

        imagedestroy($source);
        imagedestroy($thumbnail);

        $directory = Str::beforeLast($path, '/');
        $thumbnailPath = $directory.'/thumbs/'.Str::beforeLast(basename($path), '.').'.jpg';

        Storage::disk($disk)->put($thumbnailPath, $encoded);

        return $thumbnailPath;
    }
}
