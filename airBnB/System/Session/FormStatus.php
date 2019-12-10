<?php


namespace airBnB\System\Session;


class FormStatus
{
    private $errors = [];
    private $values = [];

    public function addError( string $name, string $message ): void
    {
        $this->errors[ $name ] = $message;
    }

    public function getError( string $name ): ?string
    {
        return $this->errors[ $name ] ?? null;
    }

    public function hasError( string $name = '' ): bool {
        if( empty( $name ) )
            return count( $this->errors ) > 0;

        return ! empty( $this->getError( $name ) );
    }

    public function addValue( string $name, string $value ): void
    {
        $this->values[ $name ] = $value;
    }

    public function getValue( string $name ): ?string
    {
        return $this->values[ $name ] ?? null;
    }
}