<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    // protected function handleRecordCreation(array $data): Model
    // {
    //     dd($data);
    //     $user = User::create($data['user']);
        
    //     $data = Arr::except($data, ['user']);
    //     $data['user_id'] = $user->id;


    //     $record = new ($this->getModel())($data);

    //     if ($tenant = Filament::getTenant()) {
    //         return $this->associateRecordWithTenant($record, $tenant);
    //     }

    //     $record->save();

    //     return $record;

    // }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
