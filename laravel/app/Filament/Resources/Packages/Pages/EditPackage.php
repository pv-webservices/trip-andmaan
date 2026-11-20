<?php

namespace App\Filament\Resources\Packages\Pages;

use App\Filament\Resources\Packages\PackageResource;
use App\Filament\Support\Admin;
use App\Media\MediaUsageRecorder;
use App\Models\Package;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

/**
 * Saving keeps changes private; "Publish" makes the current version live.
 * Itinerary days and photos are saved with the package (relationships).
 */
class EditPackage extends EditRecord
{
    protected static string $resource = PackageResource::class;

    public function getSubheading(): ?string
    {
        /** @var Package $p */
        $p = $this->getRecord();

        return $p->statusLabel().' · Last edited '.Admin::when($p->updated_at).' · Published '.Admin::when($p->published_at);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return [...$data, 'updated_by' => auth()->id()];
    }

    protected function afterSave(): void
    {
        /** @var Package $p */
        $p = $this->getRecord();
        // Day and photo changes are saved through relationships; mark the package as changed too.
        $p->forceFill(['has_unpublished_changes' => true])->saveQuietly();
        MediaUsageRecorder::forModel($p->fresh());
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Save draft');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return $this->getRecord()->isLive()
            ? 'Saved. Visitors still see the published version until you publish the changes.'
            : 'Saved as a draft.';
    }

    protected function getHeaderActions(): array
    {
        /** @var Package $p */
        $p = $this->getRecord();

        return [
            Admin::previewAction(fn () => '/preview/tour-packages/'.$p->slug.'/'),
            Action::make('publish')
                ->label(fn () => $p->isLive() ? 'Save & publish changes' : 'Save & publish')
                ->icon(Heroicon::OutlinedRocketLaunch)->color('success')
                ->requiresConfirmation()
                ->modalDescription(fn () => $p->isLive() && $p->published_slug !== $p->slug
                    ? 'The address changes to /tour-packages/'.$p->slug.'/. The old address will redirect there.'
                    : 'This version of the package goes live on the website and in the sitemap.')
                ->action(function () use ($p) {
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    Admin::attempt(fn () => $p->refresh()->publish(auth()->user()), 'Published at /tour-packages/'.$p->slug.'/');
                    $this->refreshFormData(['status']);
                }),
            ActionGroup::make([
                Action::make('unpublish')->label('Unpublish')->icon(Heroicon::OutlinedEyeSlash)
                    ->visible(fn () => $p->isLive())
                    ->requiresConfirmation()
                    ->modalDescription('The package page stops working and the package disappears from lists and the sitemap. You can publish it again later.')
                    ->action(fn () => Admin::attempt(fn () => $p->unpublish(auth()->user()), 'Unpublished.')),
                Action::make('archive')->label('Archive')->icon(Heroicon::OutlinedArchiveBox)->color('danger')
                    ->visible(fn () => $p->status !== 'archived')
                    ->requiresConfirmation()
                    ->modalDescription('The package is unpublished and hidden from the package list. Nothing is deleted.')
                    ->action(function () use ($p) {
                        Admin::attempt(fn () => $p->unpublish(auth()->user(), 'archived'), 'Archived.');
                        $this->redirect(PackageResource::getUrl());
                    }),
                Action::make('restore')->label('Restore from archive')->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->visible(fn () => $p->status === 'archived')
                    ->action(fn () => Admin::attempt(fn () => $p->forceFill(['status' => 'draft'])->save(), 'Restored as a draft.')),
                Action::make('duplicate')->label('Duplicate')->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->requiresConfirmation()
                    ->modalDescription('Creates an unpublished copy with its own itinerary days and photos.')
                    ->action(function () use ($p) {
                        $copy = $p->duplicate(auth()->user());
                        MediaUsageRecorder::forModel($copy);
                        $this->redirect(PackageResource::getUrl('edit', ['record' => $copy]));
                    }),
            ])->label('More')->button()->color('gray'),
        ];
    }
}
