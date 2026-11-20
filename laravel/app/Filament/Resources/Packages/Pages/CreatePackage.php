<?php

namespace App\Filament\Resources\Packages\Pages;

use App\Filament\Resources\Packages\PackageResource;
use App\Media\MediaUsageRecorder;
use Filament\Resources\Pages\CreateRecord;

class CreatePackage extends CreateRecord
{
    protected static string $resource = PackageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [...$data, 'status' => 'draft', 'updated_by' => auth()->id(), 'focal' => $data['focal'] ?? '50% 50%'];
    }

    protected function afterCreate(): void
    {
        MediaUsageRecorder::forModel($this->getRecord()->fresh());
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Package saved as a draft. Add its itinerary, preview it, then publish.';
    }
}
