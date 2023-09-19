<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers\IncrementsRelationManager;
use App\Filament\Resources\EmployeeResource\RelationManagers\LeavesRelationManager;
use App\Filament\Resources\EmployeeResource\RelationManagers\SettlementsRelationManager;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                            ->translateLabel('Name')
                            ->required(),
                        TextInput::make('email')
                            ->translateLabel('Email')
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->translateLabel('Password')
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
                            ->translateLabel('The Department')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('joinDate')
                            ->translateLabel('Join Date')
                            ->required(),
                        DatePicker::make('lastWorkingDate')
                            ->translateLabel('Last Working Date'),
                        DatePicker::make('residencyExpiryDate')
                            ->translateLabel('Residency Expiry Date')
                            ->required(),
                        TextInput::make('initialSalary')
                            ->translateLabel('Initial Salary')
                            ->numeric()
                            ->step(0.001)
                            ->default(0),
                        TextInput::make('initialLeaveTakenBalance')
                            ->translateLabel('Initial Leave Taken Balance')
                            ->numeric()
                            ->default(0),
                        Radio::make('status')
                            ->translateLabel('Status')
                            ->options([
                                'active' => __('Active'),
                                'resigned' => __('Resigned'),
                                'terminated' => __('Terminated'),
                            ])
                            ->required()
                            ->inline()
                            ->default('active'),
                    ]),
                Section::make('Attachments')
                    ->collapsed()
                    ->schema([
                        Repeater::make('attachments')
                            ->defaultItems(0)
                            ->addActionLabel('Add More')
                            ->label('')
                            ->relationship()
                            ->schema([
                                TextInput::make('notes')
                                    ->required(),
                                DatePicker::make('expiration_date'),
                                FileUpload::make('file')
                                    ->required()
                                    ->panelAspectRatio(.15)
                                    ->directory('emp_attachments')
                                    ->openable()
                                    ->downloadable()
                                    ->previewable(true),
                            ])
                            ->collapsible()
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->paginated(false)
            // ->defaultGroup('status')
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->alignStart()
                    ->translateLabel('Name')
                    ->toggleable(),
                TextColumn::make('joinDate')
                    ->translateLabel('Join Date')
                    ->date('d-m-Y')
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('department.name')
                    ->translateLabel('The Department')
                    ->alignCenter()
                    ->toggleable()
                    ->copyable(),
                TextColumn::make('status')
                    ->translateLabel('Status')
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('salary')
                    ->translateLabel('Salary')
                    ->alignEnd()
                    ->numeric(
                        decimalPlaces: 3,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->toggleable(),
                TextColumn::make('leaves_count')
                    ->translateLabel('Leaves')
                    ->counts('leaves')
                    ->toggleable()
                    ->alignCenter(),
                TextColumn::make('increments_count')
                    ->counts('increments')
                    ->toggleable()
                    ->alignCenter(),
                ViewColumn::make('attachments')
                    ->view('tables.columns.attachments-column')
                    ->toggleable()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->relationship('department', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                            ->warning()
                            ->send();
                    }),
            ])
            ->groups([
                Tables\Grouping\Group::make('status')
                    ->label('Status')
                    ->collapsible(),
                Tables\Grouping\Group::make('joinDate')
                    ->label('Join Date')
                    ->collapsible(),
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
                SettlementsRelationManager::class,
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
