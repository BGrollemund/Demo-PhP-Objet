<?php


namespace airBnB\Database\Model;


use airBnB\System\Database\Model;

class Medium extends Model
{
    public $medium_type_id;
    public $filename;
    public $caption;
    public $create_at;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'medium_type_id' => $this->medium_type_id,
            'filename' => $this->filename,
            'caption' => $this->caption
        ];
    }
}