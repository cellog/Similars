<?php
use SimilarTransactions\Main as a;
include __DIR__ . '/../autoload.php';
// div 5 376-886
$main = new SimilarTransactions\Main('user5', a::div5(2), a::div5(3) - 1);
$main->downloadLeagues(5);
?>
