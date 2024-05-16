<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Imports\CustomerImporter;
use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomers extends ManageRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color("success")->requiresConfirmation(),
            Actions\CreateAction::make(),
//           ImportAction::make()
//               ->importer(CustomerImporter::class)
        ];
    }
}
