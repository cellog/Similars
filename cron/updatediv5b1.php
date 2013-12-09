<?php
use SimilarTransactions\Main as a;
include __DIR__ . '/../autoload.php';
// div 5 376-886
$main = new SimilarTransactions\Main('user6', a::div5(3), a::div5(4) - 1);
$main->downloadLeagues(5);
?>
