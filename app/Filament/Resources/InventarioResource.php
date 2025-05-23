<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventarioResource\Pages;
use App\Filament\Resources\InventarioResource\RelationManagers;
use App\Models\Inventario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class InventarioResource extends Resource
{
    protected static ?string $model = Inventario::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = null; // Dejarlo fuera de cualquier grupo
    protected static ?int $sort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre_inv')
                    ->label('Nombre del Inventario')
                    ->required()
                    ->maxLength(70),
                Forms\Components\Textarea::make('descripcion_inv')
                    ->label('Descripción')
                    ->nullable(),
                Forms\Components\TextInput::make('cantidad_inv')
                    ->label('Cantidad')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('estado_inv')
                    ->label('Estado')
                    ->required()
                    ->maxLength(60),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_inv')->label('Nombre del Inventario'),
                TextColumn::make('descripcion_inv')->label('Descripción'),
                TextColumn::make('cantidad_inv')->label('Cantidad'),
                TextColumn::make('estado_inv')->label('Estado'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Ver'),
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\DeleteAction::make()->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                    Section::make('Datos del Inventario')
                        ->schema([
                            TextEntry::make('nombre_inv')->label('Nombre'),
                            TextEntry::make('descripcion_inv')->label('Descripción'),
                            TextEntry::make('cantidad_inv')->label('Cantidad'),
                            TextEntry::make('estado_inv')->label('Estado'),
                        ])->columns(4),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarios::route('/'),
            'create' => Pages\CreateInventario::route('/create'),
            'edit' => Pages\EditInventario::route('/{record}/edit'),
        ];
    }
}
