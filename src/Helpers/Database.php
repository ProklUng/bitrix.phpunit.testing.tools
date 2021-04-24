<?php

namespace Prokl\BitrixTestingTools\Helpers;

use InvalidArgumentException;
use mysqli;
use RuntimeException;

/**
 * Class Database
 * @package Prokl\BitrixTestingTools\Helpers
 *
 * @since 24.04.2021
 */
class Database
{
    /**
     * @var mysqli $db Соединение с базой.
     */
    private $db;

    /**
     * @var string $host
     */
    private $host;

    /**
     * @var string $username
     */
    private $username;

    /**
     * @var string $password
     */
    private $password;

    /**
     * @var string $database
     */
    private $database;

    /**
     * Database constructor.
     *
     * @param string $host     Хост.
     * @param string $database База данных.
     * @param string $username Логин.
     * @param string $password Пароль.
     */
    public function __construct(
        string $host,
        string $database,
        string $username,
        string $password
    ) {
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Создать базу, если не существует.
     *
     * @return boolean
     * @throws RuntimeException
     */
    public function createDatabaseIfNotExist() : bool
    {
        $this->connect();
        $this->db->query('USE ' . $this->database . ';');
        $result = $this->db->query('CREATE DATABASE IF NOT EXISTS ' . $this->database);
        if ($result === false) {
            throw new \RuntimeException(
                $this->db->connect_error
            );
        }

        $this->db->close();

        return true;
    }

    /**
     * Уничтожить базу, если существует.
     *
     * @return void
     */
    public function dropBase() : void
    {
        $this->connect();

        $this->db->query('USE ' . $this->database);
        $this->db->query('DROP DATABASE IF EXISTS ' . $this->database);

        $this->db->close();
    }

    /**
     * Проверка - база не пустая ли.
     *
     * @return boolean
     */
    public function hasEmptyBase() : bool
    {
        $db = mysqli_connect(
            getenv('MYSQL_HOST', true) ?: getenv('MYSQL_HOST'),
            getenv('MYSQL_USER', true) ?: getenv('MYSQL_USER'),
            getenv('MYSQL_PASSWORD', true) ?: getenv('MYSQL_PASSWORD'),
            getenv('MYSQL_DATABASE', true) ?: getenv('MYSQL_DATABASE')
        );

        if (!$db) {
            throw new InvalidArgumentException('Mysql connection error.');
        }

        $result = true;
        if (mysqli_query($db,"DESCRIBE b_user")){
            $result = false;
        }

        mysqli_close($db);
        return $result;
    }

    /**
     * Соединение с сервером БД.
     *
     * @return void
     * @throws InvalidArgumentException Когда не получилось подключиться.
     */
    public function connect() : void
    {
        $this->db = new mysqli($this->host, $this->username, $this->password);
        if ($this->db->connect_error) {
            throw new InvalidArgumentException('Mysql connection error: ' . $this->db->connect_error);
        }
    }
}