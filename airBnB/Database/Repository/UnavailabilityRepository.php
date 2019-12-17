<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\Unavailability;
use airBnB\System\Database\Repository;

class UnavailabilityRepository extends Repository
{
    protected function table(): string { return 'unavailabilities'; }

    #region Recherche dans la bdd

    public function findByRentingId( int $renting_id ): array
    {
        $query = 'SELECT * FROM ' . $this->table() .
            ' WHERE renting_id=:renting_id ORDER BY start_date ASC';

        $stmt = $this->read( $query, [ 'renting_id' => $renting_id ] );

        if( is_null( $stmt ) ) return [];

        $unavailabilities = [];

        while( $unavailability = $stmt->fetch() )
        {
            $unavailabilities[] = new Unavailability( $unavailability );
        }

        return $unavailabilities;
    }

    #endregion Recherche dans la bdd


    #region Changement dans la bdd

    public function insert( Unavailability $unavailability ): int
    {
        $query = 'INSERT INTO '.$this->table().
            ' SET renting_id=:renting_id, start_date=:start_date, end_date=:end_date';

        $id = $this->create( $query, [
            'renting_id' => (int) $unavailability->renting_id,
            'start_date' => $unavailability->start_date,
            'end_date' => $unavailability->end_date
        ]);

        return $id;
    }

    public function save( Unavailability $unavailability ): int
    {
        $query = 'UPDATE '.$this->table().
            ' SET renting_id=:renting_id, start_date=:start_date, end_date=:end_date, price=:price,'.
            ' WHERE id=:id';

        $id = $this->update( $query, [
            'id' => $unavailability->id,
            'renting_id' => $unavailability->renting_id,
            'start_date' => $unavailability->start_date,
            'end_date' => $unavailability->end_date
        ]);

        return $id;
    }

    #endregion Changement dans la bdd
}