<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class EmployeesCalculations extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Employees Calculations';

    protected static ?string $title = 'Employees Calculations';

    protected static ?string $navigationGroup = 'Reports';


    protected static string $view = 'filament.pages.employees-calculations-page';

}
