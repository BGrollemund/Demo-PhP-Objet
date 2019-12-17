<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\Booking;
use airBnB\System\Database\Repository;

class BookingRepository extends Repository
{
    protected function table(): string { return 'bookings'; }

    #region Recherche dans la bdd

    public function findByRenterId( int $renter_id ): array
    {
        $query = 'SELECT bookings.*, rentings.* FROM ' . $this->table() .
            ' JOIN rentings ON rentings.id=bookings.renting_id'.
            ' WHERE rentings.renter_id=:renter_id ORDER BY bookings.start_date ASC';

        $stmt = $this->read( $query, [ 'renter_id' => $renter_id ]);

        if( is_null( $stmt ) ) return [];

        $bookings = [];

        while( $booking = $stmt->fetch() )
        {
            $bookings[] = $booking;
        }

        return $bookings;
    }

    public function findByRentingId( int $renting_id ): array
    {
        $query = 'SELECT * FROM ' . $this->table() .
            ' WHERE renting_id=:renting_id ORDER BY start_date ASC';

        $stmt = $this->read( $query, [ 'renting_id' => $renting_id ] );

        if( is_null( $stmt ) ) return [];

        $unavailabilities = [];

        while( $unavailability = $stmt->fetch() )
        {
            $unavailabilities[] = new Booking( $unavailability );
        }

        return $unavailabilities;
    }

    public function findByUserId( int $user_id ): array
    {
        $query = 'SELECT bookings.*, rentings.* FROM ' . $this->table() .
            ' JOIN rentings ON rentings.id=bookings.renting_id'.
            ' WHERE bookings.user_id=:user_id ORDER BY bookings.start_date ASC';

        $stmt = $this->read( $query, [ 'user_id' => $user_id ]);

        if( is_null( $stmt ) ) return [];

        $bookings = [];

        while( $booking = $stmt->fetch() )
        {
            $bookings[] = $booking;
        }

        return $bookings;
    }

    #endregion Recherche dans la bdd


    #region Changement dans la bdd

    public function insert( Booking $booking ): int
    {
        $query = 'INSERT INTO '.$this->table().' SET user_id=:user_id, renting_id=:renting_id, start_date=:start_date, end_date=:end_date';

        $id = $this->create( $query, [
            'user_id' => (int) $booking->user_id,
            'renting_id' => (int) $booking->renting_id,
            'start_date' => $booking->start_date,
            'end_date' => $booking->end_date
        ]);

        return $id;
    }

    #endregion Changement dans la bdd
}