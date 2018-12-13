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
            'stuff_sub_workflow_is_enabled',
            'openpa_agenda_link',
            'booking_vat_type_list', // deprecated
            'booking_is_in_range', // deprecated
            'booking_calc_price', // deprecated,
            'booking_range_list',
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
            'booking_is_in_range' => array(
                'from_time' => array('type' => 'integer', 'required' => true),
                'to_time' => array('type' => 'integer', 'required' => true),
                'range_string' => array('type' => 'string', 'required' => true),
            ),
            'booking_calc_price' => array(
                'price_row' => array('type' => 'mixed', 'required' => true),
            ),
            'booking_range_list' => array(
                'object' => array('type' => 'object', 'required' => true),
                'from_time' => array('type' => 'integer', 'required' => false, 'default' => null),
                'to_time' => array('type' => 'integer', 'required' => false, 'default' => null),
            )
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
            
            case 'booking_range_list':
                $list = array();
                if ($namedParameters['object'] instanceof eZContentObject){
                    $rangeHandler = OpenPABookingPriceRange::instance($namedParameters['object']);
                    if ($rangeHandler->hasPriceRangeDefinition()){
                        $list = $rangeHandler->getRangeList((int)$namedParameters['from_time'], (int)$namedParameters['to_time']);
                    }
                }

                $operatorValue = $list;
            break;

            // deprecated
            case 'booking_calc_price':
                $row = $namedParameters['price_row'];
                $priceValue = $row['columns'][2];
                $vatIncluded = $row['columns'][3] == '1';
                $vatType = eZVatType::fetch((int)$row['columns'][4]);
                $VATPercent = $vatType instanceof eZVatType ? $vatType->attribute('percentage') : 0;
                if ($vatIncluded){
                    $operatorValue = $priceValue / ( $VATPercent + 100 ) * 100;                    
                }else{
                    $operatorValue = $priceValue * ( $VATPercent + 100 ) / 100;
                }
                break;

            // deprecated
            case 'booking_is_in_range':
                $operatorValue = OpenPABookingPriceRange::isBookingInRange(
                    $namedParameters['from_time'],
                    $namedParameters['to_time'],
                    $namedParameters['range_string']
                );
                break;

            // deprecated
            case 'booking_vat_type_list':
                $operatorValue = eZVatType::fetchList( true, true );
                break;

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
                $operatorValue = ObjectHandlerServiceControlBookingSalaPubblica::getStates();
                break;

            case 'booking_state_colors':
                $colors = array();
                $stateColors = ObjectHandlerServiceControlBookingSalaPubblica::getStateColors();
                foreach($stateColors as $index => $stateColor){
                    if (is_numeric($index)) {
                        $colors[ObjectHandlerServiceControlBookingSalaPubblica::getStateIdentifierFromCode($index)] = $stateColor;
                    }else{
                        $colors[$index] = $stateColor;
                    }
                }
                $operatorValue = $colors;
                break;

            case 'stuff_sub_workflow_is_enabled':
                $operatorValue = OpenPABooking::instance()->isStuffSubWorkflowEnabled();
                break;

            case 'openpa_agenda_link':
                $operatorValue = $this->findOpenpaAgendaLink();
                break;
        }
    }

    private function findOpenpaAgendaLink()
    {
        $link = false;
        $siteAccessName = OpenPABase::getCustomSiteaccessName('agenda');
        $extensions = eZSiteAccess::getIni( $siteAccessName )->variable('ExtensionSettings', 'ActiveAccessExtensions');
        if (in_array('openpa_agenda', $extensions)){
            $link = '//' . eZSiteAccess::getIni( $siteAccessName )->variable('SiteSettings', 'SiteURL') . '/editorialstuff/add/agenda';
        }
        return $link;
    }
}
