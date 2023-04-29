<?php

declare(strict_types=1);

namespace App\Config;

class DatabaseConnexion
{
    private $pathToSqliteFile;

    /**
     * PDO instance.
     *
     * @var type
     */
    private $pdo;

    public function __construct()
    {
        $this->pathToSqliteFile = __DIR__.'/../../var/database.db';
    }

    /**
     * return in instance of the PDO object that connects to the SQLite database.
     * If the instance already exists, it returns it.
     *
     * @return \PDO
     */
    public function connect()
    {
        if (null == $this->pdo) {
            try {
                $this->pdo = new \PDO('sqlite:'.$this->pathToSqliteFile);
            } catch (\PDOException $error) {
                throw new \PDOException($error->getMessage(), (int) $error->getCode());
            }
        }

        return $this->pdo;
    }
}
