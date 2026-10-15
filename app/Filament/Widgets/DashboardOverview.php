<?php

namespace App\Filament\Widgets;

use App\Models\Akun;
use App\Models\Kavling;
use App\Models\Leads;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $pending = Leads::where('status_keseriusan', 'pending')->count();
        $unit = Kavling::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            Stat::make('Leads Menunggu Validasi', $pending)
                ->description('Dari form minat website')
                ->descriptionIcon(Heroicon::OutlinedInboxArrowDown)
                ->color($pending > 0 ? 'warning' : 'success'),
            Stat::make('Total Leads', Leads::count())
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->description(Akun::where('role', 'marketing')->count().' marketing aktif')
                ->color('primary'),
            Stat::make('Unit Tersedia', (int) ($unit['tersedia'] ?? 0))
                ->description('Dari '.(int) $unit->sum().' kavling')
                ->descriptionIcon(Heroicon::OutlinedMap)
                ->color('gray'),
            Stat::make('Booking / Terjual', (int) ($unit['booking'] ?? 0).' / '.(int) ($unit['terjual'] ?? 0))
                ->descriptionIcon(Heroicon::OutlinedKey)
                ->description('Unit booking dan terjual')
                ->color('success'),
        ];
    }
}
