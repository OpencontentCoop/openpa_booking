<?php

class OpenPABookingFunctionCollection
{
    public static function fetchHandlers()
    {
        $data = array();
        $handlers = OpenPAINI::variable( 'BookingHandlers', 'Handlers', array() );
        foreach( array_keys( $handlers ) as $identifier )
        {
            try
            {
                $handler = OpenPABookingHandler::handler( $identifier, array() );
                $data[] = array( 'name' => $handler::name(), 'identifier' => $handler::identifier() );
            }
            catch ( Exception $e )
            {
                eZDebug::writeError( $e->getMessage(), __METHOD__ );
            }
        }
        return array( "result" => $data );
    }
}