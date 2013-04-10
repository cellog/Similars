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
        preg_match_all('@jugador\.php\?id_jugador=(\d+)" >[^<]+</a></td>\s+' .
                       '<td style="text-align: center;" title="[^"]+"><div style="display: none;">[^<]+</div>([A-Z]+)<@',
                       $senior, $players);
        foreach ($players[1] as $i => $player) {
            $player = new Player($player);
            $player->setPosition($players[2][$i]);
            $player->getTransfers($downloader);
            $this->seniors[] = $player;
        }
        $junior = $downloader->download('http://en.strikermanager.com/plantilla.php?juveniles=1&id_equipo=' . $this->id);
        preg_match_all('@jugador\.php\?id_jugador=(\d+)" >[^<]+</a></td>\s+' .
                       '<td style="text-align: center;" title="[^"]+"><div style="display: none;">[^<]+</div>([A-Z]+)<@',
                       $junior, $players);
        foreach ($players[1] as $player) {
            $player = new Player($player);
            $player->getTransfers($downloader);
            $this->juniors[] = $player;
        }
    }
}
