<?php


namespace airBnB\Database\Model;


use airBnB\System\Database\Model;

class Booking extends Model
{
    public $user_id;
    public $renting_id;
    public $start_date;
    public $end_date;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'renting_id' => $this->renting_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date
        ];
    }
}