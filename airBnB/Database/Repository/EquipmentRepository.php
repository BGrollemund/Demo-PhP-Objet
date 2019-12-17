<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\Equipment;
use airBnB\System\Database\Repository;

class EquipmentRepository extends Repository
{
    protected function table(): string { return 'equipments'; }

    #region Recherche dans la bdd

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

    public function findByRentingId( int $renting_id ): array
    {
        $equipment_renting = 'equipment_renting';

        $query = sprintf(
            'SELECT main.*
						FROM %s as main 
						JOIN %s as rel ON rel.equipment_id=main.id
					WHERE rel.renting_id=:renting_id
					ORDER BY main.label',
            $this->table(),
            $equipment_renting
        );

        $stmt = $this->read( $query, [ 'renting_id' => $renting_id ] );

        if( is_null( $stmt ) ) { return []; }

        $equipments = [];

        while( $equipment_data = $stmt->fetch() )
        {
            $equipments[] = new Equipment( $equipment_data );
        }

        return $equipments;
    }

    #endregion Recherche dans la bdd


    #region Changement dans la bdd



    #endregion Changement dans la bdd
}