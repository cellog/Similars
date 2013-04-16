<?php
namespace SimilarTransactions\Team;
use SimilarTransactions\Player, SimilarTransactions\Main, SimilarTransactions\ProcessManager;
class SquadGrabber extends ProcessManager
{
    protected $nextleague = self::STARTLEAGUE;
    const STARTLEAGUE = 51206;
    const ENDLEAGUE = 51374;
    
    protected $id;
    protected $players;
    protected $playerindex = -1;
    protected $downloader;
    protected $cookies;
    function __construct($players, $id, $downloader)
    {
        $this->id = $id;
        $this->players = $players;
        $this->cookies = $downloader->retrieveCookies();
        // we need a fresh mysqli connection
    }

    function childSetup()
    {
        $this->playerindex++;
    }

    function parent()
    {
        if (Main::DEBUG) {
            echo "parent " . $this->playerindex . "\n";
        }
        return $this->playerindex >= count($this->players[1]);
        $this->nextleague += 2;
        if ($this->nextleague == self::ENDLEAGUE) return true;
        return false;
    }

    function child()
    {
        $player = new Player($this->players[1][$this->playerindex]);
        $player->setPosition($this->players[2][$this->playerindex]);
        $player->setTeam($this->id);
        $main = new Main('dummy');
        $main->getDownloader()->setCookies($this->cookies);
        $player->getTransfers($main->getDownloader(), $main);
        if (Main::DEBUG) {
            echo "child " . $this->playerindex . "\n";
        }
        echo $player->toJson(),"\n";
    }
}