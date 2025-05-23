<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoResource\Pages;
use App\Filament\Resources\PedidoResource\RelationManagers;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\PeDetalle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists;
use Filament\Infolists\Infolist;

use Closure;
use Filament\Infolists\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Facades\Log;

class PedidoResource extends Resource
{
    protected static ?string $model = Pedido::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Carrito';
    protected static ?int $sort = 3;

    public static function form(Form $form): Form
    {
        $productos = Producto::get();

        return $form
            ->schema([
                Forms\Components\Section::make('Datos de cliente')
                    ->description('Ingrese la cédula del cliente y seleccione los productos. Si ya ha realizado una compra, se reflejarán automáticamente los datos del cliente. En caso de ser un nuevo cliente, los campos se activarán para el ingreso de datos.')
                    ->schema([
                        TextInput::make('cedula_cli')->label('Número de Cédula')->required()->maxLength(10)->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                if ($cliente = \App\Models\Cliente::where('cedula_cli', $state)->first()) {
                                    $set('nombre_cli', $cliente->nombre_cli);
                                    $set('telefono_cli', $cliente->telefono_cli);
                                    $set('email_cli', $cliente->email_cli);
                                    $set('is_cliente_found', true);
                                } else {
                                    $set('nombre_cli', '');
                                    $set('telefono_cli', '');
                                    $set('email_cli', '');
                                    $set('is_cliente_found', false);
                                }
                                self::updateTotal($get, $set);
                            }),
                        TextInput::make('nombre_cli')->label('Nombres y Apellidos')->maxLength(70)->live(onBlur: true)->disabled(fn ($get) => $get('is_cliente_found'))->required()->hidden(fn ($get) => !$get('cedula_cli'))
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                self::updateTotal($get, $set);
                            }),
                        TextInput::make('telefono_cli')->label('Número de Teléfono')->maxLength(10)->live(onBlur: true)->disabled(fn ($get) => $get('is_cliente_found'))->required()->hidden(fn ($get) => !$get('cedula_cli'))
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                self::updateTotal($get, $set);
                            }),
                        TextInput::make('email_cli')->label('Dirección E-mail')->maxLength(60)->live(onBlur: true)->disabled(fn ($get) => $get('is_cliente_found'))->hidden(fn ($get) => !$get('cedula_cli'))
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                self::updateTotal($get, $set);
                            }),
                        TextInput::make('is_cliente_found')->hidden(),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Datos de pedido')
                    ->description('Ingrese los datos del pedido.')
                    ->schema([
                        DatePicker::make('fecha_ped')->label('Fecha de Pedido')->required()->placeholder('YYYY-MM-DD')->live(onBlur: true)
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                self::updateTotal($get, $set);
                            }),
                        Select::make('modoPago_ped')->label('Modo de Pago')->required()->live(debounce: 500)
                            ->options([
                                'Efectivo' => 'Efectivo',
                                'Tarjeta' => 'Tarjeta',
                                'Transferencia' => 'Transferencia',
                            ])
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                self::updateTotal($get, $set);
                            }),
                        Select::make('estado_ped')->label('Estado de Pedido')->required()->live(debounce: 500)
                            ->options([
                                1 => 'Pagado',
                                0 => 'Pendiente',
                            ])
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                self::updateTotal($get, $set);
                            }),
                        TextInput::make('total_ped')->label('Total')->numeric()->prefix('$')->required()->readOnly()->live(debounce: 500)
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                self::updateTotal($get, $set);
                            }),
                    ])
                    ->columns(4),

                // Los detalles de pedido (Repeater)
                Forms\Components\Section::make('Productos de Pedido')
                    ->description('Ingrese los productos dentro de este pedido')
                    ->schema([
                        Forms\Components\Repeater::make('peDetalles')
                            ->label('')
                            ->relationship()
                            ->schema([
                                //Select::make('id_categoria')->label('Categoria')->required()->live(debounce: 400)->relationship('categoria', 'nombre_cat'),
                                // Campo para seleccionar la categoría
                                Select::make('id_categoria')->label('Categoría')->required()->reactive()
                                    ->options(Categoria::all()->pluck('nombre_cat', 'id'))
                                    //->afterStateUpdated(fn (callable $set) => $set('productos', [])),
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        // Limpiar los campos de producto, cantidad, precio unitario y subtotal
                                        $set('id_pro', null);
                                        $set('cantidad_pdet', 0);
                                        $set('precio_pdet', 0);
                                        $set('subtotal_pdet', 0);
                                
                                        // Borrar toda la lista de productos en caso de que haya más de uno
                                        $set('productos', []);
                                        
                                        // Actualizar el total del pedido
                                        self::updateTotal($get, $set);
                                    }),

                                Select::make('id_pro')->label('Producto')->required()->live(debounce: 400)
                                    //->relationship('producto', 'nombre_pro')
                                    ->options(function (callable $get) {
                                        $categoriaId = $get('id_categoria');
                                        if ($categoriaId) {
                                            return Producto::where('id_categoria', $categoriaId)->pluck('nombre_pro', 'id');
                                        }
                                        return [];
                                    })
                                    ->disableOptionWhen(function ($value, $state, callable $get) {
                                        return collect($get('../*.id_pro'))
                                            ->reject(fn ($id) => $id == $state)
                                            ->filter()
                                            ->contains($value);
                                    })
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $producto = Producto::find($state);
                                        if ($producto) {
                                            $set('precio_pdet', $producto->precio_pro);
                                            $set('subtotal_pdet', $get('cantidad_pdet') * $producto->precio_pro);
                                            //$set('subtotal_pdet', $producto->precio_pro * $get('cantidad_pdet'));
                                            //$set('subtotal_pdet', $get('cantidad_pdet') * $get('precio_pdet'));
                                        }
                                        self::updateTotal($get, $set);
                                    }),
                                TextInput::make('cantidad_pdet')->label('Cantidad')->numeric()->required()->live(onBlur: true)->default(0)
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $set('subtotal_pdet', $state * $get('precio_pdet'));
                                        self::updateTotal($get, $set);
                                    }),
                                TextInput::make('precio_pdet')->label('Precio unitario')->numeric()->required()->prefix('$')->readOnly()->default(0),
                                TextInput::make('subtotal_pdet')->label('Subtotal')->numeric()->required()->prefix('$')->readOnly()->live(debounce: 600)->default(0)
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        self::updateTotal($get, $set);
                                    }),
                            ])
                            ->createItemButtonLabel('Añadir Producto')
                            ->columns(5)
                            ->columnSpan('full')
                            ->live()

                            ->afterStateUpdated(function (callable $get, callable $set) {
                                self::updateTotal($get, $set);
                            })

                            ->deleteAction(
                                fn (Action $action) => $action->after(fn (callable $get, callable $set) => self::updateTotal($get, $set)),
                            ),

                    ])
                    ->afterStateUpdated(function (callable $get, callable $set) {
                        self::updateTotal($get, $set);
                    })
                    ->columns(4),
            ]);
    }

    public static function updateTotal(callable $get, callable $set): void
    {
        $total = 0;
        $peDetalles = $get('peDetalles') ?? [];

        foreach ($peDetalles as $index => $detalle) {
            // Verificar si los detalles tienen los campos necesarios
            if (isset($detalle['precio_pdet']) && isset($detalle['cantidad_pdet'])) {
                $subtotal = $detalle['precio_pdet'] * $detalle['cantidad_pdet'];
                $set("peDetalles.{$index}.subtotal_pdet", $subtotal); // Asegurar que el subtotal se actualiza
                $total += $subtotal;
            } else {
                Log::error('Detalle faltante', $detalle); // Log para depuración
            }
        }
        $set('total_ped', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_ped')->label('Fecha de Pedido')->sortable(),
                TextColumn::make('total_ped')->label('Total'),
                TextColumn::make('modoPago_ped')->label('Modo de Pago')->searchable(),
                TextColumn::make('estado_ped')
                    ->label('Estado')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Pagado' : 'Pendiente')
                    ->sortable(),
                TextColumn::make('cliente.nombre_cli')->label('Cliente')->sortable()->searchable(),
                TextColumn::make('created_at')->label('Creación del Pedido')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Actualización del Pedido')->dateTime()->toggleable(isToggledHiddenByDefault: true)
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
                        TextEntry::make('cliente.cedula_cli')->label('Cédula'),
                        TextEntry::make('cliente.nombre_cli')->label('Nombres y Apellidos'),
                        TextEntry::make('cliente.telefono_cli')->label('Número de Teléfono'),
                        TextEntry::make('cliente.email_cli')->label('Dirección E-mail'),
                    ])
                    ->columns(4),

                Section::make('Datos de pedido')
                    ->schema([
                        TextEntry::make('fecha_ped')->label('Fecha de Pedido'),
                        TextEntry::make('modoPago_ped')->label('Modo de Pago'),
                        TextEntry::make('estado_ped')->label('Estado de Pedido'),
                        TextEntry::make('total_ped')->label('Total')->prefix('$'),
                    ])
                    ->columns(4),
                Section::make('Detalles del pedido')
                    ->schema(function ($record) {
                        $detalles = $record->peDetalles;

                        return $detalles->map(function ($detalle, $index) {
                            return Section::make('Producto ' . ($index + 1))
                                ->schema([
                                    TextEntry::make('producto.nombre_pro')->label('Producto')->default($detalle->producto->nombre_pro),
                                    TextEntry::make('cantidad_pdet')->label('Cantidad')->default($detalle->cantidad_pdet),
                                    TextEntry::make('precio_pdet')->label('Precio Unitario')->prefix('$')->default($detalle->precio_pdet),
                                    TextEntry::make('subtotal_pdet')->label('Subtotal')->prefix('$')->default($detalle->subtotal_pdet),
                                ])
                                ->columns(4);
                        })->toArray();
                    })
                    ->columns(4),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //RelationManagers\PedetallesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPedidos::route('/'),
            'create' => Pages\CreatePedido::route('/create'),
            'edit' => Pages\EditPedido::route('/{record}/edit'),
        ];
    }

    // Método para verificar la cédula
    public function verificarCedula(array $data, callable $set)
    {
        try {
            $cliente = Cliente::where('cedula_cli', $data['cedula_cli'])->first();

            if ($cliente) {
                $set('nombre_cli', $cliente->nombre_cli);
                $set('telefono_cli', $cliente->telefono_cli);
                $set('email_cli', $cliente->email_cli);
                $set('is_cliente_found', true);
            } else {
                $set('nombre_cli', '');
                $set('telefono_cli', '');
                $set('email_cli', '');
                $set('is_cliente_found', false);
            }
        } catch (\Exception $e) {
            Log::error('Error verifying cedula', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }
}
