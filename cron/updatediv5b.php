<?php
include __DIR__ . '/../autoload.php';
// div 5 376-886
$main = new SimilarTransactions\Main(51576, 51776);
$main->downloadLeagues();
?>
