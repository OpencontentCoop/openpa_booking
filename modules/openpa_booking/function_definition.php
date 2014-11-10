<?php
$FunctionList = array();

$FunctionList['handlers'] = array(
    'name' => 'handlers',
    'operation_types' => array( 'read' ),
    'call_method' => array(
        'include_file' => 'extension/openpa_booking/classes/openpabookingfunctioncollection.php',
        'class' => 'OpenPABookingFunctionCollection',
        'method' => 'fetchHandlers' ),
    'parameter_type' => 'standard',
    'parameters' => array()
);