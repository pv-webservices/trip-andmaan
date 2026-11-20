<?php

namespace App\Filament\Resources\Sections;

use App\Filament\Resources\Sections\Pages\EditSection;
use App\Models\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;

/** Section editor; reached from Pages & global settings (not in the menu). */
class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'edit' => EditSection::route('/{record}/edit'),
        ];
    }
}
