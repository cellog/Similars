<?php
namespace SimilarTransactions\Player;
class HistoryParser
{
    function downloadAndParse($downloader, $player)
    {
        $transfers = array();
        $page = '';
        $num = 1;
        do {
            $history = $downloader->download('http://en.strikermanager.com/historial.php?id_jugador=' . $player . $page);
            $transfers = array_merge($transfers, $this->parse($history));
            if (preg_match('@historial\.php\?id_jugador=' . $player . '&pagina=' . $num . '@',
                           $history, $nextpage)) {
                $page = '&pagina=' . $num++;
            } else {
                $page = '';
            }
        } while ($page);
        return $transfers;
    }

    function parse($history)
    {
        preg_match_all('@<td style="white-space:nowrap;">(?<stamp>[^<]+)</td>\s+' .
                       '<td class="equipo"><a href="equipo.php\?id=\d+">[^<]+</a></td>\s+' .
                       '<td class="numerico">(?<average>\d+)</td>\s+' .
                       '<td><a href="equipo.php\?id=\d+">[^<]+</a></td>\s+' .
                       '<td class="numerico">(?<amount>[0-9\.]+) &euro;</td>\s+' .
                       '<td class="numerico" style="white-space:nowrap;">(?<type>Transfer agreement|Hostile Clause|Auction)</td>\s+' .
                       '<td class="numerico">[0-9\.]+&nbsp;&euro;</td>@', $history, $matches);
        $transfers = array();
        foreach ($matches[0] as $i => $notused) {
            $transfers[] = array(
                // TODO: fix timestamp, strtotime does not properly parse it
                // we have 2 kinds of dates:
                // 12 Feb 12:49
                // 16/08/2012 16:07
                // DateTime has a way to parse based on a format, I'll try that.
                'timestamp' => strtotime($matches['stamp'][$i]),
                'average' => $matches['average'][$i],
                'amount' => str_replace('.','', $matches['amount'][$i]),
                'type' => $matches['type'][$i],
            );
        }
        return $transfers;
    }
}