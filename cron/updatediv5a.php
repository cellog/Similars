<?php
use SimilarTransactions\Main as a;
include __DIR__ . '/../autoload.php';
// div 5 376-886
$main = new SimilarTransactions\Main('user3', a::div5(0), a::div5(1) - 1);
$main->downloadLeagues(1);
?>
