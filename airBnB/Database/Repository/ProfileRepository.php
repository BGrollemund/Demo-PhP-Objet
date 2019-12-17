<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\Profile;
use airBnB\System\Database\Repository;

class ProfileRepository extends Repository
{
    protected function table(): string { return 'profiles'; }

    #region Recherche dans la bdd

    public function findByUsername(string $username ): ?Profile
    {
        $query = 'SELECT * FROM '.$this->table().' WHERE username=:username ';

        $stmt = $this->read( $query, [ 'username' => $username ] );

        if( is_null($stmt) ) return null;

        $user_data = $stmt->fetch();

        return $user_data ? new Profile( $user_data ) : null;
    }

    #endregion Recherche dans la bdd


    #region Changement dans la bdd

    public function insert( Profile $profile ): int
    {
        $query = 'INSERT INTO '.$this->table().' SET username=:username, birth_date=:birth_date, city=:city, country=:country';

        $id = $this->create( $query, [
            'username' => $profile->username,
            'birth_date' => $profile->birth_date,
            'city' => $profile->city,
            'country' => $profile->country
        ]);

        return $id;
    }

    #endregion Changement dans la bdd
}