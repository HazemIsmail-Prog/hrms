<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;





class ListEmployees extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $toDate;

    public function table(Table $table): Table
    {
        return $table
            ->query(Employee::query())
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->translateLabel()
                    ->sortable(),

                TextColumn::make('department.name')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->translateLabel()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Resigned' => 'warning',
                        'Terminated' => 'danger',
                    })
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('joinDate')
                    ->date()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('salary')
                    ->alignEnd()
                    ->numeric(decimalPlaces: 3, thousandsSeparator: ',')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('net_working_days')
                    ->alignCenter()
                    ->state(function (Model $record) {
                        return $record->getNetWorkingDaysAttribute($this->toDate);
                    })
                    ->numeric(thousandsSeparator: ',')
                    ->toggleable(),
                    
                    TextColumn::make('indemnity')
                    ->alignEnd()
                    ->money('kwd')
                    // ->numeric(decimalPlaces: 3, thousandsSeparator: ',')
                    ->state(function (Model $record) {
                        return $record->getIndemnityAttribute($this->toDate);
                    })
                    ->toggleable(),

                TextColumn::make('leave_balance_days')
                    ->state(function (Model $record) {
                        return $record->getLeaveBalanceDaysAttribute($this->toDate);
                    })
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->toggleable()
                    ->alignCenter(),

                TextColumn::make('leave_balance_amount')
                    ->alignEnd()
                    ->money('kwd')
                    // ->suffix(' KWD')
                    // ->prefix('KWD')
                    ->state(function (Model $record) {
                        return $record->getLeaveBalanceAmountAttribute($this->toDate);
                    })
                    // ->numeric(decimalPlaces: 3, thousandsSeparator: ',')
                    ->toggleable(),
                    ])
                    ->filters([
                Filter::make('toDate')->form([
                    DatePicker::make('toDate')
                        // ->default(now())
                        ->helperText('Leave this blank will get results till today')
                ])

                    ->query(function (array $data) {
                        $this->toDate = $data['toDate'];
                    }),
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'active' => 'Active',
                        'resigned' => 'Resigned',
                        'terminated' => 'Terminated',
                    ]),
                SelectFilter::make('Department')
                    ->relationship('department', 'name')
                    ->preload()
                    ->multiple(),
            ], layout: FiltersLayout::AboveContent)->persistFiltersInSession()

            ->groupedBulkActions([
                ExportBulkAction::make()->exports([
                    ExcelExport::make()->fromTable()->ignoreFormatting()
                ])
            ]);
    }


    public function render(): View
    {
        return view('livewire.employees.list-employees');
    }
}
