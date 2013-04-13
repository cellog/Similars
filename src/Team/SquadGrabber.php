<?php
namespace SimilarTransactions\Team;
use SimilarTransactions\Player, SimilarTransactions\Main;
class SquadGrabber
{
    protected $nextleague = self::STARTLEAGUE;
    const STARTLEAGUE = 51206;
    const ENDLEAGUE = 51374;
    
    protected $id;
    protected $players;
    protected $playerindex = -1;
    protected $downloader;
    function __construct($players, $id, $downloader)
    {
        $this->id = $id;
        $this->players = $players;
        $this->downloader = $downloader;
        // we need a fresh mysqli connection
        $this->main = new Main('dummy'); // league numbers are not used here
    }

    function childSetup()
    {
        $this->playerindex++;
    }

    function parent()
    {
        if (Main::DEBUG) {
            echo "parent " . $this->playerindex;
        }
        return $this->playerindex >= count($this->players);
        $this->nextleague += 2;
        if ($this->nextleague == self::ENDLEAGUE) return true;
        return false;
    }

    function child()
    {
        if (Main::DEBUG) {
            echo "child " . $this->playerindex;
        }
        $player = new Player($player);
        $player->setPosition($players[2][$this->playerindex]);
        $player->setTeam($this->id);
        $player->getTransfers($this->downloader, $this->main);
        echo $player->toJson();
    }
}
