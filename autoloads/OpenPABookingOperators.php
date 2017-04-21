<?php

class OpenPABookingOperators
{
    /**
     * Returns the list of template operators this class supports
     *
     * @return array
     */
    function operatorList()
    {
        return array(
            'booking_states',
            'booking_state_colors',
            'booking_root_node',
            'location_node_id',
            'location_class_identifiers',
            'stuff_node_id',
            'stuff_class_identifiers',
            'stuff_sub_workflow_is_enabled'
        );
    }

    /**
     * Indicates if the template operators have named parameters
     *
     * @return bool
     */
    function namedParameterPerOperator()
    {
        return true;
    }

    /**
     * Returns the list of template operator parameters
     *
     * @return array
     */
    function namedParameterList()
    {
        return array(

        );
    }


    /**
     * Executes the template operator
     *
     * @param eZTemplate $tpl
     * @param string $operatorName
     * @param mixed $operatorParameters
     * @param string $rootNamespace
     * @param string $currentNamespace
     * @param mixed $operatorValue
     * @param array $namedParameters
     * @param mixed $placement
     */
    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement )
    {        
        switch( $operatorName )
        {

            case 'location_class_identifiers':
                $service = new ObjectHandlerServiceControlBookingSalaPubblica();
                $operatorValue = $service->salaPubblicaClassIdentifiers();
                break;

            case 'location_node_id':
                $operatorValue = OpenPABooking::locationsNodeId();
                break;

            case 'stuff_class_identifiers':
                $operatorValue = ObjectHandlerServiceControlBookingSalaPubblica::stuffClassIdentifiers();
                break;

            case 'stuff_node_id':
                $operatorValue = OpenPABooking::stuffNodeId();
                break;

            case 'booking_root_node':
                $operatorValue = OpenPABooking::instance()->rootNode();
                break;

            case 'booking_states':
                $operatorValue = ObjectHandlerServiceControlBooking::getStates();
                break;

            case 'booking_state_colors':
                $colors = array();
                $stateColors = ObjectHandlerServiceControlBooking::getStateColors();
                foreach($stateColors as $index => $stateColor){
                    if (is_numeric($index)) {
                        $colors[ObjectHandlerServiceControlBooking::getStateIdentifierFromCode($index)] = $stateColor;
                    }
                }
                $operatorValue = $colors;
                break;

            case 'stuff_sub_workflow_is_enabled':
                $operatorValue = OpenPABooking::instance()->isStuffSubWorkflowEnabled();
                break;
        }
    }
}
