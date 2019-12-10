<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\Profile;
use airBnB\System\Database\Repository;

class ProfileRepository extends Repository
{
    protected function table(): string { return 'profiles'; }


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
}