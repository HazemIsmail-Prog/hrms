<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EmployeeResource\Pages;
use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\IncrementsRelationManager;
use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\LeavesRelationManager;
use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\SettlementsRelationManager;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\AssignOp\Mod;

class EmployeeResource extends Resource
{
    protected static ?string $model =  Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Employees Management';

    public static function getModelLabel(): string
    {
        return __('Employee');
    }
    public static function getPluralModelLabel(): string
    {
        return __('Employees');
    }

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Auth')
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                        '2xl' => 1,
                    ])
                    ->description(__('Authentication Data'))
                    ->relationship('user')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->translateLabel()
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->translateLabel()
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->label('Password')
                            ->translateLabel()
                            ->password()
                            // ->visibleOn('create')
                            ->required(function (string $operation) {
                                return $operation === 'create';
                            }),
                    ]),

                Section::make('Details')
                    ->columns(2)
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 2,
                        '2xl' => 3,
                    ])
                    ->description(__('Employee Details'))
                    ->schema([
                        Select::make('department_id')
                            ->label('The Department')
                            ->translateLabel()
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('employee_id')
                            ->label('Reporting to')
                            ->translateLabel()
                            ->relationship(
                                'employee',
                                'name',
                                modifyQueryUsing: function (Builder $query,Model $record = null) {
                                    $query->when($record,function($q) use ($record){
                                        $q->where('id', '!=' , $record->id);
                                    });
                                },
                            )
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => $record->user->name)

                            ->searchable()
                            ->preload()
                            ->optionsLimit(5),
                        DatePicker::make('joinDate')
                            ->label('Join Date')
                            ->translateLabel()
                            ->required(),
                        DatePicker::make('lastWorkingDate')
                            ->label('Last Working Date')
                            ->translateLabel()->requiredIf('status', ['Resigned', 'Terminated']),
                        DatePicker::make('residencyExpiryDate')
                            ->label('Residency Expiry Date')
                            ->translateLabel()
                            ->required(),
                        TextInput::make('initialSalary')
                            ->label('Initial Salary')
                            ->translateLabel()
                            ->numeric()
                            ->step(0.001)
                            ->default(0),
                        TextInput::make('initialLeaveTakenBalance')
                            ->label('Initial Leave Taken Balance')
                            ->translateLabel()
                            ->numeric()
                            ->default(0),
                        Radio::make('status')
                            ->label('Status')
                            ->translateLabel()
                            ->options([
                                'Active' => __('Active'),
                                'Resigned' => __('Resigned'),
                                'Terminated' => __('Terminated'),
                            ])
                            ->columnSpanFull()
                            ->required()
                            ->inline()
                            ->default('active'),
                    ]),

                Section::make('Attachments')
                    ->columnSpanFull()
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
            ->columns([
                'sm' => 1,
                'md' => 1,
                'lg' => 1,
                'xl' => 3,
                '2xl' => 4,
            ]);
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
                    ->alignStart()
                    ->label('Name')
                    ->translateLabel()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('joinDate')
                    ->label('Join Date')
                    ->translateLabel()
                    ->date()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('department.name')
                    ->label('The Department')
                    ->translateLabel()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable()
                    ->copyable(),

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
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('salary')
                    ->label('Salary')
                    ->translateLabel()
                    ->money('kwd')
                    ->alignEnd()
                    // ->numeric(
                    //     decimalPlaces: 3,
                    //     decimalSeparator: '.',
                    //     thousandsSeparator: ',',
                    // )
                    ->toggleable(),

                TextColumn::make('leaves_count')
                    ->label('Leaves')
                    ->translateLabel()
                    ->counts('leaves')
                    ->sortable()
                    ->toggleable()
                    ->alignCenter(),

                TextColumn::make('increments_count')
                    ->label('Increaments')
                    ->translateLabel()
                    ->counts('increments')
                    ->toggleable()
                    ->sortable()
                    ->alignCenter(),

                //     ViewColumn::make('attachments')
                //         ->view('tables.columns.attachments-column')
                //         ->toggleable()
                //         ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('The Department')
                    ->translateLabel()
                    ->relationship('department', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->modalWidth('7xl'),
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
