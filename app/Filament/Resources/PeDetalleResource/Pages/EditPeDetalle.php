<?php

namespace App\Filament\Resources\PeDetalleResource\Pages;

use App\Filament\Resources\PeDetalleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPeDetalle extends EditRecord
{
    protected static string $resource = PeDetalleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
