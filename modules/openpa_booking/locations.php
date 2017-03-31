<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$id = $Params['ID'];

if ( is_numeric( $id ))
{
    $contentModule = eZModule::exists( 'content' );
    return $contentModule->run(
        'view',
        array( 'full', $id )
    );
}
else
{
    if ($Params['FunctionName'] == 'stuff'){
        $nodeId = OpenPABooking::stuffNodeId();
    }else{
        $nodeId = OpenPABooking::locationsNodeId();
    }

    $contentModule = eZModule::exists( 'content' );
    return $contentModule->run(
        'view',
        array( 'full', $nodeId )
    );
}
