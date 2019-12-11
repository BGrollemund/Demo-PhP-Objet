<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\Equipment;
use airBnB\System\Database\Repository;

class EquipmentRepository extends Repository
{
    protected function table(): string { return 'equipments'; }

    public function findAll(): array
    {
        $query = 'SELECT * FROM '.$this->table();

        $stmt = $this->read( $query );

        if( is_null( $stmt ) )
            return [];

        $equipments = [];

        while( $equipment = $stmt->fetch() )
        {
            $equipments[] = new Equipment( $equipment );
        }

        return $equipments;
    }
}