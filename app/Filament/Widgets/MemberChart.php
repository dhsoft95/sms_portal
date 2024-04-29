<?php

namespace App\Filament\Widgets;

use App\Models\message;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MemberChart extends ChartWidget
{
    protected static ?string $heading = 'Messages sent  Per Monthly';

    protected function getData(): array
    {
        $trans=message::query('id','created_at')->get()->groupBy(function ($trans){
            return Carbon::parse( $trans->created_at)->format('F');
        });
//       dd($trans);
        $quantities=[];
        foreach ($trans as $tran=>$value){
            array_push($quantities,$value->count());
        }
        return [
            'datasets' => [
                [
                    'label' => 'Messages sent  Per Monthly',
                    'data' => $quantities,
                ],
            ],
            'labels' =>  $trans->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
