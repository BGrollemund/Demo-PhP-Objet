<?php


namespace airBnB\Database\Model;


use airBnB\System\Database\Model;

class Renting extends Model
{
    public $renter_id;
    public $city;
    public $country;
    public $price;
    public $renting_type_id;
    public $renting_type_label;
    public $area;
    public $description;
    public $sleeping_num;

    public function toArray(): array
    {
        return [
            'renter_id' => $this->renter_id,
            'id' => $this->id,
            'city' => $this->city,
            'country' => $this->country,
            'price' => $this->price,
            'renting_type_id' => $this->renting_type_id,
            'area' => $this->area,
            'description' => $this->description,
            'sleeping_num' => $this->sleeping_num
        ];
    }
}