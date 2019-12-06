<?php


namespace airBnB\Database\Model;


use airBnB\System\Database\Model;

class User extends Model
{
    public $role_id;
    public $profile_id;
    public $email;
    public $password;
    public $banned;
    public $created_at;
    public $updated_at;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'role_id' => $this->role_id,
            'profile_id' => $this->profile_id,
            'email' => $this->email,
            'password' => $this->password,
            'banned' => $this->banned
        ];
    }
}