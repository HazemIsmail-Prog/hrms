<?php

namespace App\Filament\Pages;

use App\Models\Leave;
use Filament\Forms\Components\Radio;
use Filament\Pages\Page;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequests extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.leave-requests';

    protected static ?string $navigationGroup = 'Employees Management';

    public static function getNavigationBadge(): ?string
    {
        return Leave::where('status', 'pending')->count() > 0 ?? null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }


    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->query(Leave::query())
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([

                TextColumn::make('employee.user.name')->label('Name')->translateLabel(),
                TextColumn::make('employee.department.name')->label('The Department')->translateLabel(),
                TextColumn::make('start_date')->date(),
                TextColumn::make('end_date')->date(),
                TextColumn::make('leave_days'),
                TextColumn::make('type')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        'sick_leave' => 'warning',
                    }),
                TextColumn::make('notes')->markdown(),
                ViewColumn::make('attachments')->view('tables.columns.attachments-column'),
                SelectColumn::make('status')
                    ->options([
                        'approved' => 'Approved',
                        'pending' => 'Pending',
                        'rejected' => 'Rejected',
                    ])
                    ->selectablePlaceholder(false),

            ])
            ->filters([
                Filter::make('status')->form([
                    Radio::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->inline()
                        ->columnSpanFull()
                        ->default('pending')
                ])
                    ->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->where('status', $data);
                    })
            ], layout: FiltersLayout::AboveContent);
    }
}
