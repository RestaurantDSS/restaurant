<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

use Filament\Notifications\Notification;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Carrito';
    protected static ?int $sort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de cliente')
                    ->description('Ingrese los datos del nuevo cliente')
                    ->schema([
                        TextInput::make('nombre_cli')->label('Nombre del Cliente')->required()->maxLength(70),
                        TextInput::make('cedula_cli')->label('Cédula del Cliente')->required()->maxLength(10)->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Verificar si la cédula ya existe en la base de datos
                                if (Cliente::where('cedula_cli', $state)->exists()) {
                                    // Limpiar el campo de cédula
                                    $set('cedula_cli', '');

                                    // Mostrar notificación de advertencia
                                    Notification::make()
                                        ->title('Cédula Existente')
                                        ->body('Esta cédula ya pertenece a otro cliente registrado en el sistema. Por favor, ingrese otra.')
                                        ->danger()
                                        ->send();
                                }
                            }),
                        TextInput::make('email_cli')->label('E-mail del Cliente')->email()->maxLength(60)->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Verificar si el e-mail ya existe en la base de datos
                                if (Cliente::where('email_cli', $state)->exists()) {
                                    // Limpiar el campo de e-mail
                                    $set('email_cli', '');

                                    // Mostrar notificación de advertencia
                                    Notification::make()
                                        ->title('Dirección de Correo Existente')
                                        ->body('Este correo ya pertenece a otro cliente registrado en el sistema. Por favor, ingrese otro.')
                                        ->danger()
                                        ->send();
                                }
                            }),
                        TextInput::make('telefono_cli')->label('Teléfono del Cliente')->required()->maxLength(10),
                        ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_cli')->label('Nombre')->searchable(),
                TextColumn::make('cedula_cli')->label('Cédula')->searchable(),
                TextColumn::make('email_cli')->label('E-mail'),
                TextColumn::make('telefono_cli')->label('Teléfono'),
                TextColumn::make('created_at')->label('Creado')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Datos de cliente')
                    ->schema([
                        TextEntry::make('cedula_cli')->label('Cédula'),
                        TextEntry::make('nombre_cli')->label('Nombres y Apellidos'),
                        TextEntry::make('telefono_cli')->label('Número de Teléfono'),
                        TextEntry::make('email_cli')->label('Dirección E-mail'),
                    ])
                    ->columns(4),
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
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
