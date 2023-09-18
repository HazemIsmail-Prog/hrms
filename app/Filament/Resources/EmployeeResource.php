<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers\IncrementsRelationManager;
use App\Filament\Resources\EmployeeResource\RelationManagers\LeavesRelationManager;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeResource extends Resource
{
    protected static ?string $model =  Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return __('Employee');
    }
    public static function getPluralModelLabel(): string
    {
        return __('Employees');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Auth')
                    ->columnSpan(1)
                    ->description(__('Authentication Data'))
                    ->relationship('user')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required(),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->label(__('Password'))
                            ->password()
                            ->visibleOn('create')
                            ->required(function (string $operation) {
                                return $operation === 'create';
                            }),

                    ]),

                Section::make('Details')
                    ->columns(2)
                    ->columnSpan(3)
                    ->description(__('Employee Details'))
                    ->schema([
                        Select::make('department_id')
                            ->label(__('The Department'))
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('joinDate')
                            ->label(__('Join Date'))
                            ->required(),
                        DatePicker::make('lastWorkingDate')
                            ->label(__('Last Working Date')),
                        DatePicker::make('residencyExpiryDate')
                            ->label(__('Residency Expiry Date'))
                            ->required(),

                        TextInput::make('initialSalary')
                            ->label(__('Initial Salary'))
                            ->numeric()
                            ->step(0.001)
                            ->default(0),
                        TextInput::make('initialLeaveTakenBalance')
                            ->label(__('Initial Leave Taken Balance'))
                            ->numeric()
                            ->default(0),
                        Radio::make('status')
                            ->label(__('Status'))
                            ->options([
                                'active' => __('Active'),
                                'resigned' => __('Resigned'),
                                'terminated' => __('Terminated'),
                            ])
                            ->required()
                            ->inline()
                            ->default('active'),
                    ]),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('Name'))->toggleable(),
                TextColumn::make('joinDate')
                    ->label(__('Join Date'))->toggleable(),
                TextColumn::make('department.name')
                    ->label(__('The Department'))->toggleable(),
                TextColumn::make('status')
                    ->label(__('Status'))->toggleable(),
                TextColumn::make('salary')
                    ->label(__('Salary'))->toggleable(),
                TextColumn::make('leaves_count')->counts('leaves')->toggleable(),
                TextColumn::make('increments_count')->counts('increments')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->relationship('department', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Details', [
                LeavesRelationManager::class,
                IncrementsRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
