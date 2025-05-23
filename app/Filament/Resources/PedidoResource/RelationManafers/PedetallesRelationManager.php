<?php

namespace App\Filament\Resources\PedidoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class PedetallesRelationManager extends RelationManager
{
    protected static string $relationship = 'pedetalles';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_pro')->label('Producto')->relationship('producto', 'nombre_pro')->required(),
                Forms\Components\TextInput::make('cantidad_pdet')->label('Cantidad')->numeric()->required(),
                Forms\Components\TextInput::make('precio_pdet')->label('Precio')->numeric()->required()->prefix('$')->disabled(true),
                Forms\Components\TextInput::make('subtotal_pdet')->label('Subtotal')->numeric()->required()->prefix('$')->disabled(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Productos de pedido')
            ->columns([
                TextColumn::make('producto.nombre_pro')->label('Producto'),
                TextColumn::make('cantidad_pdet')->label('Cantidad'),
                TextColumn::make('precio_pdet')->label('Precio')->prefix('$'),
                TextColumn::make('subtotal_pdet')->label('Subtotal')->prefix('$'),
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
