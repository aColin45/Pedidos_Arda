<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class InventarioImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Solo necesitamos esta clase para convertir el Excel a Colección
    }
}