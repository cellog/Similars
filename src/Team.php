<?php
namespace SimilarTransactions;
class Team
{
    public
        $id,
        $name,
        $seniors = array(),
        $juniors = array();
    function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = htmlspecialchars_decode($name);
    }

    function getSquad($downloader, Main $main)
    {
        if (Main::DEBUG) {
            echo "Processing " . $this->name, "\n";
        }
        $senior = $downloader->download('http://en.strikermanager.com/plantilla.php?id_equipo=' . $this->id);
        preg_match_all('@jugador\.php\?id_jugador=(\d+)" >[^<]+</a></td>\s+' .
                       '<td style="text-align: center;" title="[^"]+"><div style="display: none;">[^<]+</div>([A-Z]+)<@',
                       $senior, $players);
        $seniors = new Team\SquadGrabber($players[1], $this->id, $this->downloader);
        $seniors->go();
        return;
        //foreach ($players[1] as $i => $player) {
        //    $player = new Player($player);
        //    $player->setPosition($players[2][$i]);
        //    $player->setTeam($this->id);
        //    $player->getTransfers($downloader, $main);
        //    $this->seniors[] = $player;
        //}
        $junior = $downloader->download('http://en.strikermanager.com/plantilla.php?juveniles=1&id_equipo=' . $this->id);
        preg_match_all('@jugador\.php\?id_jugador=(\d+)" >[^<]+</a></td>\s+' .
                       '<td style="text-align: center;" title="[^"]+"><div style="display: none;">[^<]+</div>([A-Z]+)<@',
                       $junior, $players);
        foreach ($players[1] as $player) {
            $player = new Player($player);
            $player->getTransfers($downloader, $main);
            $this->juniors[] = $player;
        }
    }

    function update(Main $main)
    {
        foreach ($this->seniors as $player) {
            $main->updatePlayer($player);
        }
        foreach ($this->juniors as $player) {
            $main->updatePlayer($player);
        }
    }
}
