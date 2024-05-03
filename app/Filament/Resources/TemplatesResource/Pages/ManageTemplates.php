<?php

namespace App\Filament\Resources\TemplatesResource\Pages;

use App\Filament\Resources\TemplatesResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTemplates extends ManageRecords
{
    protected static string $resource = TemplatesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
