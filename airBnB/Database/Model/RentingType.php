<?php


namespace airBnB\Database\Model;


use airBnB\System\Database\Model;

class RentingType extends Model
{
    public $label;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label
        ];
    }
}