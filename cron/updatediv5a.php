<?php
include __DIR__ . '/../autoload.php';
// div 5 376-886
$main = new SimilarTransactions\Main(51376, 51476);
$main->downloadLeagues();
?>
