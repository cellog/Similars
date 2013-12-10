<?php
use SimilarTransactions\Main as a;
include __DIR__ . '/../autoload.php';
$main = new SimilarTransactions\Main('user2', a::div4(0), a::div4(1));
$main->downloadLeagues(1);
?>
