<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeDetalleResource\Pages;
use App\Filament\Resources\PeDetalleResource\RelationManagers;
use App\Models\PeDetalle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

class PeDetalleResource extends Resource
{
    protected static ?string $model = PeDetalle::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('precio_pdet')->required()->numeric()->prefix('$'),
                TextInput::make('cantidad_pdet')->required()->numeric(),
                TextInput::make('subtotal_pdet')->required()->numeric()->prefix('$'),
                Select::make('id_ped')
                    ->relationship('pedido', 'nombre_ped')
                    ->searchable(),
                Select::make('id_pro')
                    ->relationship('producto', 'nombre_pro')
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('precio_pdet')->sortable(),
                TextColumn::make('cantidad_pdet')->sortable(),
                TextColumn::make('subtotal_pdet')->sortable(),
                TextColumn::make('pedido.nombre_ped')->label('Pedido')->sortable()->searchable(),
                TextColumn::make('producto.nombre_pro')->label('Producto')->sortable()->searchable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeDetalles::route('/'),
            'create' => Pages\CreatePeDetalle::route('/create'),
            'edit' => Pages\EditPeDetalle::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
