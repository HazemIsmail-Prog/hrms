<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getCachedTabs(): array
    {
        return [
            null => ListRecords\Tab::make('All'),
            'active' => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', 'active')),
            'resigned' => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', 'resigned')),
            'terminated' => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', 'terminated')),
        ];
    }
}
