<?php

namespace App\Filament\Resources\Media\Pages;

use App\Cms\Sanitizer;
use App\Filament\Resources\Media\MediaResource;
use App\Media\MediaOptions;
use App\Media\MediaUploader;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected static ?string $title = 'Upload to the media library';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            MediaOptions::fileField('file', null)->required(),
            ...MediaResource::metadataFields(),
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $file = is_array($data['file']) ? reset($data['file']) : $data['file'];
        /** @var TemporaryUploadedFile $file */
        $meta = Sanitizer::data(collect($data)->except('file')->all());
        $media = app(MediaUploader::class)->store($file, null, [], auth()->user(), $file->getClientOriginalName());
        $media->forceFill(array_filter($meta, fn ($v) => $v !== null && $v !== ''))->save();

        return $media;
    }

    protected function getRedirectUrl(): string
    {
        return MediaResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
