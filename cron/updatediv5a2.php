<?php
use SimilarTransactions\Main as a;
include __DIR__ . '/../autoload.php';
// div 5 376-886
$main = new SimilarTransactions\Main('user4', a::div5(1), a::div5(2) - 1);
$main->downloadLeagues(5);
?>
