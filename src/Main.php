<?php
namespace SimilarTransactions;
use Mysqli, mysqli_sql_exception;
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
        if ($this->db->connect_error) {
            die('Connect Error (' . $e->getMessage() . ')');
        }
    }

    function setupDatabase()
    {
        try {
            $this->db->query('SELECT * FROM similars_leagues LIMIT 0,1;');
            return;
        } catch (mysqli_sql_exception $e) {
            
        }
    }

    function __destruct()
    {
        $this->db->close();
    }
}
new Main;