<?php

class OpenPABookingType extends eZWorkflowEventType
{

    const WORKFLOW_TYPE_STRING = 'openpabooking';

    function __construct()
    {
        $this->eZWorkflowEventType(
            self::WORKFLOW_TYPE_STRING,
            ezpI18n::tr( 'openpa/workflow/event', 'Workflow Prenotazioni' )
        );
    }

    function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );

        try
        {
            OpenPABookingHandler::executeWorkflow( $parameters, $process, $event );
            return eZWorkflowType::STATUS_ACCEPTED;
        }
        catch( Exception $e )
        {
            eZDebug::writeError( $e->getMessage(), __METHOD__ );
            return eZWorkflowType::STATUS_REJECTED;
        }

    }
}

eZWorkflowEventType::registerEventType( OpenPABookingType::WORKFLOW_TYPE_STRING, 'OpenPABookingType' );
