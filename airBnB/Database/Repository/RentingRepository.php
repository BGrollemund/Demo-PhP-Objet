<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\Medium;
use airBnB\Database\Model\Renting;
use airBnB\System\Database\Repository;

class RentingRepository extends Repository
{
    protected function table(): string { return 'rentings'; }

    #region Recherche dans la bdd

    public function findAllFiltered( array $data ): array
    {
        $query = 'SELECT rentings.*';

        if( isset( $data[ 'equipments' ] ) ) {
            $query .= ', COUNT( equipment_renting.renting_id ) AS total';

            $count = count( $data[ 'equipments' ] );

            $data[ 'equipments' ] = implode( ',', $data[ 'equipments' ] );
        }

        $query .= ' FROM '.$this->table();

        // TODO: erreur dans les valeurs d'entrée de $data (voir $_GET du controleur)

        if( isset( $data[ 'equipments' ] ) ) {
            $query .= ' JOIN equipment_renting ON equipment_renting.renting_id=rentings.id';
        }

        if(  isset( $data[ 'sleeping_num' ] ) && $data[ 'sleeping_num' ] !== '' ) {
            switch( $data[ 'sleeping_num' ] ) {
                case '2':
                    $query .= ' WHERE sleeping_num in (1,2)';
                    break;
                case '4':
                    $query .= ' WHERE sleeping_num in (3,4)';
                    break;
                case '6':
                    $query .= ' WHERE sleeping_num in (5,6)';
                    break;
                case '8':
                    $query .= ' WHERE sleeping_num in (6,7)';
                    break;
                case 'more':
                    $query .= ' WHERE sleeping_num >= 8';
                    break;
            }

            if(  isset( $data[ 'renting_type_id' ] ) && $data[ 'renting_type_id' ] !== '' ) {
                $query .= ' AND renting_type_id='.$data[ 'renting_type_id' ];
            }

            if( isset( $data[ 'equipments' ] ) ) {
                $query .= ' AND equipment_renting.equipment_id IN ('.$data[ 'equipments' ].')';
                $query .= ' GROUP BY equipment_renting.renting_id';
            }

        }
        elseif( isset( $data[ 'renting_type_id' ] ) && $data[ 'renting_type_id' ] !== '' ) {
            $query .= ' WHERE renting_type_id='.$data[ 'renting_type_id' ];

            if( isset( $data[ 'equipments' ] ) ) {
                $query .= ' AND equipment_renting.equipment_id IN ('.$data[ 'equipments' ].')';
                $query .= ' GROUP BY equipment_renting.renting_id';
            }
        }
        elseif( isset( $data[ 'equipments' ] ) ) {
            $query .= ' WHERE equipment_renting.equipment_id IN ('.$data[ 'equipments' ].')';
            $query .= ' GROUP BY equipment_renting.renting_id';
        }


        if( isset( $data[ 'order' ] ) && $data[ 'order' ] === 'ASC' ) {
        $query .= ' ORDER BY price ASC';
        }

        if( isset( $data[ 'order' ] ) && $data[ 'order' ] === 'DESC' ) {
            $query .= ' ORDER BY price DESC';
        }

        $stmt = $this->read( $query );

        if( is_null( $stmt ) )
            return [];

        $rentings = [];

        while( $renting = $stmt->fetch() )
        {
            if( isset( $count ) && (int) $renting['total'] !== $count ) {
                continue;
            }

            $rentings[] = new Renting( $renting );
        }

        return $rentings;
    }

    public function findById(int $id ): ?Renting
    {
        $query = 'SELECT * FROM ' . $this->table() . ' WHERE id=:id';

        $stmt = $this->read( $query, [ 'id' => $id ]);

        if( is_null( $stmt ) ) return null;

        $data = $stmt->fetch();

        return $data ? new Renting( $data ) : null;
    }

    public function findByRenterId( int $renter_id ): array
    {
        $query = 'SELECT * FROM ' . $this->table() . ' WHERE renter_id=:renter_id ORDER BY id DESC';

        $stmt = $this->read( $query, [ 'renter_id' => $renter_id ]);

        if( is_null( $stmt ) ) return [];

        $rentings = [];

        while( $renting = $stmt->fetch() )
        {
            $rentings[] = new Renting( $renting );
        }

        return $rentings;
    }

    public function findEquipmentsById( int $id ): array
    {
        $equipment_renting = 'equipment_renting';

        $query = 'SELECT equipments.label FROM '.$equipment_renting.
            ' JOIN equipments ON equipments.id=equipment_renting.equipment_id'.
            ' WHERE equipment_renting.renting_id='.$id;

        $stmt = $this->read( $query );

        if( is_null( $stmt ) )
            return [];

        $equipment_labels = [];

        while( $equipment_label = $stmt->fetch() )
        {
            $equipment_labels[] = $equipment_label[ 'label' ];
        }

        return $equipment_labels;
    }

    public function findFavoriteByUserId( int $user_id ): array
    {
        $query = 'SELECT rentings.* FROM rentings'.
            ' JOIN favorites ON favorites.renting_id=rentings.id'.
            ' WHERE user_id='.$user_id;


        $stmt = $this->read( $query );

        $data = [];

        while( $datum = $stmt->fetch() )
        {
            $data[] = new Renting( $datum );
        }

        return $data;
    }

    public function findMediumById( int $id ): ?Medium
    {
        $medium_renting = 'medium_renting';

        $query = 'SELECT media.* FROM '.$medium_renting.
            ' JOIN media ON media.id='.$medium_renting.'.medium_id'.
            ' WHERE renting_id='.$id;

        $stmt = $this->read( $query );

        $data = $stmt->fetch();

        return $data ? new Medium( $data ) : null;
    }

    public function isFavoriteById( int $user_id, int $renter_id ): bool
    {
        $favorites = 'favorites';

        $query = 'SELECT * FROM '.$favorites.
            ' WHERE user_id='.$user_id.' AND renting_id='.$renter_id;

        $stmt = $this->read( $query );

        $result = $stmt->fetch();

        return $result > 0;
    }


    #endregion Recherche dans la bdd


    #region Changement dans la bdd

    public function bindEquipments( int $renting_id, array $equipment_ids ): bool
    {
        $query_values = [];

        foreach( $equipment_ids as $equipment_id ) {
            $query_values[] = sprintf( '(%s,%s)', $renting_id, $equipment_id );
        }

        $query = sprintf( 'INSERT INTO equipment_renting VALUES %s', implode(',', $query_values ) );

        $id = $this->create( $query );

        return $id > 0;

    }

    public function unbindEquipments( int $renting_id, array $equipment_ids  ): bool
    {
        $query = sprintf( 'DELETE FROM equipment_renting WHERE renting_id=:renting_id AND equipment_id IN (%s)',
            implode(',', $equipment_ids )
        );

        $id = $this->delete( $query, ['renting_id' => $renting_id ] );

        return $id > 0;
    }

    public function bindMedia( int $renting_id, int $medium_id ): bool
    {
        $query = 'INSERT INTO medium_renting VALUES (' . $renting_id . ',' . $medium_id . ')';

        $id = $this->create( $query );

        return $id > 0;
    }

    public function insert( Renting $renting ): int
    {
        $query = 'INSERT INTO '.$this->table().
            ' SET renter_id=:renter_id, city=:city, country=:country, price=:price,'.
            ' renting_type_id=:renting_type_id, area=:area, description=:description, sleeping_num=:sleeping_num';

        $id = $this->create( $query, [
            'renter_id' => $renting->renter_id,
            'city' => $renting->city,
            'country' => $renting->country,
            'price' => (int) $renting->price,
            'renting_type_id' => $renting->renting_type_id,
            'area' => (int) $renting->area,
            'description' => $renting->description,
            'sleeping_num' => (int) $renting->sleeping_num
        ]);

        return $id;
    }

    public function save( Renting $renting ): int
    {
        $query = 'UPDATE '.$this->table().
            ' SET renter_id=:renter_id, city=:city, country=:country, price=:price,'.
            ' renting_type_id=:renting_type_id, area=:area, description=:description, sleeping_num=:sleeping_num'.
            ' WHERE id=:id';

        $id = $this->update( $query, [
            'id' => $renting->id,
            'renter_id' => $renting->renter_id,
            'city' => $renting->city,
            'country' => $renting->country,
            'price' => (int) $renting->price,
            'renting_type_id' => $renting->renting_type_id,
            'area' => (int) $renting->area,
            'description' => $renting->description,
            'sleeping_num' => (int) $renting->sleeping_num
        ]);

        return $id;
    }

    public function saveBindMedia( int $renting_id, int $medium_id ): int
    {
        $query = 'UPDATE medium_renting SET medium_id=:medium_id WHERE renting_id=:renting_id';

        $id = $this->update( $query, [
            'renting_id' => $renting_id,
            'medium_id' => $medium_id
        ]);

        return $id;
    }

    #endregion Changement dans la bdd
}