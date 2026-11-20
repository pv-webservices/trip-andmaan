<?php

namespace App\Filament\Resources\Destinations\Pages;

use App\Cms\Sanitizer;
use App\Filament\Resources\Destinations\DestinationResource;
use App\Media\MediaUsageRecorder;
use App\Models\Destination;
use Filament\Resources\Pages\CreateRecord;

class CreateDestination extends CreateRecord
{
    protected static string $resource = DestinationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = Sanitizer::data($data);

        return [...$data, 'status' => 'draft', 'updated_by' => auth()->id(), 'focal' => $data['focal'] ?? '50% 50%'];
    }

    protected function afterCreate(): void
    {
        /** @var Destination $d */
        $d = $this->getRecord();
        MediaUsageRecorder::forModel($d);
    }
}
