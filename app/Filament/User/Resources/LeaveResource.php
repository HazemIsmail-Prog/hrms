<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\LeaveResource\Pages;
use App\Models\Leave;
use App\Rules\OverlappingLeavePeriods;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        $query = static::getModel()::query()->where('employee_id', auth()->user()->employee_id);

        return $query;
    }

    public static function canEdit(Model $record): bool
    {
        return $record->status === 'pending';
    }

    public static function canDelete(Model $record): bool
    {
        return $record->status === 'pending';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start_date')
                    ->required()->rule(new OverlappingLeavePeriods()),
                DatePicker::make('end_date')
                    ->required()->rule(new OverlappingLeavePeriods()),
                Radio::make('type')->required()->inline()->options([
                    'paid' => 'Paid',
                    'unpaid' => 'Unpaid',
                    'sick_leave' => 'Sick Leave',
                ])->default('paid'),

                MarkdownEditor::make('notes')
                    ->toolbarButtons([
                        // 'attachFiles',
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'heading',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        // 'table',
                        'undo',
                    ])
                    ->columnSpanFull(),
                Section::make('Attachments')->collapsed()->schema([
                    Repeater::make('attachments')
                        ->defaultItems(0)
                        ->addActionLabel('Add More')
                        ->label('')
                        ->relationship()
                        ->schema([
                            TextInput::make('notes')->required(),
                            DatePicker::make('expiration_date'),
                            FileUpload::make('file')
                                ->required()
                                ->panelAspectRatio(.15)
                                ->directory('leaves')
                                ->openable()
                                ->downloadable()
                                ->previewable(true),
                        ])->collapsible()->columns(3)->columnSpanFull(),
                ])
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->deferLoading()
            ->recordUrl(null)
            ->recordAction(null)
            ->recordTitleAttribute('name')
            ->defaultSort('start_date', 'desc')
            ->columns([
                TextColumn::make('start_date')->date()->sortable()->alignCenter(),
                TextColumn::make('end_date')->date()->sortable()->alignCenter(),
                TextColumn::make('leave_days')->alignCenter(),
                TextColumn::make('type')->sortable()->alignCenter(),
                TextColumn::make('status')->sortable()->alignCenter(),
                TextColumn::make('notes')->markdown(),
                ViewColumn::make('attachments')->view('tables.columns.attachments-column')->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }
}
