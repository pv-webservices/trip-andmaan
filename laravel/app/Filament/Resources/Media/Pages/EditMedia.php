<?php

namespace App\Filament\Resources\Media\Pages;

use App\Cms\ContentVersion;
use App\Cms\Sanitizer;
use App\Filament\Resources\Media\MediaResource;
use App\Filament\Support\Admin;
use App\Media\MediaOptions;
use App\Media\MediaUploader;
use App\Models\Media;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Text;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Details of one file. "Replace file" changes the file everywhere it is used,
 * so the places are listed and must be confirmed when there is more than one.
 * To change one place only, choose a different file in that place instead.
 */
class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->label();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return Sanitizer::data($data);
    }

    protected function afterSave(): void
    {
        ContentVersion::bump(); // alt text / credits may be shown on live pages
    }

    protected function getHeaderActions(): array
    {
        /** @var Media $media */
        $media = $this->getRecord();
        $count = $media->usages()->distinct('label')->count('label');

        return [
            Action::make('replace')
                ->label('Replace file')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('warning')
                ->modalHeading('Replace this file everywhere it is used')
                ->modalDescription($count > 1
                    ? "This file is used in {$count} places. Replacing it changes all of them immediately, including published pages. To change only one place, open that section and choose a different file instead."
                    : 'The new file takes the place of this one immediately wherever it is used.')
                ->schema([
                    Text::make(new HtmlString(MediaResource::usages($media)->toHtml())),
                    MediaOptions::fileField('file', $media->type)->required(),
                    Checkbox::make('confirm')->label("I understand this changes all {$count} places listed above")
                        ->accepted()->visible($count > 1),
                ])
                ->action(function (array $data) use ($media) {
                    $file = is_array($data['file']) ? reset($data['file']) : $data['file'];
                    /** @var TemporaryUploadedFile $file */
                    if (Admin::attempt(fn () => app(MediaUploader::class)->replace($media, $file, $file->getClientOriginalName()), 'File replaced.')) {
                        $this->redirect(MediaResource::getUrl('edit', ['record' => $media]));
                    }
                }),
            Action::make('delete')
                ->label('Delete')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->disabled($count > 0)
                ->tooltip($count > 0 ? 'Used on the website. Remove or replace it in those places first.' : null)
                ->requiresConfirmation()
                ->modalDescription('The file is deleted permanently. This cannot be undone.')
                ->action(function () use ($media) {
                    if (Admin::attempt(fn () => app(MediaUploader::class)->delete($media), 'File deleted.')) {
                        $this->redirect(MediaResource::getUrl());
                    }
                }),
        ];
    }
}
