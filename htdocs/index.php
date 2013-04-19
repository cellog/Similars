<?php
include __DIR__ . '/../autoload.php';
$a = new SimilarTransactions\Main('dummy');
$q = new SimilarTransactions\Player\Query($a);
echo '<pre>' . $q->numPlayers() . ' players processed, with ' . $q->numTransactions() . ' transactions</pre>';
if (isset($_POST) && isset($_POST['query'])) {
    if (isset($_POST['age'])) {
        foreach ($_POST['age'] as $age) {
            $q->age($age);
        }
    }
    if (isset($_POST['positions'])) {
        foreach($_POST['positions'] as $position) {
            $q->position($position);
        }
    }
    if (isset($_POST['type'])) {
        foreach ($_POST['type'] as $type) {
            $q->type($type);
        }
    }
    if (isset($_POST['average']) && $_POST['average']) {
        $a = $_POST['average'];
        $a = explode('-', $a);
        if (count($a) == 2) {
            $min = $a[0];
            $max = $a[1];
            if ($min < $max) {
                for ($i = $min; $i <= $max; $i++) {
                    $q->average($i);
                }
            }
        } elseif (count($a) == 1) {
            $q->average($a[0]);
        }
    }
    echo '<pre>';
    $s = $q->search();
    echo "Average price: $", number_format(round($q->averagePrice($s)), 0);
    echo $q->listings($s);
    echo '</pre>';
}
?>
<form name="query" action="index.php" method="post">
    <input type="hidden" name="query" value="1">
    <p>Average: <input type="text" value="" name="average" placeholder="low-high"></p>
    Age: <select name="age[]" multiple="yes" size="3">
        <option>14</option>
        <option>15</option>
        <option>16</option>
        <option>17</option>
        <option>18</option>
        <option>19</option>
        <option>20</option>
        <option>21</option>
        <option>22</option>
        <option>23</option>
        <option>24</option>
        <option>25</option>
        <option>26</option>
        <option>27</option>
        <option>28</option>
        <option>29</option>
        <option>30</option>
        <option>31</option>
        <option>32</option>
        <option>33</option>
        <option>34</option>
        <option>35</option>
        <option>36</option>
        <option>37</option>
        <option>38</option>
    </select>
    <select name="positions[]" multiple="yes" size="10">
        <option>GK</option>
        <option>LB</option>
        <option>LDF</option>
        <option>CDF</option>
        <option>RDF</option>
        <option>RB</option>
        <option>DFM</option>
        <option>LM</option>
        <option>LIM</option>
        <option>IM</option>
        <option>RIM</option>
        <option>RM</option>
        <option>OM</option>
        <option>LW</option>
        <option>LF</option>
        <option>CF</option>
        <option>RF</option>
        <option>RW</option>
    </select>
    <select name="type[]" multiple="yes">
        <option selected="yes">Auction</option>
        <option selected="yes">Transfer agreement</option>
        <option selected="yes">Direct Purchase</option>
        <option selected="yes">Hostile Clause</option>
    </select>
    <input type="submit" value="Search">
</form>