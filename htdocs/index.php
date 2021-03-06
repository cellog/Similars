<html lang="en">
 <head>
  <link href="/sm/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
  <style type="text/css" media="screen">
  table.tablesorter thead tr .headerSortUp {
     background-image: url(/sm/bootstrap/img/asc.gif);
    }
    table.tablesorter thead tr .headerSortDown {
     background-image: url(/sm/bootstrap/img/desc.gif);
    }
    table.tablesorter {
     background-color: #CDCDCD;
     margin:10px 0pt 15px;
     font-size: 8pt;
     width: 100%;
     text-align: left;
    }
    table.tablesorter thead tr th, table.tablesorter tfoot tr th {
     background-color: #e6EEEE;
     border: 1px solid #FFF;
     font-size: 8pt;
     padding: 4px;
    }
    table.tablesorter thead tr .header {
     background-image: url(/sm/bootstrap/img/bg.gif);
     background-repeat: no-repeat;
     background-position: center right;
     cursor: pointer;
    }
    table.tablesorter tbody td {
     color: #3D3D3D;
     padding: 4px;
     background-color: #FFF;
     vertical-align: top;
    }
    table.tablesorter tbody tr.odd td {
     background-color:#F0F0F6;
    }
    table.tablesorter thead tr .headerSortDown, table.tablesorter thead tr .headerSortUp {
     background-color: #8dbdd8;
    }
  </style>
 </head>
 <body>
<?php
include __DIR__ . '/../autoload.php';
$a = new SimilarTransactions\Main('user1', 'dummy');
$q = new SimilarTransactions\Player\Query($a);
echo '<pre>' . $q->numPlayers() . ' players processed, with ' . $q->numTransactions() . ' transactions</pre>';
if (isset($_GET) && isset($_GET['query'])) {
    if (isset($_GET['age'])) {
        foreach ($_GET['age'] as $age) {
            $q->age($age);
        }
    }
    if (isset($_GET['positions'])) {
        foreach($_GET['positions'] as $position) {
            $q->position($position);
        }
    }
    if (isset($_GET['type'])) {
        foreach ($_GET['type'] as $type) {
            $q->type($type);
        }
    }
    if (isset($_GET['average']) && $_GET['average']) {
        $a = $_GET['average'];
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
    if (isset($_GET['price']) && strlen($_GET['price'])) {
        $a = $_GET['price'];
        $a = str_replace(array('$',',','.'), array('', '', ''), $a);
        $a = explode('-', $a);
        if (count($a) == 2) {
            $min = $a[0];
            $max = $a[1];
            if ($min < $max) {
                $q->price($min)->price($max);
            }
        } elseif (count($a) == 1) {
            $q->price($a[0]);
        }
    }
    if (isset($_GET['datelow']) && strlen($_GET['datelow'])) {
        $q->date(strtotime($_GET['datelow'] . ' +1 day'));
    }
    if (isset($_GET['datehigh']) && strlen($_GET['datehigh'])) {
        $q->date(strtotime($_GET['datehigh'] . ' +1 day'));
    }
    echo '<pre>';
    $s = $q->search();
    echo "Average price: $", number_format(round($q->averagePrice($s)), 0);
    echo '</pre>';
    echo $q->listings($s);
}
?>
<form name="query" action="index.php" method="get">
    <input type="hidden" name="query" value="1">
    <p>Average: <input type="text" value="" name="average" placeholder="low-high or average"></p>
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
    <p>Price: <input type="text" name="price" placeholder="low-high or price"></p>
    <p>Start Date: <input type="text" name="datelow"> End Date: <input type="text" name="datehigh"></p>
    <input type="submit" value="Search">
</form>
  <script src="http://code.jquery.com/jquery-latest.js"></script>
  <script src="bootstrap/js/bootstrap.min.js"></script>
  <script src="bootstrap/js/jquery.tablesorter.min.js"></script>
  <script type="application/x-javascript">
  
$(document).ready(function() 
{ 
$.tablesorter.addParser({
        id: "monetaryValue",
        is: function (s) {
            var sp = s.replace(/,/, '.');
            var test = (/([�$�] ?\d+\.?\d*|\d+\.?\d* ?)/.test(sp)); //check currency with symbol
            return test;
        }, format: function (s) {
            return $.tablesorter.formatFloat(s.replace(new RegExp(/[^\d\.]/g), ""));
        }, type: "numeric"
    });
    $('#searchresultstable').tablesorter({
        widgets:['zebra'],
        textExtraction:function(node){
            return $(node).text();
        },
        headers: {
            3: { // 2 is column index
                sorter: "monetaryValue"
            }
        },
        sortList: [[4,1]]
    });
} 
);
  </script>
 </body>
</html>