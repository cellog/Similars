<?php
namespace SimilarTransactions;
use Mysqli, mysqli_sql_exception, DateTime;
class Main
{
    protected $user;
    protected $pass;
    protected $database;
    protected $downloader;
    protected $start;
    protected $end;
    /**
     * @var Mysqli
     */
    protected $db;
    function __construct($startleague, $endleague = null)
    {
        date_default_timezone_set('Africa/Abidjan');
        $this->start = $startleague;
        $this->end = $endleague;
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
        $this->downloader = new SMGrabber;
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
            average TINYINT NOT NULL,
            url VARCHAR(200) NOT NULL,
            INDEX type (type),
            INDEX price (price),
            INDEX age (age),
            INDEX positionage (position, age),
            INDEX positiontype (position, type),
            PRIMARY KEY (hash, stamp)
        );');
        $this->db->query('CREATE TABLE IF NOT EXISTS player (
            team_id INT NOT NULL,
            hash CHAR(32) NOT NULL,
            lastupdated TIMESTAMP NOT NULL,
            current_age TINYINT NOT NULL,
            position VARCHAR(3) NOT NULL,
            INDEX team (team_id),
            PRIMARY KEY (hash)
        )');
    }

    function getTeam($teamid, $transfers = false)
    {
        $players = $this->db->query('SELECT * FROM players WHERE team_id="' . $this->db->real_escape_string($teamid) . '"');
        if (!$players->num_rows) {
            return array();
        }
        $ret = array();
        while ($player = $players->fetch_assoc()) {
            $p = new Player($player['hash']);
            $p->setCurrentAge($player['current_age']);
            $p->setPosition($player['position']);
            $p->setLastUpdate($player['lastupdated']);
            $p->setTeam($player['team_id']);
            if ($transfers) {
                $p->transfers = $this->getTransfers($player['hash']);
            }
        }
        return $ret;
    }

    /**
     * convert to a Mysql TIMESTAMP
     */
    function toTimeStamp($stamp)
    {
        $d = new DateTime;
        $d->setTimestamp($stamp);
        return $d->format('Y-m-d H:i');
    }

    /**
     * convert to a unix timestamp
     */
    function fromTimeStamp($stamp)
    {
        $d = DateTime::createFromFormat('Y-m-d H:i', $stamp);
        return $d->getTimestamp();
    }

    function toTypeCode($type)
    {
        $arr = array(
            'Transfer agreement' => 0,
            'Hostile Clause' => 1,
            'Auction' => 2,
            'Direct Purchase' => 3
        );
        return $arr[$type];
    }

    function fromTypeCode($code)
    {
        $arr = array(
            'Transfer agreement',
            'Hostile Clause',
            'Auction',
            'Direct Purchase'
        );
        return $arr[$code];
    }

    function getTransfers($hash)
    {
        $transfers = $this->db->query('SELECT * FROM transactions WHERE hash="' . $this->db->real_escape_string($hash).'"
                                      ORDER BY stamp DESC');
        $ret = array();
        if (!$transfers) {
            return $ret;
        }
        while ($transfer = $transfers->fetch_assoc()) {
            $ret[] = array(
                'timestamp' => $this->fromTimeStamp($transfer['stamp']),
                'average' => $transfer['average'],
                'price' => $transfer['price'],
                'type' => $this->fromTypeCode($transfer['type']),
                'url' => $transfer['url'],
            );
        }
        return $ret;
    }

    function updatePlayer(Player $player)
    {
        $this->db->query($z = 'REPLACE INTO player (team_id, hash, lastupdated, current_age, position) VALUES (
                         "' . $this->db->real_escape_string($player->team) . '",
                         "' . $this->db->real_escape_string($player->getPlayerHash()) . '",
                         CURRENT_TIMESTAMP,
                         "' . $this->db->real_escape_string($player->age) . '",
                         "' . $this->db->real_escape_string($player->pos) . '"
                         );');
        foreach ($player->transfers as $transfer) {
            $age = $this->getAgeOfTransaction($transfer['timestamp'], $player->age);
            if (!$age) {
                continue;
            }
            $this->db->query($z = 'REPLACE INTO transaction (age, average, hash, position, price, stamp, type, url) VALUES (
                             "' . $this->db->real_escape_string($age) . '",
                             "' . $this->db->real_escape_string($transfer['average']) . '",
                             "' . $this->db->real_escape_string($player->getPlayerHash()) . '",
                             "' . $this->db->real_escape_string($player->pos) . '",
                             "' . $this->db->real_escape_string($transfer['price']) . '",
                             "' . $this->db->real_escape_string($this->toTimeStamp($transfer['timestamp'])) . '",
                             "' . $this->db->real_escape_string($this->toTypeCode($transfer['type'])) . '",
                             "' . $this->db->real_escape_string($transfer['url']) . '"
                             );');
        }
    }

    function downloadLeagues()
    {
        $id = $this->start;
        do {
            $league = $this->downloader->download('http://en.strikermanager.com/liga.php?id_liga=' . $id);
            preg_match_all('/equipo\.php\?id=(\d+)/', $league, $matches);
            foreach($matches[1] as $team) {
                $team = new Team($team);
                $team->getSquad($this->downloader, $this);
                $team->update($this);
            }
            $id += 2;
        } while ($id <= $this->end);
    }

    function getAgeOfTransaction($stamp, $currentage)
    {
        $season = $this->getSeason(time());
        $transactionseason = $this->getSeason($stamp);
        if (!$transactionseason) return false;
        return $currentage - ($season - $transactionseason);
    }

    function getSeason($stamp)
    {
        $date = new DateTime();
        $comp = new DateTime();
        $date->setTimestamp($stamp);
        // March 29, 2013 is start of season 10
        $comp->setTime(0, 0, 0);
        $comp->setDate(2013, 3, 29);
        if ($date->diff($comp)->invert) {
            return 10;
        }
        // December 28, 2012 is start of season 9
        $comp->setDate(2012, 12, 28);
        if ($date->diff($comp)->invert) {
            return 9;
        }
        // September 28, 2012
        $comp->setDate(2012, 9, 28);
        if ($date->diff($comp)->invert) {
            return 8;
        }
        // June 29, 2012
        $comp->setDate(2012, 6, 28);
        if ($date->diff($comp)->invert) {
            return 7;
        }
        // March 30, 2012
        $comp->setDate(2012, 3, 30);
        if ($date->diff($comp)->invert) {
            return 6;
        }
        // December 30, 2011
        $comp->setDate(2011, 12, 30);
        if ($date->diff($comp)->invert) {
            return 5;
        }
        // September 17, 2011
        $comp->setDate(2011, 9, 17);
        if ($date->diff($comp)->invert) {
            return 4;
        }
        return false; // too old to use
    }

    function __destruct()
    {
        $this->db->close();
    }
}