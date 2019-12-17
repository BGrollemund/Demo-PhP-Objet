<?php


namespace airBnB\Database\Repository;


use airBnB\Database\Model\Medium;
use airBnB\System\Database\Repository;

class MediumRepository extends Repository
{
    public function table(): string { return 'media'; }

    #region Recherche dans la bdd



    #endregion Recherche dans la bdd


    #region Changement dans la bdd

    public function insert( Medium $medium ): int
    {
        $query = 'INSERT INTO '.$this->table().
            ' SET medium_type_id=:medium_type_id, filename=:filename, filepath=:filepath, caption=:caption';

        $id = $this->create( $query, [
            'medium_type_id' => $medium->medium_type_id,
            'filename' => $medium->filename,
            'filepath' => $medium->filepath,
            'caption' => $medium->caption
        ]);

        return $id;
    }

    #endregion Changement dans la bdd
}