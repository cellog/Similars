<?php
namespace SimilarTransactions;
class Team
{
    public
        $id,
        $seniors = array(),
        $juniors = array();
    function __construct($id)
    {
        $this->id = $id;
    }

    function getSquad($downloader)
    {
        $senior = $downloader->download('http://en.strikermanager.com/plantilla.php?id_equipo=' . $this->id);
        preg_match_all('/jugador\.php\?id_jugador=(\d+)/', $senior, $players);
        foreach ($players[1] as $player) {
            $player = new Player($player);
            $player->getTransfers($downloader);
            $this->seniors[] = $player;
        }
        $junior = $downloader->download('http://en.strikermanager.com/plantilla.php?juveniles=1&id_equipo=' . $this->id);
        preg_match_all('/jugador\.php\?id_jugador=(\d+)/', $junior, $players);
        foreach ($players[1] as $player) {
            $player = new Player($player);
            $player->getTransfers($downloader);
            $this->juniors[] = $player;
        }
    }
}
