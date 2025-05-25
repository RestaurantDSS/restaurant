<?php

namespace App\Filament\Resources\PeDetalleResource\Pages;

use App\Filament\Resources\PeDetalleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeDetalles extends ListRecords
{
    protected static string $resource = PeDetalleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
