<?php
include __DIR__ . '/../autoload.php';
$main = new SimilarTransactions\Main(51206, 51886);
$main->downloadLeagues();
?>
