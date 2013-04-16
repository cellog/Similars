<?php
include __DIR__ . '/../autoload.php';
$main = new SimilarTransactions\Main(51208, 51208);
$main->downloadLeagues();
?>
