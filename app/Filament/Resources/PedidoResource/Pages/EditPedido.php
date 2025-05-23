<?php

namespace App\Filament\Resources\PedidoResource\Pages;

use App\Filament\Resources\PedidoResource;
use Filament\Actions;
use Filament\Actions\Action;
use App\Models\Cliente;
use App\Models\PeDetalle;
use App\Models\Pedido;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Log;

class EditPedido extends EditRecord
{
    protected static string $resource = PedidoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

        Log::info('Datos antes de actualizar pedido', $data); // Log para depuración

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Obtener el cliente asociado al pedido y agregar los datos del cliente al formulario
        $cliente = Cliente::find($data['id_cli']);
        if ($cliente) {
            $data['cedula_cli'] = $cliente->cedula_cli;
            $data['nombre_cli'] = $cliente->nombre_cli;
            $data['telefono_cli'] = $cliente->telefono_cli;
            $data['email_cli'] = $cliente->email_cli;
        }

        // Obtener los detalles del pedido y agregarlos al formulario
        $detalles = PeDetalle::where('id_ped', $data['id'])->get()->toArray();
        if ($detalles) {
            $data['peDetalles'] = $detalles;
        } else {
            Log::error('Detalles de pedido no encontrados en la base de datos', $data); // Log para depuración
        }

        Log::info('Datos antes de rellenar el formulario de edición', $data); // Log para depuración

        return $data;
    }
}
