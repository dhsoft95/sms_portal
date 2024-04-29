<?php

namespace App\Filament\Resources\CampaignsResource\Pages;

use App\Filament\Resources\CampaignsResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCampaigns extends ManageRecords
{
    protected static string $resource = CampaignsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
