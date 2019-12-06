<?php


namespace airBnB\Database\Repository;


use airBnB\System\Database\Repository;

class MediumRepository extends Repository
{
    public function table(): string { return 'media'; }
}