<?php
namespace SimilarTransactions\Player;
class HistoryParser
{
    function downloadAndParse($downloader, $player, $parent)
    {
        $transfers = array();
        $page = '';
        $num = 1;
        $age = 0;
        do {
            $history = $downloader->download($url = 'http://en.strikermanager.com/historial.php?id_jugador=' . $player . $page);
            if (!$age) {
                preg_match('@<td>Age</td>\s+<td>(\d+) years</td>@', $history, $match);
                $age = $match[1];
                $parent->setCurrentAge($age);
            }
            $transfers = array_merge($transfers, $this->parse($history, $url));
            if (preg_match('@historial\.php\?id_jugador=' . $player . '&pagina=' . $num . '@',
                           $history, $nextpage)) {
                $page = '&pagina=' . $num++;
            } else {
                $page = '';
            }
        } while ($page);
        return $transfers;
    }

    function parse($history, $url)
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
                'url' => $url,
            );
        }
        return $transfers;
    }
}