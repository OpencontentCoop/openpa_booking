<?php

class OpenPABookingHandler
{
    /**
     * @param string $identifier
     * @param array
     * @return OpenPABookingHandlerInterface
     * @throws Exception
     */
    public static function handler( $identifier, $Params )
    {
        $className = false;
        $handlers = OpenPAINI::variable( 'BookingHandlers', 'Handlers', array() );
        if ( isset( $handlers[$identifier] ) )
        {
            $className = $handlers[$identifier];
        }
        if ( class_exists( $className ) )
        {
            $handler = new $className( $Params );
            if ( $handler instanceof OpenPABookingHandlerInterface )
            {
                return $handler;
            }
            throw new Exception( "Booking handler $className must implement OpenPABookingHandlerInterface" );
        }
        throw new Exception( "Booking handler class '$className' not found" );
    }

    public static function executeWorkflow( $parameters, $process, $event )
    {
        $handlers = OpenPAINI::variable( 'BookingHandlers', 'Handlers', array() );
        foreach( $handlers as $identifier => $className )
        {
            $handler = new $className( array() );
            if ( $handler instanceof OpenPABookingHandlerInterface )
            {
                $handler->workflow( $parameters, $process, $event );
            }
        }
    }
}