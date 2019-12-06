<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\Role;
use airBnB\System\Database\Repository;

class RoleRepository extends Repository
{
    protected function table(): string { return 'roles'; }

    public function getById( int $id ): ?Role
    {
        $query = 'SELECT * FROM '.$this->table().' WHERE id=:role_id ';

        $stmt = $this->read( $query, [ 'role_id' => $id ] );

        if( is_null( $stmt ) ) return null;

        $role_data = $stmt->fetch();

        return $role_data ? new Role( $role_data ) : null;
    }
}