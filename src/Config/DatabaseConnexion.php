<?php

declare(strict_types=1);

namespace App\Config;

class DatabaseConnexion
{
    private $PATH_TO_SQLITE_FILE;

    /**
     * PDO instance.
     *
     * @var type
     */
    private $pdo;

    public function __construct()
    {
        $this->PATH_TO_SQLITE_FILE = __DIR__.'/../../var/database.db';
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
                $this->pdo = new \PDO('sqlite:'.$this->PATH_TO_SQLITE_FILE);
            } catch (\PDOException $e) {
                echo $e->getMessage();
            }
        }

        return $this->pdo;
    }
}
