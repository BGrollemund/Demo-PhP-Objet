<?php


namespace airBnB\System\Database;


use PDO;
use PDOStatement;

abstract class Repository
{
    protected $pdo = null;

    public function __construct( PDO $pdo ) { $this->pdo = $pdo; }

    protected abstract function table(): string;

    protected function create( string $query, ?array $params = null ): int
    {
        if( ! $this->checkSQLCommand($query, 'INSERT') )
            return 0;

        $stmt = $this->pdo->prepare($query);
        $success = $stmt->execute($params);

        if( ! $success ) return 0;

        return $this->pdo->lastInsertId();
    }

    protected function read( string $query, ?array $params = null ): ?PDOStatement
    {
        if( ! $this->checkSQLCommand($query, 'SELECT') )
            return null;

        $stmt = $this->pdo->prepare($query);
        $success = $stmt->execute($params);

        if( ! $success ) return null;

        return $stmt;
    }

    protected function update( string $query, ?array $params = null ): int
    {
        if( ! $this->checkSQLCommand($query, 'UPDATE') )
            return 0;

        $stmt = $this->pdo->prepare($query);
        $success = $stmt->execute($params);

        if( ! $success ) return 0;

        return $stmt->rowCount();
    }

    protected function delete( string $query, ?array $params = null ): int
    {
        if( ! $this->checkSQLCommand($query, 'DELETE') )
            return 0;

        $stmt = $this->pdo->prepare($query);
        $success = $stmt->execute($params);

        if( ! $success ) return 0;

        return $stmt->rowCount();
    }

    private function checkSQLCommand( string $query, string $command ): bool
    {
        $query_words = explode( ' ', $query );

        if( strtoupper( $query_words[0] ) !== $command )
            return false;

        return true;
    }
}