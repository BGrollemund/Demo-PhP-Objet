<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\User;
use airBnB\System\Database\Repository;

class UserRepository extends Repository
{
    protected function table(): string { return 'users'; }

    public function getByEmail( string $email ): ?User
    {
        $query = 'SELECT * FROM '.$this->table().' WHERE email = :email ';

        $stmt = $this->read( $query, ['email' =>$email] );

        if( is_null($stmt) ) return null;

        $user_data = $stmt->fetch();

        return $user_data ? new User( $user_data ) : null;
    }

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

}