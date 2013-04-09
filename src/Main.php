<?php
namespace SimilarTransactions;
use Mysqli, mysqli_sql_exception, DateTime;
class Main
{
    protected $user;
    protected $pass;
    protected $database;
    /**
     * @var Mysqli
     */
    protected $db;
    function __construct()
    {
        if (!file_exists(__DIR__ . '/../config.json')) {
            die("no configuration file found\n");
        }
        $info = json_decode(file_get_contents(__DIR__ . '/../config.json'), 1);
        $this->user = $info['user'];
        $this->pass = $info['password'];
        $this->database = $info['database'];
        \mysqli_report(MYSQLI_REPORT_STRICT);
        try {
            $this->db = new Mysqli('127.0.0.1', $this->user, $this->pass, $this->database);
        } catch (mysqli_sql_exception $e) {
            die('Connect Error (' . $e->getMessage() . ')');
        }
        $this->setupDatabase();
    }

    function setupDatabase()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS transaction (
            hash CHAR(32) NOT NULL,
            type TINYINT NOT NULL,
            stamp TIMESTAMP NOT NULL,
            price BIGINT NOT NULL,
            position VARCHAR(3) NOT NULL,
            age TINYINT NOT NULL,
            INDEX type (type),
            INDEX price (price),
            INDEX age (age),
            INDEX positionage (position, age),
            INDEX positiontype (position, type),
            PRIMARY KEY (hash, stamp)
        );');
    }

    function getAgeOfTransaction($stamp, $currentage)
    {
        $season = $this->getSeason(time());
        return $currentage - ($season - $this->getSeason($stamp));
    }

    function getSeason($stamp)
    {
        $date = new DateTime();
        $date->setTimestamp($stamp);
        // March 29, 2013 is start of season 10
        $comp->setDate(2013, 3, 29);
        if (!$date->diff($comp)->invert) {
            return 10;
        }
        // December 28, 2012 is start of season 9
        $comp->setDate(2012, 12, 28);
        if (!$date->diff($comp)->invert) {
            return 9;
        }
        // September 28, 2012
        $comp->setDate(2012, 9, 28);
        if (!$date->diff($comp)->invert) {
            return 8;
        }
        // June 29, 2012
        $comp->setDate(2012, 6, 28);
        if (!$date->diff($comp)->invert) {
            return 7;
        }
        // March 30, 2012
        $comp->setDate(2012, 3, 30);
        if (!$date->diff($comp)->invert) {
            return 6;
        }
        // December 30, 2011
        $comp->setDate(2011, 12, 30);
        if (!$date->diff($comp)->invert) {
            return 5;
        }
        // September 17, 2011
        $comp->setDate(2011, 9, 17);
        if (!$date->diff($comp)->invert) {
            return 4;
        }
        return false; // too old to use
    }

    function __destruct()
    {
        $this->db->close();
    }
}
new Main;