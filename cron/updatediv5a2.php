<?php
include __DIR__ . '/../autoload.php';
// div 5 376-886
$main = new SimilarTransactions\Main(51478, 51576);
$main->downloadLeagues();
?>
