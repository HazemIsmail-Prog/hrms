<?php

namespace App\Filament\User\Resources\LeaveResource\Pages;

use App\Filament\User\Resources\LeaveResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateLeave extends CreateRecord
{
    protected static string $resource = LeaveResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $data['employee_id'] = auth()->user()->employee_id;
        $data['status'] = 'pending';
        // dd($data);
        $record = new ($this->getModel())($data);

        if ($tenant = Filament::getTenant()) {
            return $this->associateRecordWithTenant($record, $tenant);
        }

        $record->save();

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
