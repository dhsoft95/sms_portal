<?php

namespace App\Filament\Widgets;

use App\Models\message;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SmsChart extends ChartWidget
{
    protected static ?string $heading = 'Successful Messages sent Per Monthly';
    protected static string $color = 'success';
    protected function getData(): array
    {
        $trans = message::query()
            ->select('id', 'created_at')
            ->where('status', true) // Filter messages where status is true (successful)
            ->get()
            ->groupBy(function ($trans) {
                // Group the data by month using Carbon to parse the created_at date
                return Carbon::parse($trans->created_at)->format('F');
            });
        $quantities=[];
        foreach ($trans as $tran=>$value){
            array_push($quantities,$value->count());
        }
        return [
            'datasets' => [
                [
                    'label' => 'Successful Messages sent Per Monthly',
                    'data' => $quantities,
                ],
            ],
            'labels' =>  $trans->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
