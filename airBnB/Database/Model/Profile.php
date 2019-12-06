<?php


namespace airBnB\Database\Model;


use airBnB\System\Database\Model;

class Profile extends Model
{
    public $avatar_id;
    public $username;
    public $birth_date;
    public $bio;
    public $city;
    public $country;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'avatar_id' => $this->avatar_id,
            'username' => $this->username,
            'birth_date' => $this->birth_date,
            'bio' => $this->bio,
            'city' => $this->city,
            'country' => $this->country
        ];
    }
}