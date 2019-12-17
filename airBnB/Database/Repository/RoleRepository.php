<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\Role;
use airBnB\System\Database\Repository;

class RoleRepository extends Repository
{
    protected function table(): string { return 'roles'; }

    #region Recherche dans la bdd

    public function findById(int $id ): ?Role
    {
        $query = 'SELECT * FROM '.$this->table().' WHERE id=:role_id ';

        $stmt = $this->read( $query, [ 'role_id' => $id ] );

        if( is_null( $stmt ) ) return null;

        $role_data = $stmt->fetch();

        return $role_data ? new Role( $role_data ) : null;
    }

    public function findIdByLabel(string $label ): int
    {
        $query = 'SELECT * FROM '.$this->table().' WHERE label=:label ';

        $stmt = $this->read( $query, [ 'label' => $label ] );

        if( is_null( $stmt ) ) return 0;

        $label_data = $stmt->fetch();

        return $label_data ? $label_data[ 'id' ] : 0;
    }

    #endregion Recherche dans la bdd


    #region Changement dans la bdd



    #endregion Changement dans la bdd
}