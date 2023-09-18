<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IncrementsRelationManager extends RelationManager
{
    protected static string $relationship = 'increments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('apply_date')->required(),
                TextInput::make('amount')->numeric()->required(),
                Radio::make('type')->required()->inline()->options([
                    'allowance' => 'Allowance',
                    'basic' => 'Basic',
                ])->default('allowance'),
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

            ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('apply_date')->dateTime('d-m-Y'),
                TextColumn::make('amount'),
                TextColumn::make('type'),
                TextColumn::make('notes')->markdown(),

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
