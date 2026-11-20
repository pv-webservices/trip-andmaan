<?php

namespace App\Filament\Resources\Sections\Pages;

use App\Cms\Sanitizer;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Sections\SectionResource;
use App\Filament\Support\Admin;
use App\Models\Section;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

/**
 * Structured editor for one section. "Save draft" stores the draft only;
 * "Publish" saves and makes it live; "Discard draft" returns to what is live.
 */
class EditSection extends EditRecord
{
    protected static string $resource = SectionResource::class;

    public function form(Schema $schema): Schema
    {
        /** @var Section $section */
        $section = $this->getRecord();

        return $schema->columns(1)->components([
            Text::make($section->definition()->description)->color('gray'),
            ...$section->definition()->schema(),
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->page->title.' › '.$this->getRecord()->label;
    }

    public function getSubheading(): ?string
    {
        /** @var Section $s */
        $s = $this->getRecord();

        return $s->statusLabel().' · Last edited '.Admin::when($s->draft_updated_at, $s->editor?->name)
            .' · Published '.Admin::when($s->published_at, $s->publisher?->name);
    }

    public function getBreadcrumbs(): array
    {
        $page = $this->getRecord()->page;

        return [
            PageResource::getUrl() => 'Pages',
            PageResource::getUrl('view', ['record' => $page]) => $page->title,
            $this->getRecord()->label,
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->getRecord()->draft_data ?? [];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Section $record */
        $data = Sanitizer::data($data);
        if ($errors = $record->definition()->errors($data)) {
            Notification::make()->danger()->title('Please fix this first')->body(implode(' ', $errors))->persistent()->send();
            throw new Halt;
        }
        $record->saveDraft($data, auth()->user());

        return $record;
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Save draft');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Draft saved. Preview it, then publish when ready.';
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        /** @var Section $section */
        $section = $this->getRecord();

        return [
            Admin::previewAction(fn () => $section->page->previewUrl()),
            Action::make('publish')
                ->label('Save & publish')
                ->icon(Heroicon::OutlinedRocketLaunch)
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Your changes to this section go live on the website immediately.')
                ->action(function () use ($section) {
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    $section->refresh();
                    if (! $section->draft_data) {
                        return;
                    }
                    $section->publish(auth()->user());
                    Notification::make()->success()->title('Published. The website shows the changes now.')->send();
                }),
            Action::make('discard')
                ->label('Discard draft')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('gray')
                ->visible(fn () => $section->published_data !== null && $section->hasUnpublishedChanges())
                ->requiresConfirmation()
                ->modalDescription('Your unpublished changes to this section will be lost and it returns to what visitors currently see.')
                ->action(function () use ($section) {
                    $section->discardDraft(auth()->user());
                    $this->fillForm();
                    Notification::make()->success()->title('Draft discarded.')->send();
                }),
        ];
    }
}
