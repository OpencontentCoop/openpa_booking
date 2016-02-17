<?php

class ObjectHandlerServiceControlBookingSalaPubblica extends ObjectHandlerServiceControlBooking
{
    function run()
    {
        $this->fnData['sala'] = 'getSala';
        $this->fnData['has_manual_price'] = 'hasManualPrice';
        $this->fnData['timeslot_count'] = 'getTimeSlotCount';
        $this->data['all_day'] = false; //@todo
        $this->fnData['concurrent_requests'] = 'getConcurrentRequests';
        parent::run();
    }
    
    protected static function fetchConcurrentItems( DateTime $start, DateTime $end, $states = array(), $sala = array(), $id = null, $count = false )
    {
        $filters = array();
        if ( $id )
        {
            $filters['-meta_id_si'] = $id;
        }
        if ( !empty( $states ) )
        {
            $stateFilters = array();
            if ( count( $states ) > 1 )
            {
                $stateFilters = array( 'or' );
            }
            foreach( $states as $stateCode )
            {
                $state = self::getStateObject( $stateCode );
                if ( $state instanceof eZContentObjectState )
                {
                    $stateFilters[] = 'meta_object_states_si:' . $state->attribute( 'id' );
                }
            }
            if ( !empty( $stateFilters ) )
            {
                $filters[] = $stateFilters;
            }
        }
        $dateFilter = array(
            'or',
            array(
                'and',
                OpenPASolr::generateSolrField('from_time','date') => '[ ' . ezfSolrDocumentFieldBase::preProcessValue( $start->getTimestamp(), 'date' ) .' TO ' . ezfSolrDocumentFieldBase::preProcessValue( $end->getTimestamp(), 'date' ) . ' ]',
                OpenPASolr::generateSolrField('to_time','date') => '[ ' . ezfSolrDocumentFieldBase::preProcessValue( $start->getTimestamp(), 'date' ) .' TO ' . ezfSolrDocumentFieldBase::preProcessValue( $end->getTimestamp(), 'date' ) . ' ]'
            ),
            array(
                'and',
                OpenPASolr::generateSolrField('from_time','date') => '[ * TO ' . ezfSolrDocumentFieldBase::preProcessValue( $start->getTimestamp(), 'date' ) . ' ]',
                OpenPASolr::generateSolrField('to_time','date') => '[ ' . ezfSolrDocumentFieldBase::preProcessValue( $end->getTimestamp(), 'date' ) . ' TO * ]'
            ),
            array(
                'and',
                OpenPASolr::generateSolrField('from_time','date') => '[ ' . ezfSolrDocumentFieldBase::preProcessValue( $start->getTimestamp(), 'date' ) .' TO ' . ezfSolrDocumentFieldBase::preProcessValue( $end->getTimestamp(), 'date' ) . ' ]',
                OpenPASolr::generateSolrField('to_time','date') => '[ ' . ezfSolrDocumentFieldBase::preProcessValue( $end->getTimestamp(), 'date' ) . ' TO * ]'
            ),            
            array(
                'and',
                OpenPASolr::generateSolrField('from_time','date') => '[ * TO ' . ezfSolrDocumentFieldBase::preProcessValue( $start->getTimestamp(), 'date' ) . ' ]',
                OpenPASolr::generateSolrField('to_time','date') => '[ ' . ezfSolrDocumentFieldBase::preProcessValue( $start->getTimestamp(), 'date' ) .' TO ' . ezfSolrDocumentFieldBase::preProcessValue( $end->getTimestamp(), 'date' ) . ' ]'
            )
            ,            
            array(
                'and',
                OpenPASolr::generateSolrField('from_time','date') => '[' . ezfSolrDocumentFieldBase::preProcessValue( $start->getTimestamp(), 'date' ) . ' TO * ]',
                OpenPASolr::generateSolrField('to_time','date') => '[ * TO ' . ezfSolrDocumentFieldBase::preProcessValue( $end->getTimestamp(), 'date' ) . ' ]'
            )
        );
        $filters[] = $dateFilter;
        $sortBy = array( OpenPASolr::generateSolrField('from_time','date') => 'desc', 'published' => 'asc' );
        $solrSearch = new eZSolr();
        $search = $solrSearch->search( '', array(
            'SearchSubTreeArray' => $sala,            
            'SearchLimit' => $count ? 1 : 1000,
            'SortBy' => $sortBy,
            'Filter' => $filters
        ));
        return $count ? $search['SearchCount'] : $search['SearchResult'];
    }

    protected function getConcurrentRequests()
    {
        $data = array();
        if ( $this->isValid() )
        {
            $sala = array();
            if ( $this->getSala() instanceof eZContentObject )
            {
                $sala[] = $this->getSala()->attribute( 'main_node_id' );
            }
            $data = self::fetchConcurrentItems(
                $this->getStartDateTime(),
                $this->getEndDateTime(),
                array( self::STATUS_PENDING, self::STATUS_WAITING_FOR_CHECKOUT, self::STATUS_WAITING_FOR_PAYMENT ),
                $sala,
                $this->container->getContentObject()->attribute( 'id' )
            );
        }        
        return $data;
    }

    protected function getTimeSlotCount()
    {
        $e = new DateTime('00:00');
        $f = clone $e;
        $e->add( $this->getEndDateTime()->diff( $this->getStartDateTime() ) );
        $hourMinutes = $f->diff( $e )->format( "%h,%i" ); //@todo
        return round( $hourMinutes );
    }

    public function getApproverIds()
    {
        $sala = $this->getSala();
        if ( $sala instanceof eZContentObject )
        {
            /** @var eZContentObjectAttribute[] $salaDataMap */
            $salaDataMap = $sala->attribute( 'data_map' );
            if ( isset( $salaDataMap['reservation_manager'] ) )
            {
                return explode( '-', $salaDataMap['reservation_manager']->toString() );
            }
        }
        return array();
    }

    protected function getStartDateTime()
    {
        $date = new DateTime();
        if ( isset( $this->container->attributesHandlers['from_time'] ) )
        {
            $date->setTimestamp( $this->container->attributesHandlers['from_time']->attribute( 'contentobject_attribute' )->toString() );
        }
        return $date;
    }

    protected function getEndDateTime()
    {
        $date = new DateTime();
        if ( isset( $this->container->attributesHandlers['to_time'] ) )
        {
            $date->setTimestamp( $this->container->attributesHandlers['to_time']->attribute( 'contentobject_attribute' )->toString() );
        }
        return $date;
    }

    /**
     * @return eZContentObject
     */
    protected function getSala()
    {
        $sala = null;
        if ( $this->isValid() )
        {
            if ( isset( $this->container->attributesHandlers['sala'] ) )
            {
                $sala = $this->container->attributesHandlers['sala']->attribute( 'contentobject_attribute' )->attribute( 'content' );
            }
        }
        return $sala;
    }
    
    protected function hasManualPrice()
    {        
        $sala = $this->getSala();
        if ( $sala instanceof eZContentObject )
        {
            $salaDataMap = $sala->attribute( 'data_map' );
            if ( isset( $salaDataMap['manual_price'] ) && $salaDataMap['manual_price']->attribute( 'data_int' ) == 1 )
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        throw new Exception( "Sala non trovata" );
    }

    public function addOrder( eZOrder $order )
    {
        if ( isset( $this->container->attributesHandlers['order_id'] ) )
        {
            /** @var eZContentObjectAttribute $orderAttribute */
            $orderAttribute = $this->container->attributesHandlers['order_id']->attribute( 'contentobject_attribute' );
            $orderAttribute->fromString( $order->attribute( 'id' ) );
            $orderAttribute->store();
        }
        if ( $order->attribute( 'status' ) != eZOrderStatus::DELIVERED )
        {
            $this->changeState( self::STATUS_WAITING_FOR_PAYMENT );
        }
        else
        {
            $this->changeState( self::STATUS_APPROVED );
        }
    }

    public function templateDirectory()
    {
        return 'sala_pubblica';
    }

    public function salaPubblicaClassIdentifier()
    {
        return 'sala_pubblica';
    }

    public function prenotazioneClassIdentifier()
    {
        return 'prenotazione_sala';
    }

    public function createObject( eZContentObject $parentObject, $start, $end )
    {
        $object = null;
        if ( $parentObject->attribute( 'class_identifier' ) == $this->salaPubblicaClassIdentifier() )
        {
            $classIdentifier = $this->prenotazioneClassIdentifier();
            $class = eZContentClass::fetchByIdentifier( $classIdentifier );
            if ( !$class instanceof eZContentClass )
            {
                throw new Exception( "Classe $classIdentifier non trovata" );
            }

            $languageCode = eZINI::instance()->variable( 'RegionalSettings', 'Locale' );
            $object = eZContentObject::createWithNodeAssignment(
                $parentObject->attribute( 'main_node' ),
                $class->attribute( 'id' ),
                $languageCode,
                false );

            if ( $object )
            {

                /** @var eZContentObjectAttribute[] $parentObjectDataMap */
                $parentObjectDataMap = $parentObject->attribute( 'data_map' );
                
                $object->setAttribute( 'section_id', OpenPABase::initSection( 'Prenotazioni', 'booking' )->attribute( 'id' ) );
                $object->store();

                /** @var eZContentObjectAttribute[] $dataMap */
                $dataMap = $object->attribute( 'data_map' );

                if ( isset( $dataMap['from_time'] ) && $start )
                {
                    $dataMap['from_time']->fromString( $start );
                    $dataMap['from_time']->store();
                }

                if ( isset( $dataMap['to_time'] ) && $start )
                {
                    $dataMap['to_time']->fromString( $end );
                    $dataMap['to_time']->store();
                }
                
                if ( isset( $dataMap['stuff'] ) )
                {
                    if ( isset( $parentObjectDataMap['servizi'] ) )
                    {
                        $servizi = explode( ',', $parentObjectDataMap['servizi']->toString() );
                        $content = array();
                        foreach( $servizi as $servizio )
                        {
                            $content[] = trim( $servizio );
                        }
                        if ( !empty( $content ) )
                        {
                            $string = implode( "\n", $content );
                            $dataMap['stuff']->fromString( $string );   
                            $dataMap['stuff']->store();   
                        }                        
                    }
                }

                if ( isset( $dataMap['sala'] ) )
                {
                    $dataMap['sala']->fromString( $parentObject->attribute( 'id' ) );
                    $dataMap['sala']->store();
                }
                else
                {
                    throw new Exception( "Missing contentclass attribute 'sala' in {$classIdentifier} class" );
                }

                if ( isset( $dataMap['price'] ) )
                {                    
                    if ( isset( $parentObjectDataMap['price'] ) )
                    {
                        $dataMap['price']->fromString( $parentObjectDataMap['price']->toString() );
                        $dataMap['price']->store();
                    }
                }
            }
        }
        return $object;
    }

    public function setPrice( $price )
    {
        if ( $this->isValid() )
        {
            $dataMap = $this->container->getContentObject()->attribute( 'data_map' );
            $parts = explode( '|', $dataMap['price']->toString() );
            $parts[0] = $price;
            $dataMap['price']->fromString( implode( '|', $parts ) );
            $dataMap['price']->store();
            return true;
        }
        return false;
    }
    
    public function setOrderStatus( $status )
    {
        if ( $this->isValid() )
        {
            $dataMap = $this->container->getContentObject()->attribute( 'data_map' );
            $orderId = $dataMap['order_id']->toString();
            $order = eZOrder::fetch( $orderId );
            if ( $order instanceof eZOrder )
            {
                $order->modifyStatus( $status );                
                return true;
            }
        }
        return false;
    }
    
    /**
     * @param int $start unixtimestamp
     * @param int $end unixtimestamp
     * @param eZContentObject $sala
     *
     * @throws Exception
     */
    public function isValidDate( $start, $end, eZContentObject $sala )
    {
        $startDateTime = new DateTime( 'now', new DateTimeZone( 'Europe/Rome' ) );
        $startDateTime->setTimestamp( $start );

        $endDateTime = new DateTime( 'now', new DateTimeZone( 'Europe/Rome' ) );
        $endDateTime->setTimestamp( $end );

        $salaSubtree = array();
        if ( $sala instanceof eZContentObject )
        {
            $salaSubtree[] = $sala->attribute( 'main_node_id' );
        }

        $data = self::fetchConcurrentItems(
            $startDateTime,
            $endDateTime,
            array( self::STATUS_APPROVED ),
            $salaSubtree,
            null,
            true
        );
        if ( $data > 0 )
        {
            throw new Exception( "Giorno o orario non disponibile" );
        }
        
        $now = time();
        if ( $start < $now )
        {
            throw new Exception( "Giorno o orario non disponibile" );
        }
    }

    public static function init( $options = array() )
    {
        $self = new self();

        OpenPABase::initStateGroup( self::$stateGroupIdentifier, self::$stateIdentifiers );

        $section = OpenPABase::initSection( 'Prenotazioni', 'booking' );

        $classes = array(
            $self->prenotazioneClassIdentifier(),
            $self->salaPubblicaClassIdentifier()
        );

        foreach( $classes as $identifier )
        {
            $tools = new OpenPAClassTools( $identifier, true );
            if ( !$tools->isValid() )
            {
                $tools->sync( true );
                OpenPALog::warning( "La classe $identifier è stata aggiornata" );
            }
        }

        $prenotazioneClass = eZContentClass::fetchByIdentifier( $self->prenotazioneClassIdentifier() );
        $salaClass = eZContentClass::fetchByIdentifier( $self->salaPubblicaClassIdentifier() );

        $roleName = 'Prenotazione sala pubblica';
        $role = eZRole::fetchByName( $roleName );
        if ( !$role instanceof eZRole )
        {
            $role = eZRole::create( $roleName );
            $role->store();

            $policies = array();
            $policies[] =  array(
                'ModuleName' => 'collaboration',
                'FunctionName' => '*'
            );
            $policies[] =  array(
                'ModuleName' => 'openpa_booking',
                'FunctionName' => '*'
            );
            $policies[] =  array(
                'ModuleName' => 'openpa',
                'FunctionName' => 'data'
            );
            $policies[] =  array(
                'ModuleName' => 'shop',
                'FunctionName' => 'buy'
            );
            $policies[] =  array(
                'ModuleName' => 'content',
                'FunctionName' => 'create',
                'Limitation' => array(
                    'Class' => $prenotazioneClass->attribute( 'id' ),
                    'ParentClass' => $salaClass->attribute( 'id' )
                )
            );
            $policies[] =  array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Owner' => 1,
                    'Class' => $prenotazioneClass->attribute( 'id' ),
                    'Section' => $section->attribute( 'id' )
                )
            );

            foreach( $policies as $policy )
            {
                $role->appendPolicy( $policy['ModuleName'], $policy['FunctionName'], $policy['Limitation'] );
            }

            $defaultUserPlacement = (int)eZINI::instance()->variable( "UserSettings", "DefaultUserPlacement" );
            $membersGroup = eZContentObject::fetchByNodeID( $defaultUserPlacement );
            if ( $membersGroup instanceof eZContentObject )
            {
                $role->assignToUser( $membersGroup->attribute( 'id' )  );
            }
        }

        OpenPALog::error( "Attiva il workflow Prenotazioni in post_publish e in post_checkout" );
    }
}