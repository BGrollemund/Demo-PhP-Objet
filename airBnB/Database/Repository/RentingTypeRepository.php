<?php


namespace airBnB\Database\Repository;

use airBnB\Database\Model\RentingType;
use airBnB\System\Database\Repository;

class RentingTypeRepository extends Repository
{
    protected function table(): string { return 'renting_types'; }

    #region Recherche dans la bdd

    public function findAll(): array
    {
        $query = 'SELECT * FROM '.$this->table();

        $stmt = $this->read( $query );

        if( is_null( $stmt ) )
            return [];

        $renting_types = [];

        while( $renting_type = $stmt->fetch() )
        {
            $renting_types[] = new RentingType( $renting_type );
        }

        return $renting_types;
    }

    public function findLabelById( int $id ): string
    {
        $query = 'SELECT * FROM '.$this->table().' WHERE id=:id ';

        $stmt = $this->read( $query, [ 'id' => $id ] );

        if( is_null( $stmt ) ) return '';

        $data = $stmt->fetch();

        return $data ? $data[ 'label' ] : '';
    }

    #endregion Recherche dans la bdd


    #region Changement dans la bdd



    #endrregion Changement dans la bdd


}