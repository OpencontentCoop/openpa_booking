<?php

class DataHandlerBookingSalaPubblica implements OpenPADataHandlerInterface
{
    /**
     * @var eZContentObjectTreeNode
     */
    protected $currentSalaNode;

    /**
     * @var eZContentObject
     */
    protected $currentSalaObject;

    public function __construct( array $Params )
    {
        $salaId = eZHTTPTool::instance()->getVariable( 'sala', false );
        $this->currentSalaObject = eZContentObject::fetch( intval( $salaId ) );
        if ( !$this->currentSalaObject instanceof eZContentObject )
        {
            throw new Exception( "Sala pubblica $salaId non trovata" );
        }
    }

    public function getData()
    {
        $data = array();
        $start = new DateTime(
            eZHTTPTool::instance()->getVariable( 'start', false ),
            new DateTimeZone( 'Europe/Rome' )
        );
        $end = new DateTime(
            eZHTTPTool::instance()->getVariable( 'end', false ),
            new DateTimeZone( 'Europe/Rome' )
        );

        $current = eZHTTPTool::instance()->getVariable( 'current', false );

        if ( $start instanceof DateTime && $end instanceof DateTime )
        {
            $filters = array();
            $dateFilter = array(
                'or',
                'attr_from_time_dt' => '[ ' .ezfSolrDocumentFieldBase::preProcessValue( $start->getTimestamp(), 'date' ) . ' TO ' . ezfSolrDocumentFieldBase::preProcessValue( $end->getTimestamp(), 'date' ) . ' ]',
                'attr_to_time_dt' => '[ ' .ezfSolrDocumentFieldBase::preProcessValue( $start->getTimestamp(), 'date' ) . ' TO ' . ezfSolrDocumentFieldBase::preProcessValue( $end->getTimestamp(), 'date' ) . ' ]'
            );
            $filters[] = $dateFilter;
            $sortBy = array( 'attr_from_time_dt' => 'desc', 'published' => 'desc' );
            $solrSearch = new eZSolr();
            $search = $solrSearch->search( '', array(
                'SearchSubTreeArray' => array( $this->currentSalaObject->attribute( 'main_node_id' ) ),
                'SearchLimit' => 1000,
                'SortBy' => $sortBy,
                'Filter' => $filters
            ) );
            foreach( $search['SearchResult'] as $node )
            {
                $openpaObject = OpenPAObjectHandler::instanceFromObject( $node );
                if ( $openpaObject->hasAttribute( 'control_booking_sala_pubblica' )
                     && $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'is_valid' ) )
                {
                    $state = ObjectHandlerServiceControlBookingSalaPubblica::getStateObject( $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'current_state_code' ) );
                    if ( $state instanceof eZContentObjectState )
                    {
                        $item = new stdClass();
                        $item->title = $state->attribute( 'current_translation' )->attribute( 'name' );
                        $item->start = $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'start' )->format( 'c' );
                        $item->end = $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'end' )->format( 'c' );
                        $item->allDay = $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'all_day' );
                        if ( $current && $node->attribute( 'contentobject_id' ) == $current )
                        {
                            $item->color = "#ff0000";
                            $item->title = $node->attribute( 'object' )->attribute( 'owner' )->attribute( 'name' );
                        }
                        $data[] = $item;
                    }

                }
            }
        }
        return $data;
    }
}