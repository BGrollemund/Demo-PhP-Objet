<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\User;
use airBnB\System\Database\Repository;

class UserRepository extends Repository
{
    protected function table(): string { return 'users'; }

    #region Recherche dans la bdd

    public function findByEmail(string $email ): ?User
    {
        $query = 'SELECT * FROM '.$this->table().' WHERE email=:email ';

        $stmt = $this->read( $query, [ 'email' => $email ] );

        if( is_null($stmt) ) return null;

        $user_data = $stmt->fetch();

        return $user_data ? new User( $user_data ) : null;
    }

    #endregion Recherche dans la bdd


    #region Changement dans la bdd

    public function insert( User $user ): int
    {
        $query = 'INSERT INTO '.$this->table().' SET role_id=:role_id, profile_id=:profile_id, email=:email, password=:password';

        $id = $this->create( $query, [
            'role_id' => $user->role_id,
            'profile_id' => $user->profile_id,
            'email' => $user->email,
            'password' => $user->password
        ]);

        return $id;
    }

    public function bindFavoriteById( int $user_id, int $renting_id ): bool
    {
        $query = 'INSERT INTO favorites SET user_id=:user_id, renting_id=:renting_id';

        $id = $this->create( $query, [
            'user_id' => $user_id,
            'renting_id' => $renting_id
        ]);

        return $id > 0;
    }

    public function unbindFavoriteById( int $user_id, int $renting_id ): bool
    {
        $query = 'DELETE FROM favorites WHERE user_id=:user_id AND renting_id=:renting_id';

        $id = $this->delete( $query, [
            'user_id' => $user_id,
            'renting_id' => $renting_id
        ]);

        return $id > 0;
    }

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

    #endregion Changement dans la bdd
}