<?php


$Module = array(
    'name' => 'OpenPa Booking Module',
    'variable_params' => true
);


$ViewList['add'] = array(
    'functions' => array('book'),
    'script' => 'add.php',
    'params' => array('HandlerIdentifier'),
    'unordered_params' => array()
);

$ViewList['view'] = array(
    'functions' => array('book'),
    'script' => 'view.php',
    'params' => array('HandlerIdentifier'),
    'unordered_params' => array()
);

$ViewList['home'] = array(
    'functions' => array('read'),
    'script' => 'home.php',
    'params' => array(),
    'unordered_params' => array()
);

$ViewList['info'] = array(
    'functions' => array('read'),
    'script' => 'info.php',
    'params' => array('Page'),
    'unordered_params' => array()
);

$ViewList['locations'] = array(
    'functions' => array('read'),
    'script' => 'locations.php',
    'params' => array('ID'),
    'unordered_params' => array()
);

$ViewList['stuff'] = array(
    'functions' => array('read'),
    'script' => 'locations.php',
    'params' => array('ID'),
    'unordered_params' => array()
);

$ViewList['config'] = array(
    'script' => 'config.php',
    'params' => array("Part"),
    'unordered_params' => array('offset' => 'Offset'),
    'functions' => array('config')
);

$ViewList["shop_register"] = array(
    "functions" => array('book'),
    "script" => "shop_register.php",
    'ui_context' => 'edit',
    "default_navigation_part" => 'ezshopnavigationpart',
    'single_post_actions' => array(
        'StoreButton' => 'Store',
        'CancelButton' => 'Cancel'
    )
);

$ViewList["invoice"] = array(
    "functions" => array('book'),
    "script" => "invoice.php",
    'params' => array('OrderID'),
);

$ViewList['export'] = array(
    'script' => 'export.php',
    'params' => array('Query'),
    'unordered_params' => array(),
    'functions' => array('export')
);

$ViewList['edit'] = array(
    'script' => 'edit.php',
    'params' => array('HandlerIdentifier'),
    'unordered_params' => array(),
    'functions' => array('edit')
);

$ViewList['trash'] = array(
    'script' => 'trash.php',
    'params' => array('HandlerIdentifier'),
    'unordered_params' => array(),
    'functions' => array('edit')
);

$ViewList['calendar'] = array(
    'script' => 'calendar.php',
    'params' => array('HandlerIdentifier'),
    'unordered_params' => array(),
    'functions' => array('calendar')
);


$FunctionList['read'] = array();
$FunctionList['book'] = array();
$FunctionList['config'] = array();
$FunctionList['export'] = array();
$FunctionList['edit'] = array();
$FunctionList['calendar'] = array();
