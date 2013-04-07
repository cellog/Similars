<?php
namespace SimilarTransactions;
function autoload($class)
{
    include __DIR__ . '/src' . str_replace(array(__NAMESPACE__, '\\'), array('', '/'), $class) . '.php';
}
spl_autoload_register(__NAMESPACE__ . '\\autoload');
?>