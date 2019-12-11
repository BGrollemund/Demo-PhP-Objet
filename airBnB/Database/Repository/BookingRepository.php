<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\Booking;
use airBnB\System\Database\Repository;

class BookingRepository extends Repository
{
    protected function table(): string { return 'bookings'; }

    public function findByRenterId( int $renter_id ): array
    {
        $query = 'SELECT bookings.*, rentings.* FROM ' . $this->table() .
            ' JOIN rentings ON rentings.id=bookings.renting_id'.
            ' WHERE rentings.renter_id=:renter_id ORDER BY bookings.id DESC';

        $stmt = $this->read( $query, [ 'renter_id' => $renter_id ]);

        if( is_null( $stmt ) ) return [];

        $bookings = [];

        while( $booking = $stmt->fetch() )
        {
            $bookings[] = $booking;
        }

        return $bookings;
    }

    public function findByUserId( int $user_id ): array
    {
        $query = 'SELECT bookings.*, rentings.* FROM ' . $this->table() .
            ' JOIN rentings ON rentings.id=bookings.renting_id'.
            ' WHERE bookings.user_id=:user_id ORDER BY bookings.id DESC';

        $stmt = $this->read( $query, [ 'user_id' => $user_id ]);

        if( is_null( $stmt ) ) return [];

        $bookings = [];

        while( $booking = $stmt->fetch() )
        {
            $bookings[] = $booking;
        }

        return $bookings;
    }

    public function insert( Booking $booking ): int
    {
        $explode_start_date = explode('/', $booking->start_date );
        $reverse_start_date = array_reverse( $explode_start_date );
        $new_start_date = implode('/', $reverse_start_date );

        $explode_end_date = explode('/', $booking->end_date );
        $reverse_end_date = array_reverse( $explode_end_date );
        $new_end_date = implode('/', $reverse_end_date );

        $query = 'INSERT INTO '.$this->table().' SET user_id=:user_id, renting_id=:renting_id, start_date=:start_date, end_date=:end_date';

        $id = $this->create( $query, [
            'user_id' => (int) $booking->user_id,
            'renting_id' => (int) $booking->renting_id,
            'start_date' => $new_start_date,
            'end_date' => $new_end_date
        ]);

        return $id;
    }
}