<?php
use SimilarTransactions\Main as a;
include __DIR__ . '/../autoload.php';
// div 5 376-886
$main = new SimilarTransactions\Main('user7', a::div5(4), a::div5(5));
$main->downloadLeagues(1);
?>
