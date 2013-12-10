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
    protected $user;
    function __construct($players, $id, $downloader, $user)
    {
        $this->id = $id;
        $this->players = $players;
        $this->user = $user;
        $this->cookies = $downloader->retrieveCookies();
        // we need a fresh mysqli connection
    }

    function childSetup()
    {
        $this->playerindex++;
    }

    function parent()
    {
        return $this->playerindex > count($this->players[1]);
    }

    function child()
    {
        if ($this->playerindex > count($this->players[0]) || !isset($this->players[1][$this->playerindex])
            || null === $this->players[1][$this->playerindex]) {
            return;
        }
        $player = new Player($this->players[1][$this->playerindex]);
        $player->setPosition($this->players[2][$this->playerindex]);
        $player->setTeam($this->id);
        $main = new Main($this->user, 'dummy');
        $main->getDownloader()->setCookies($this->cookies);
        $player->getTransfers($main->getDownloader(), $main);
        if (Main::DEBUG) {
            echo "child " . $this->playerindex . "\n";
        }
        $main->updatePlayer($player);
        if ($main::DEBUG) {
            echo $player->toJson(),"\n";
        }
    }
}
