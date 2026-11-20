<?php

namespace App\Filament\Resources\Destinations\Pages;

use App\Cms\Sanitizer;
use App\Filament\Resources\Destinations\DestinationResource;
use App\Filament\Support\Admin;
use App\Media\MediaUsageRecorder;
use App\Models\Destination;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditDestination extends EditRecord
{
    protected static string $resource = DestinationResource::class;

    public function getSubheading(): ?string
    {
        /** @var Destination $d */
        $d = $this->getRecord();

        return $d->statusLabel().' · Last edited '.Admin::when($d->updated_at).' · Published '.Admin::when($d->published_at);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return [...Sanitizer::data($data), 'updated_by' => auth()->id()];
    }

    protected function afterSave(): void
    {
        MediaUsageRecorder::forModel($this->getRecord()->fresh());
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Save draft');
    }

    protected function getHeaderActions(): array
    {
        /** @var Destination $d */
        $d = $this->getRecord();

        return [
            Admin::previewAction(fn () => '/preview/destinations/'.$d->slug.'/'),
            Action::make('publish')->label('Save & publish')->icon(Heroicon::OutlinedRocketLaunch)->color('success')
                ->requiresConfirmation()
                ->action(function () use ($d) {
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    Admin::attempt(fn () => $d->refresh()->publish(auth()->user()), 'Published at /destinations/'.$d->slug.'/');
                }),
            ActionGroup::make([
                Action::make('unpublish')->label('Unpublish')->icon(Heroicon::OutlinedEyeSlash)->visible(fn () => $d->isLive())
                    ->requiresConfirmation()
                    ->action(fn () => Admin::attempt(fn () => $d->unpublish(auth()->user()), 'Unpublished.')),
                Action::make('archive')->label('Archive')->icon(Heroicon::OutlinedArchiveBox)->color('danger')
                    ->requiresConfirmation()
                    ->action(function () use ($d) {
                        Admin::attempt(fn () => $d->unpublish(auth()->user(), 'archived'), 'Archived.');
                        $this->redirect(DestinationResource::getUrl());
                    }),
            ])->label('More')->button()->color('gray'),
        ];
    }
}
