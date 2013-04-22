<?php
include __DIR__ . '/../autoload.php';
$main = new SimilarTransactions\Main(51248, 51374);
$main->downloadLeagues();
?>
