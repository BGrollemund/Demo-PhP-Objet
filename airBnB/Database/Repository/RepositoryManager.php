<?php


namespace airBnB\Database\Repository;


use airBnB\System\Database\Database;

class RepositoryManager
{
    private static $manager = null;

    private $mediumRepository = null;
    public function mediumRepository(): MediumRepository { return $this->mediumRepository; }

    private $roleRepository = null;
    public function roleRepository(): RoleRepository { return $this->roleRepository; }

    public static function manager(): ?self
    {
        if( is_null(self::$manager) )
            self::$manager = new self();

        return self::$manager;
    }

    private function __construct() {
        $pdo = Database::connection();

        $this->mediumRepository = new MediumRepository($pdo);
        $this->roleRepository = new RoleRepository($pdo);
    }

    private function __clone() {}
    private function __wakeup() {}
}