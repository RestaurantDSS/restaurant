<?php

namespace App\Filament\Resources\CategoriaResource\Pages;

use App\Filament\Resources\CategoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoria extends CreateRecord
{
    protected static string $resource = CategoriaResource::class;

    protected function getRedirectUrl(): string  // Asegúrate de cambiar el tipo de retorno a string
    {
        return $this->getResource()::getUrl('index');
    }
}
