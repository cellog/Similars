<?php
namespace SimilarTransactions\Player;
use SimilarTransactions\Main;
class Query
{
    protected $db;
    protected
        $main,
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

    function average($average)
    {
        if (!filter_var($average, \FILTER_VALIDATE_FLOAT) || $average < 1 || $average > 99) {
            throw new \Exception('Invalid average "' . $average . '"');
        }
        $this->averages[] = $average;
        return $this;
    }

    function type($type)
    {
        $ok = $this->main->toTypeCode($type);
        if (!$ok) {
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

    function search()
    {
        $sql = 'SELECT * FROM transaction WHERE 1=1 ';
        if (count($this->age)) {
            $sql .= ' AND age IN ("' . implode('","', $this->age) . '")';
        }
        if (count($this->position)) {
            $sql .= ' AND position IN ("' . implode('","', $this->position) . '")';
        }
        if (count($this->average)) {
            $sql .= ' AND average IN ("' . implode('","', $this->average) . '")';
        }
        if (count($this->type)) {
            $sql .= ' AND type IN ("' . implode('","', $this->type) . '")';
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
        while (false !== ($transfer = $result->fetch_assoc())) {
            $ret[] = array(
                'timestamp' => $this->main->fromTimeStamp($transfer['stamp']),
                'average' => $transfer['average'],
                'price' => $transfer['price'],
                'type' => $this->main->fromTypeCode($transfer['type']),
                'url' => $transfer['url'],
            );
        }
        return $ret;
    }
}
