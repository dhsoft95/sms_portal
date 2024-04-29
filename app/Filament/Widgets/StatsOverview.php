<?php

namespace App\Filament\Widgets;

use App\Models\customer;
use App\Models\message;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $send = message::where('status', '=', '1')->count();
        $failed = message::where('status', '=', '0')->count();
        $all = customer::count();
        return [
            Stat::make('Total successful messages', $send)
                ->description('32k increase')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Total failed messages',   $failed)
                ->description('7% increase')
                ->descriptionIcon('heroicon-m-bolt-slash')->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger'),
            Stat::make('All customes', $all)
                ->description('3% increase')->chart([7, 2, 10, 3, 15, 4, 17])
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
        ];
    }
}
