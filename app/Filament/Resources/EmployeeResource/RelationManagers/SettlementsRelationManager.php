<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SettlementsRelationManager extends RelationManager
{
    protected static string $relationship = 'settlements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')->required(),
                Fieldset::make('Settlement Type')->schema([
                    Radio::make('type')->required()->reactive()->options([
                        'leave' => 'Leave',
                        'indemnity' => 'Indemnity',
                    ])->default('leave')->afterStateUpdated(function (?string $state, Set $set, ?string $old) {
                        $set('days', null);
                        $set('amount', null);
                    }),
                    TextInput::make('days')->numeric()->step(1)->required()->live()->visible(function (Get $get) {
                        return $get('type') === 'leave';
                    }),
                    TextInput::make('amount')->numeric()->step(0.001)->required()->live()->visible(function (Get $get) {
                        return $get('type') === 'indemnity';
                    }),
                ])->columnSpan(1),
                RichEditor::make('notes')
                    ->disableToolbarButtons([
                        'attachFiles',
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
                                ->directory('settlements')
                                ->openable()
                                ->downloadable()
                                ->previewable(true),
                        ])->collapsible()->columns(3)->columnSpanFull(),
                ])
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
        ->deferLoading()
            ->recordAction(null)
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('date'),
                TextColumn::make('type'),
                TextColumn::make('amount_or_days'),
                ViewColumn::make('attachments')->view('tables.columns.attachments-column')

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
