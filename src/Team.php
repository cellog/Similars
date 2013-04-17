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
            echo "Processing " . $this->name, " seniors\n";
        }
        $senior = $downloader->download('http://en.strikermanager.com/plantilla.php?id_equipo=' . $this->id);
        preg_match_all('@jugador\.php\?id_jugador=(\d+)" >[^<]+</a></td>\s+' .
                       '<td style="text-align: center;" title="[^"]+"><div style="display: none;">[^<]+</div>([A-Z]+)<@',
                       $senior, $players);
        $seniors = new Team\SquadGrabber($players, $this->id, $downloader);
        $seniors->go();
        $seniors->waitForChildren();
        if (Main::DEBUG) {
            echo "Processing " . $this->name, " juniors\n";
        }
        $junior = $downloader->download('http://en.strikermanager.com/plantilla.php?juveniles=1&id_equipo=' . $this->id);
        preg_match_all('@jugador\.php\?id_jugador=(\d+)" >[^<]+</a></td>\s+' .
                       '<td style="text-align: center;" title="[^"]+"><div style="display: none;">[^<]+</div>([A-Z]+)<@',
                       $junior, $players);
        $juniors = new Team\SquadGrabber($players, $this->id, $downloader);
        $juniors->go();
        $juniors->waitForChildren();
    }
}
