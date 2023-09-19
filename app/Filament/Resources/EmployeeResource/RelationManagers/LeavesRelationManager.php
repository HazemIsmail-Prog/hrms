<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeavesRelationManager extends RelationManager
{
    protected static string $relationship = 'leaves';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required(),
                Radio::make('type')->required()->inline()->options([
                    'unpaid' => 'Unpaid',
                    'paid' => 'Paid',
                    'sick_leave' => 'Sick Leave',
                ])->default('paid'),
                Radio::make('status')->required()->inline()->options([
                    'approved' => 'Approved',
                    'pending' => 'Pending',
                    'rejected' => 'Rejected',
                ])->default('approved'),

                MarkdownEditor::make('notes')->toolbarButtons([
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
                ]),
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
            ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
        ->deferLoading()
            ->recordAction(null)
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('start_date')->dateTime('d-m-Y'),
                TextColumn::make('end_date')->dateTime('d-m-Y'),
                TextColumn::make('type'),
                TextColumn::make('status'),
                TextColumn::make('notes')->markdown(),
                TextColumn::make('leave_days'),
                ViewColumn::make('attachments')->view('tables.columns.attachments-column'),
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
