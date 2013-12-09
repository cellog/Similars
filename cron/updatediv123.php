<?php
use SimilarTransactions\Main as a;
include __DIR__ . '/../autoload.php';
$main = new SimilarTransactions\Main('user1', a::DIV1, a::div3());
$main->downloadLeagues(1);
?>
