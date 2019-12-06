<?php


namespace airBnB\Database\Model;


use airBnB\System\Database\Model;

class Role extends Model
{
    public const USER = 1;
    public const RENTER = 2;

    public $label;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label
        ];
    }
}