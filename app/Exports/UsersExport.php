<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = collect($data);
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nom',
            'Email',
            'Téléphone',
            'Adresse',
            'Date de naissance',
            'Statut',
            'Rôles',
            'Date de création',
            'Dernière connexion',
            'Archivé le'
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row['ID'],
            $row['Nom'],
            $row['Email'],
            $row['Téléphone'],
            $row['Adresse'],
            $row['Date de naissance'],
            $row['Statut'],
            $row['Rôles'],
            $row['Date de création'],
            $row['Dernière connexion'],
            $row['Archivé le'],
        ];
    }
}
