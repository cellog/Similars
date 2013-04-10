<?php
namespace SimilarTransactions;
class Player
{
    public
        $id,
        $transfers,
        $age,
        $pos;
    function __construct($id)
    {
        $this->id = $id;
    }

    function getTransfers($downloader)
    {
        $a = new Player\HistoryParser;
        $this->transfers = $a->downloadAndParse($downloader, $this->id, $this);
    }

    function setCurrentAge($age)
    {
        $this->age = $age;
    }

    function setPosition($pos)
    {
        $this->pos = $pos;
    }
}
