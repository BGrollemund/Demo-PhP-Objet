<?php


namespace airBnB\System\Database;


abstract class Model
{
    public $id;

    public function __construct( array $data = [] )
    {
        $this->hydrate( $data );
    }

    abstract public function toArray(): array;

    private function hydrate( $data ): void
    {
        foreach( $data as $column => $value ) {
            if( ! property_exists( $this, $column ) )
                continue;

            $this->$column = $value;
        }
    }
}