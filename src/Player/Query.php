<?php
namespace SimilarTransactions\Player;
use SimilarTransactions\Main;
class Query
{
    protected $db;
    protected
        $main,
        $minprice = false,
        $mindate = false,
        $maxprice = false,
        $maxdate = false,
        $ages = array(),
        $positions = array(),
        $averages = array(),
        $types = array();
    function __construct(Main $main)
    {
        $this->db = $main->getDatabase();
        $this->main = $main;
    }

    function age($age)
    {
        $ok = filter_var($age, \FILTER_VALIDATE_INT, array('min_range' => 14, 'max_range' => 38));
        if (!$ok) {
            throw new \Exception('Invalid age, must be between 14 and 38');
        }
        $this->ages[] = $age;
        return $this;
    }

    function position($position)
    {
        $positions = array(
            'GK' => 1,
            'LB' => 1,
            'LDF' => 1,
            'CDF' => 1,
            'RDF' => 1,
            'RB' => 1,
            'DFM' => 1,
            'LM' => 1,
            'LIM' => 1,
            'IM' => 1,
            'RIM' => 1,
            'RM' => 1,
            'OM' => 1,
            'LW' => 1,
            'LF' => 1,
            'CF' => 1,
            'RF' => 1,
            'RW' => 1,
        );
        if (!isset($positions[$position])) {
            throw new \Exception('Invalid position "' . $position . '"');
        }
        $this->positions[] = $position;
        return $this;
    }

    function price($price)
    {
        $ok = filter_var($price, \FILTER_VALIDATE_INT, array('min_range' => 0, 'max_range' => 100000000000000));
        if (!$ok) {
            throw new \Exception('Invalid price, must be between 0 and 100 trillion');
        }
        if (false === $this->minprice) {
            $this->minprice = $price;
            return $this;
        }
        if (false !== $this->maxprice) {
            throw new \Exception('Invalid: minimum and maximum price have already been set');
        }
        $this->maxprice = $price;
        return $this;
    }

    function date($date)
    {
        $ok = filter_var($date, \FILTER_VALIDATE_INT, array('min_range' => 0, 'max_range' => 9999999999));
        if (!$ok) {
            throw new \Exception('Invalid date must be a valid timestamp');
        }
        if (false === $this->mindate) {
            $this->mindate = $date;
            return $this;
        }
        if (false !== $this->maxdate) {
            throw new \Exception('Invalid: minimum and maximum price have already been set');
        }
        $this->maxdate = $date;
        return $this;
    }

    function average($average)
    {
        $ok = filter_var($average, \FILTER_VALIDATE_INT, array('min_range' => 1, 'max_range' => 99));
        if (!$ok) {
            throw new \Exception('Invalid average "' . $average . '"');
        }
        $this->averages[] = $ok;
        return $this;
    }

    function type($type)
    {
        $ok = $this->main->toTypeCode($type);
        if (false === $ok) {
            throw new \Exception('Unknown transaction type "' . $type . '"');
        }
        $this->types[] = $ok;
        return $this;
    }

    function reset()
    {
        $this->ages = $this->positions = $this->averages = $this->types = array();
        return $this;
    }

    function averagePrice($results)
    {
        $total = 0;
        if (count($results) == 0) return 0;
        foreach ($results as $result) {
            $total += $result['price'];
        }
        return $total/count($results);
    }

    function search()
    {
        $sql = 'SELECT * FROM transaction WHERE 1=1 ';
        if (count($this->ages)) {
            $sql .= ' AND age IN ("' . implode('","', $this->ages) . '")';
        }
        if (count($this->positions)) {
            $sql .= ' AND position IN ("' . implode('","', $this->positions) . '")';
        }
        if (count($this->averages)) {
            $sql .= ' AND average IN ("' . implode('","', $this->averages) . '")';
        }
        if (count($this->types)) {
            $sql .= ' AND type IN ("' . implode('","', $this->types) . '")';
        }
        if (false !== $this->minprice) {
            if (false !== $this->maxprice) {
                $sql .= ' AND price >= ' . $this->minprice . ' AND price <= ' . $this->maxprice;
            } else {
                $sql .= ' AND price >= ' . $this->minprice;
            }
        }
        if (false !== $this->mindate) {
            if (false !== $this->maxdate) {
                $sql .= ' AND UNIX_TIMESTAMP(stamp) >= ' . $this->mindate . ' AND UNIX_TIMESTAMP(stamp) <= ' . $this->maxdate;
            } else {
                $sql .= ' AND UNIX_TIMESTAMP(stamp) >= ' . $this->mindate;
            }
        }
        $sql .= ' ORDER BY stamp DESC';
        $result = $this->db->query($sql);
        if (!$result) {
            return array();
        }
        if (!$result->num_rows) {
            return array();
        }
        $ret = array();
        while ($transfer = $result->fetch_assoc()) {
            $ret[] = array(
                'timestamp' => $this->main->fromTimeStamp($transfer['stamp']),
                'average' => $transfer['average'],
                'price' => $transfer['price'],
                'type' => $this->main->fromTypeCode($transfer['type']),
                'age' => $transfer['age'],
                'position' => $transfer['position'],
                'url' => $transfer['url'],
            );
        }
        return $ret;
    }

    function listings(array $s)
    {
        $d = new \DateTime;
        $ret = '
    <table class="tablesorter" id="searchresultstable">
    <thead>
        <tr><th>Age</th><th>Pos.</th><th>Av.</th><th>Price</th><th>Date</th><th>Transaction Type</th></tr>
    </thead>
    <tbody>
    ';
        foreach ($s as $info) {
            $d->setTimestamp($info['timestamp']);
            $ret .= '<tr><td>' . $info['age'] . '</td><td>' . $info['position'] . '</td><td>' .
                $info['average'] . '</td><td>$' .
                number_format($info['price']) . '</td><td>' . $d->format('Y-m-d') . '</td><td>' .
                $info['type'] . '</td></tr>';
        }
        $ret .= '</tbody>
    </table>';
        return $ret;
    }

    function numPlayers()
    {
        $q = $this->db->query('SELECT COUNT(*) FROM player');
        $res = $q->fetch_array();
        return $res[0];
    }

    function numTransactions()
    {
        $q = $this->db->query('SELECT COUNT(*) FROM transaction');
        $res = $q->fetch_array();
        return $res[0];
    }
}
