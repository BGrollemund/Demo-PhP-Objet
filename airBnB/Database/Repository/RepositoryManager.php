<?php


namespace airBnB\Database\Repository;


use airBnB\System\Database\Database;

class RepositoryManager
{
    private static $manager = null;

    private $bookingRepository = null;
    public function bookingRepository(): BookingRepository { return $this->bookingRepository; }

    private $equipmentRepository = null;
    public function equipmentRepository(): EquipmentRepository { return $this->equipmentRepository; }

    private $mediumRepository = null;
    public function mediumRepository(): MediumRepository { return $this->mediumRepository; }

    private $profileRepository = null;
    public function profileRepository(): ProfileRepository { return $this->profileRepository; }

    private $rentingRepository = null;
    public function rentingRepository(): RentingRepository { return $this->rentingRepository; }

    private $rentingTypeRepository = null;
    public function rentingTypeRepository(): RentingTypeRepository { return $this->rentingTypeRepository; }

    private $roleRepository = null;
    public function roleRepository(): RoleRepository { return $this->roleRepository; }

    private $userRepository = null;
    public function userRepository(): UserRepository { return $this->userRepository; }

    public static function manager(): ?self
    {
        if( is_null(self::$manager) )
            self::$manager = new self();

        return self::$manager;
    }

    private function __construct() {
        $pdo = Database::connection();

        $this->bookingRepository = new BookingRepository($pdo);
        $this->equipmentRepository = new EquipmentRepository($pdo);
        $this->mediumRepository = new MediumRepository($pdo);
        $this->profileRepository = new ProfileRepository($pdo);
        $this->rentingRepository = new RentingRepository($pdo);
        $this->rentingTypeRepository = new RentingTypeRepository($pdo);
        $this->roleRepository = new RoleRepository($pdo);
        $this->userRepository = new UserRepository($pdo);
    }

    private function __clone() {}
    private function __wakeup() {}
}