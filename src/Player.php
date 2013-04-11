<?php
namespace SimilarTransactions;
class Player
{
    public
        $id,
        $hash = false,
        $team,
        $transfers,
        $age,
        $pos,
        $lastupdate = false;
    function __construct($id)
    {
        $this->id = $id;
    }

    function getTransfers($downloader, Main $main)
    {
        $a = new Player\HistoryParser;
        $this->transfers = $a->downloadAndParse($downloader, $this->id, $this, $main);
    }

    function setCurrentAge($age)
    {
        $this->age = $age;
    }

    function setPosition($pos)
    {
        $this->pos = $pos;
    }

    function setLastUpdate($lastupdate)
    {
        $this->lastupdate = $lastupdate;
    }

    function setTeam($team)
    {
        $this->team = $team;
    }

    function getPlayerHash()
    {
        if ($this->hash) return $this->hash;
        return md5($this->id . ' similar ');
    }
}
