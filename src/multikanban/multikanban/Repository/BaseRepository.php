<?php

namespace multikanban\multikanban\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use PDO;

abstract class BaseRepository
{
    protected $connection;

    private $repoContainer;

    public function __construct(Connection $connection, RepositoryContainer $repoContainer)
    {
        $this->connection = $connection;
        $this->repoContainer = $repoContainer;
    }

    
}
