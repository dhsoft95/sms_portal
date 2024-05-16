<?php

namespace App\Filament\Imports;

use App\Models\customer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CustomerImporter extends Importer
{
    protected static ?string $model = customer::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('fname')
                ->requiredMapping()->label('First Name')
                ->rules(['required', 'max:255']),
            ImportColumn::make('lname')->label('Last Name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('phone')
                ->requiredMapping()->label('Phone Number')
                ->rules(['required', 'max:255']),
            ImportColumn::make('district_name')
                ->rules(['max:255'])->label('District Name'),
            ImportColumn::make('Category Name')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?customer
    {
        // return Customer::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new customer();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customer import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
