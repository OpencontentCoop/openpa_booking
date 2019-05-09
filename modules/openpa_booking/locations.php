<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$id = $Params['ID'];

if ( is_numeric( $id ))
{
    $contentModule = eZModule::exists( 'content' );
    $result = $contentModule->run(
        'view',
        array( 'full', $id )
    );

    if (isset($result['content_info']['class_identifier'])){
        $booking = new ObjectHandlerServiceControlBookingSalaPubblica();
        if(in_array($result['content_info']['class_identifier'], $booking->bookableClassIdentifiers())){
            return $result;
        }
    }elseif (isset($result['errorCode'])) {
        return $result;
    }    

    return $module->redirectTo('/');
}
else
{
    if ($Params['FunctionName'] == 'stuff'){
        if (!OpenPABooking::instance()->isStuffBookingEnabled()){
            return $module->redirectTo('/');
        }
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
