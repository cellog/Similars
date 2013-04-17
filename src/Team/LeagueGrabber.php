<?php
namespace SimilarTransactions\Team;
use SimilarTransactions\Team, SimilarTransactions\Main, SimilarTransactions\ProcessManager;
class LeagueGrabber extends ProcessManager
{
    const PROCESSCOUNT = 3;
    protected $teams;
    protected $teamindex = -1;
    protected $downloader;
    protected $cookies;
    function __construct($teams, $downloader)
    {
        $this->teams = $teams;
        $this->cookies = $downloader->retrieveCookies();
    }

    function childSetup()
    {
        $this->teamindex++;
    }

    function parent()
    {
        return $this->teamindex >= count($this->teams[1]);
    }

    function child()
    {
        $team = new Team($this->teams[1][$this->teamindex], $this->teams[2][$this->teamindex]);
        $main = new Main('dummy');
        $main->getDownloader()->setCookies($this->cookies);
        $team->getSquad($main->getDownloader(), $main);
        if (Main::DEBUG) {
            echo "child team " . $this->teamindex . "\n";
        }
    }
}
