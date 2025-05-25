<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductoResource\RelationManagers;
use App\Models\Producto;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Menú';
    protected static ?int $sort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de producto')
                    ->description('Ingrese los datos del nuevo producto del menú.')
                        ->schema([
                            TextInput::make('nombre_pro')->label('Nombre')->required()->maxLength(60),
                            TextInput::make('descripcion_pro')->label('Descripción de producto')->required()->maxLength(150),
                            TextInput::make('precio_pro')->label('Precio unitario')->numeric()->required()->prefix('$'),
                            Select::make('disponibilidad_pro')
                                ->label('Disponibilidad del producto')
                                ->options([
                                    1 => 'Disponible',
                                    0 => 'No disponible',
                                ])
                                ->required(),
                            Select::make('id_categoria')->label('Categoria')->relationship('categoria', 'nombre_cat')->required(),
                            FileUpload::make('imagenRef_pro')->label('Imagen del Producto')->required()
                            ->disk('producto') // Especifica el disco (antes: public)
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->uploadingMessage('Cargando imagen...')
                            ->directory('Producto') // Deja vacío para usar el root del disco (antes 'imagenes/Producto')
                            ->maxSize(2048) // tamaño máximo del archivo en KB
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file): string => (string) str($file->getClientOriginalName())
                                    ->prepend('prod-')
                            ),
                        ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_pro')->label('Nombre')->searchable(),
                TextColumn::make('descripcion_pro')->label('Descripción')->searchable(),
                TextColumn::make('precio_pro')->label('Precio')->numeric(),
                TextColumn::make('disponibilidad_pro')->label('Disponibilidad')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Disponible' : 'No disponible')
                    ->sortable(),
                ImageColumn::make('imagenRef_pro')->label('Imagen')->disk('producto')->width(50)->height(50)->circular() //(antes: producto)
                    ->url(fn($record) => asset('storage/imagenes/Producto/' . $record->imagenRef_pro)),
                TextColumn::make('categoria.nombre_cat')->label('Categoria')->sortable(),
                TextColumn::make('created_at')->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('secondary'),
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->color('primary'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->label('Eliminar seleccionados')
                    ->color('danger'),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Datos de producto')
                    ->schema([
                        TextEntry::make('nombre_pro')->label('Nombre'),
                        TextEntry::make('descripcion_pro')->label('Descripción'),
                        TextEntry::make('precio_pro')->label('Precio unitario'),
                        TextEntry::make('disponibilidad_pro')->label('Disponibilidad'),
                        TextEntry::make('categoria.nombre_cat')->label('Categoría'),
                        ImageEntry::make('imagenRef_pro')->label('Imagen del Producto')->width(150)->height(150)
                            ->default(fn ($record) => asset('storage/imagenes/Producto/' . $record->imagenRef_pro)), // Genera la URL correcta
                    ])
                    ->columns(3),
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}
