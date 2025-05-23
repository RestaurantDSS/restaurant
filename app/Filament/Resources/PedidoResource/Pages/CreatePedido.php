<?php

namespace App\Filament\Resources\PedidoResource\Pages;

use App\Filament\Resources\PedidoResource;
use Filament\Actions;
use Filament\Actions\Action;
use App\Models\Cliente;
use App\Models\PeDetalle;
use App\Models\Pedido;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Livewire\Component;


class CreatePedido extends CreateRecord
{
    protected static string $resource = PedidoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Verificar si los datos del cliente están definidos y no están vacíos
        $cedulaCli = $data['cedula_cli'] ?? null;
        $nombreCli = $data['nombre_cli'] ?? '';
        $telefonoCli = $data['telefono_cli'] ?? '';
        $emailCli = $data['email_cli'] ?? '';

        if ($cedulaCli) {
            $cliente = Cliente::firstOrCreate(
                ['cedula_cli' => $cedulaCli],
                ['nombre_cli' => $nombreCli, 'telefono_cli' => $telefonoCli, 'email_cli' => $emailCli]
            );

            $data['id_cli'] = $cliente->id;
        } else {
            throw new \Exception("La cédula del cliente no puede estar vacía");
        }

        // Asegurar que cada detalle de pedido tenga `precio_pdet` y `subtotal_pdet` configurados
        if (isset($data['peDetalles'])) {
            $detallesActualizados = [];

            foreach ($data['peDetalles'] as $detalle) {
                $producto = Producto::find($detalle['id_pro']);
                if ($producto) {
                    $detalleActualizado = [
                        'id_pro' => $detalle['id_pro'],
                        'cantidad_pdet' => $detalle['cantidad_pdet'],
                        'precio_pdet' => $producto->precio_pro,
                        'subtotal_pdet' => $producto->precio_pro * $detalle['cantidad_pdet'],
                    ];

                    Log::info('Detalle actualizado', $detalleActualizado); // Log para depuración

                    $detallesActualizados[] = $detalleActualizado;
                } else {
                    throw new \Exception("Producto no encontrado para el detalle del pedido");
                }
            }

            $data['peDetalles'] = $detallesActualizados;
        }else {
            Log::error('Detalles de pedido no encontrados en los datos', $data); // Log para depuración
        }

        Log::info('Datos antes de crear pedido', $data); // Log para depuración

        return $data;
    }

    /*protected function getActions(): array
    {
        return [
            Action::make('verificarCedula')
                ->label('Verificar Cédula')
                ->form([
                    TextInput::make('cedula_cli')->label('Cédula del Cliente')->required()->maxLength(10),
                ])
                ->action(function (array $data) {
                    Log::info('Verificar cédula action triggered', ['data' => $data]);

                    try {
                        $cliente = Cliente::where('cedula_cli', $data['cedula_cli'])->first();
                        Log::info('Cliente query result', ['cliente' => $cliente]);
                        
                        //$this->emit('clienteVerificado'); //No bloquea pero no muestra nada
                        $this->fillClienteData($cliente);
                        //Livewire::emit('clienteVerificado');
                        $this->dispatch('clienteVerificado');
                        //$this->dispatchBrowserEvent('clienteVerificado'); // Emitir evento para cerrar modal
                        //Livewire::dispatch('clienteVerificado'); // Emitir evento para cerrar modal
                        //$this->emit('clienteVerificado'); //Si bloquea pero muestra datos
                    } catch (\Exception $e) {
                        Log::error('Error verifying cedula', ['exception' => $e->getMessage()]);
                    }
                })
                ->color('primary')
                ->modalButton('Verificar')
                ->modalHeading('Verificar Cédula')
        ];
    }

    protected function fillClienteData($cliente)
    {
        $this->form->fill([
            'cedula_cli' => $cliente ? $cliente->cedula_cli : '',
            'nombre_cli' => $cliente ? $cliente->nombre_cli : '',
            'telefono_cli' => $cliente ? $cliente->telefono_cli : '',
            'email_cli' => $cliente ? $cliente->email_cli : '',
            'is_cliente_found' => (bool) $cliente,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return array_merge($data, ['is_cliente_found' => false]);
    }

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
        'cedula_cli' => '',
        'nombre_cli' => '',
        'telefono_cli' => '',
        'email_cli' => '',
        'total_ped' => 0,
        'modoPago_ped' => 'Efectivo',
        'estado_ped' => 0,
        'fecha_ped' => date('Y-m-d'),
        'is_cliente_found' => false,
        ]);
    }*/
}
