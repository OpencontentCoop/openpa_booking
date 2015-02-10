<?php

/** @var eZModule $module */
$module = $Params['Module'];
$identifier = $Params['HandlerIdentifier'];
try
{
    $handler = OpenPABookingHandler::handler( $identifier, $Params );
    return $handler->add();
}
catch ( Exception $e )
{
    eZDebug::writeError( $e->getMessage(), __FILE__ );
    return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
}