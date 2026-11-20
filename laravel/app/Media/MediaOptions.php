<?php

namespace App\Media;

use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/** Options, labels and the inline upload form for media picker fields. */
class MediaOptions
{
    public static function recent(string $type, mixed $selected = null): array
    {
        $items = Media::query()->ofType($type)->latest('id')->limit(40)->get();
        if (is_numeric($selected) && ! $items->contains('id', (int) $selected) && ($current = Media::query()->find($selected))) {
            $items->prepend($current);
        }

        return $items->mapWithKeys(fn (Media $m) => [$m->id => self::html($m)])->all();
    }

    public static function search(string $type, string $term): array
    {
        return Media::query()->ofType($type)->search($term)->latest('id')->limit(40)->get()
            ->mapWithKeys(fn (Media $m) => [$m->id => self::html($m)])->all();
    }

    public static function label(mixed $id): ?string
    {
        $media = is_numeric($id) ? Media::query()->find($id) : null;

        return $media ? self::html($media) : null;
    }

    /** Thumbnail + name, escaped (the select renders option labels as HTML). */
    public static function html(Media $media): string
    {
        $name = e($media->label());
        $meta = e(match ($media->type) {
            'image' => "{$media->width}×{$media->height} · {$media->humanSize()}",
            default => strtoupper(pathinfo($media->path, PATHINFO_EXTENSION)).' · '.$media->humanSize(),
        });
        $thumb = $media->isImage()
            ? '<img src="'.e($media->urlNear(200)).'" alt="" style="width:56px;height:40px;object-fit:cover;border-radius:4px;flex:none">'
            : '<span style="width:56px;height:40px;display:grid;place-items:center;border-radius:4px;background:#e5e7eb;font-size:11px;flex:none">'.($media->type === 'video' ? 'VIDEO' : 'PDF').'</span>';

        return '<span style="display:flex;gap:10px;align-items:center">'.$thumb.'<span><span style="display:block;font-weight:600">'.$name.'</span><span style="font-size:12px;opacity:.7">#'.$media->id.' · '.$meta.'</span></span></span>';
    }

    public static function accepted(string $type): array
    {
        return array_keys(config("cms.media.types.{$type}", []));
    }

    public static function uploadForm(string $type): array
    {
        return [
            self::fileField('file', $type)->required(),
            TextInput::make('title')->label('Name in the media library')->maxLength(150),
            TextInput::make('alt')->label('Alternative text')->maxLength(250)->visible($type === 'image'),
        ];
    }

    public static function fileField(string $name, ?string $type): FileUpload
    {
        $types = $type ? [$type] : ['image', 'video', 'document'];
        $maxMb = max(array_map(fn ($t) => (int) config("cms.media.{$t}_max_mb"), $types));

        return FileUpload::make($name)
            ->label('File')
            ->storeFiles(false)
            ->acceptedFileTypes(array_merge(...array_map(fn ($t) => self::accepted($t), $types)))
            ->maxSize($maxMb * 1024)
            ->helperText(implode(' ', array_map(fn ($t) => match ($t) {
                'image' => 'Images: JPEG, PNG or WebP up to '.config('cms.media.image_max_mb').' MB (converted to WebP in several sizes).',
                'video' => 'Videos: web-ready MP4 or WebM up to '.config('cms.media.video_max_mb').' MB. Videos are not converted, so export them small (720p, a few MB).',
                'document' => 'PDF up to '.config('cms.media.document_max_mb').' MB.',
            }, $types)));
    }

    public static function createFromUpload(array $data, ?string $type): int
    {
        $file = $data['file'] ?? null;
        if (is_array($file)) {
            $file = reset($file);
        }
        if (! $file instanceof TemporaryUploadedFile) {
            throw ValidationException::withMessages(['file' => 'Please choose a file to upload.']);
        }

        return app(MediaUploader::class)->store($file, $type, array_filter([
            'title' => $data['title'] ?? null,
            'alt' => $data['alt'] ?? null,
        ]), auth()->user(), $file->getClientOriginalName())->id;
    }
}
