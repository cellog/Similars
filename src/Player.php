<?php
namespace SimilarTransactions;
class Player
{
    public
        $id,
        $transfers;
    function __construct($id)
    {
        $this->id = $id;
    }

    function getTransfers($downloader)
    {
        $a = new Player\HistoryParser;
        $this->transfers = $a->downloadAndParse($downloader, $this->id);
    }
}
