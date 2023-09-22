<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class EmployeeCustomPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Employees Calculations';

    protected static ?string $title = 'Employees Calculations';

    protected static ?string $navigationGroup = 'Reports';


    protected static string $view = 'filament.pages.employee-custom-page';

}
