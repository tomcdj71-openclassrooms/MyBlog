<?php

declare(strict_types=1);

namespace App\Config;

class DatabaseConnexion
{
    private $pathToSqliteFile;
    private $env;

    /**
     * PDO instance.
     *
     * @var type
     */
    private $pdo;

    public function __construct()
    {
        $this->pathToSqliteFile = __DIR__.'/../../var/database.db';
        $this->env = new Configuration(['mode']);
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
                if ('dev' === $this->env->get('mode')) {
                    throw new \PDOException($error->getMessage(), (int) $error->getCode());
                }

                return "La base de données n'est pas disponible pour le moment.";
            }
        }

        return $this->pdo;
    }

    /**
     * This function returns a prepared statement for the given SQL query.
     *
     * @param string $sql the SQL query to prepare
     *
     * @return \PDOStatement the prepared statement
     */
    public function prepare($sql): \PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * This method returns the last inserted ID.
     * This method is used in the session class to get the ID of the last inserted session.
     * The ID is then used to update the session table.
     */
    public function lastInsertId(): int
    {
        return $this->pdo->lastInsertId();
    }
}
