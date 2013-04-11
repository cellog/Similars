<?php
namespace SimilarTransactions\Player;
class HistoryParser
{
    protected $done = false;
    function downloadAndParse($downloader, $player, $parent, $main)
    {
        $transfers = array();
        $page = '';
        $num = 1;
        $age = 0;
        $existingTransfers = $main->getTransfers($parent->getPlayerHash());
        do {
            $history = $downloader->download($url = 'http://en.strikermanager.com/historial.php?id_jugador=' . $player . $page);
            if (!$age) {
                preg_match('@<td>Age</td>\s+<td>(\d+) years</td>@', $history, $match);
                $age = $match[1];
                $parent->setCurrentAge($age);
            }
            $pagetransfers = $this->parse($history, $url, $existingTransfers);
            $transfers = array_merge($transfers, $pagetransfers);
            if ($this->done) {
                return $transfers;
            }
            if (preg_match('@historial\.php\?id_jugador=' . $player . '&pagina=' . $num . '@',
                           $history, $nextpage)) {
                $page = '&pagina=' . $num++;
            } else {
                $page = '';
            }
        } while ($page);
        return $transfers;
    }

    function parse($history, $url, $existingTransfers)
    {
        if (count($existingTransfers)) {
            $newestTransferTime = $existingTransfers[0]['timestamp'];
        } else {
            $newestTransferTime = 0;
        }
        preg_match_all('@<td style="white-space:nowrap;">(?<stamp>[^<]+)</td>\s+' .
                       '<td class="equipo"><a href="equipo.php\?id=\d+">[^<]+</a></td>\s+' .
                       '<td class="numerico">(?<average>\d+)</td>\s+' .
                       '<td><a href="equipo.php\?id=\d+">[^<]+</a></td>\s+' .
                       '<td class="numerico">(?<amount>[0-9\.]+) &euro;</td>\s+' .
                       '<td class="numerico" style="white-space:nowrap;">' .
                       '(?<type>Transfer agreement|Hostile Clause|Auction|Direct Purchase)</td>\s+' .
                       '<td class="numerico">[0-9\.]+&nbsp;&euro;</td>@', $history, $matches);
        $transfers = array();
        foreach ($matches[0] as $i => $notused) {
            $a = \DateTime::createFromFormat('j M H:i', $matches['stamp'][$i]);
            if (!$a) {
                $a = \DateTime::createFromFormat('d/m/Y H:i', $matches['stamp'][$i]);
                if (!$a) {
                    if (preg_match('/Today (.+)/', $matches['stamp'][$i], $match)) {
                        $a = \DateTime::createFromFormat('H:i', $match[1]);
                    } elseif (preg_match('/Yesterday (.+)/', $matches['stamp'][$i], $match)) {
                        $a = \DateTime::createFromFormat('H:i', $match[1]);
                        $a->sub(new \DateInterval('P1D'));
                    } elseif (preg_match('/[A-Za-z]+, (\d+ .+)/', $matches['stamp'][$i], $match)) {
                        $a = \DateTime::createFromFormat('d H:i', $match[1]);
                    } else {
                        var_dump($url, $matches['stamp'][$i]);
                    }
                }
            }
            $stamp = $a->format('U');
            if ($stamp < $newestTransferTime) {
                $this->done = true;
                return $transfers;
            }
            $transfers[] = array(
                'timestamp' => $stamp,
                'average' => $matches['average'][$i],
                'price' => str_replace('.','', $matches['amount'][$i]),
                'type' => $matches['type'][$i],
                'url' => $url,
            );
        }
        return $transfers;
    }
}