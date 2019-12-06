<?php


namespace airBnB\Database\Model;


use airBnB\System\Database\Model;

class Availability extends Model
{
    public $renting_id;
    public $start_date;
    public $end_date;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'renting_id' => $this->renting_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date
        ];
    }
}