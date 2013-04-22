<?php
include __DIR__ . '/../autoload.php';
$main = new SimilarTransactions\Main(51206, 51246);
$main->downloadLeagues();
?>
