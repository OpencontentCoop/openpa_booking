<?php


$Module = array(
    'name' => 'OpenPa Booking Module',
    'variable_params' => true
);


$ViewList['add'] = array(
    'functions' => array('book'),
    'script' => 'add.php',
    'params' => array('HandlerIdentifier'),
    'unordered_params' => array());

$ViewList['view'] = array(
    'functions' => array('book'),
    'script' => 'view.php',
    'params' => array('HandlerIdentifier'),
    'unordered_params' => array());

$FunctionList['book'] = array();



?>
