<?php


namespace airBnB\Database\Repository;

use airBnB\Database\Model\RentingType;
use airBnB\System\Database\Repository;

class RentingTypeRepository extends Repository
{
    protected function table(): string { return 'renting_types'; }

    public function getLabelById( int $id ): string
    {
        $query = 'SELECT * FROM '.$this->table().' WHERE id=:id ';

        $stmt = $this->read( $query, [ 'id' => $id ] );

        if( is_null( $stmt ) ) return '';

        $data = $stmt->fetch();

        return $data ? $data[ 'label' ] : '';
    }

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
}