<?php

class ObjectHandlerServiceControlBookingAppuntamentoSindaco extends ObjectHandlerServiceControlBooking
{
    function run()
    {
        $this->fnData['is_valid_sindaco'] = 'isValidSindaco';
        $this->fnData['time_slots'] = 'getTimeSlots';
        $this->fnData['grouped_time_slots'] = 'getGroupedTimeSlots';
        parent::run();
    }

    protected function isValidSindaco()
    {
        $isValid = false;
        if ( $this->container->getContentObject() instanceof eZContentObject )
        {
            if (
                isset( $this->container->attributesHandlers['booking_start'] )
                && isset( $this->container->attributesHandlers['booking_end'] )
                && isset( $this->container->attributesHandlers['booking_timetable'] )
                && isset( $this->container->attributesHandlers['booking_slot'] )
                && isset( $this->container->attributesHandlers['approvers'] )
            )
            {
                if (
                    $this->container->attributesHandlers['booking_start']->attribute( 'has_content' )
                    && $this->container->attributesHandlers['booking_end']->attribute( 'has_content' )
                    && $this->container->attributesHandlers['booking_timetable']->attribute( 'has_content' )
                    && $this->container->attributesHandlers['booking_slot']->attribute( 'has_content' )
                    && $this->container->attributesHandlers['approvers']->attribute( 'has_content' )
                )
                {
                    $isValid = true;
                }
            }
        }
        return $isValid;
    }

    function getTimeSlots()
    {
        $slots = array();
        if ( $this->isValidSindaco() )
        {
            try
            {
                $node = $this->container->getContentNode();

                if ( $node instanceof eZContentObjectTreeNode )
                {
                    /** @var OpenPACalendarItem[] $splittableSlots */
                    $splittableSlots = OpenPACalendarTimeTable::getEvents(
                        $node,
                        array(
                            'search_from_timestamp' => $this->container->attributesHandlers['booking_start']->attribute(
                                'contentobject_attribute'
                            )->toString(),
                            'search_to_timestamp' => $this->container->attributesHandlers['booking_end']->attribute(
                                'contentobject_attribute'
                            )->toString()
                        ),
                        'booking_timetable'
                    );
                    $timeSlot = $this->container->attributesHandlers['booking_slot']->attribute(
                        'contentobject_attribute'
                    )->toString();
                    $interval = new DateInterval( 'PT' . $timeSlot . 'M' );

                    foreach ( $splittableSlots as $splittable )
                    {
                        /** @var DateTime[] $bySlot */
                        $bySlot = new DatePeriod(
                            $splittable->attribute( 'fromDateTime' ),
                            $interval,
                            $splittable->attribute( 'toDateTime' )
                        );
                        foreach ( $bySlot as $slot )
                        {
                            $startSlot = clone $slot;
                            $endSlot = $slot->add( $interval );

                            $singleSlot = array(
                                'referrer_contentobject_id' => $this->container->getContentObject()->attribute( 'id' ),
                                'fromDateTime' => $startSlot,
                                'from' => $startSlot->getTimestamp(),
                                'toDateTime' => $endSlot,
                                'to' => $endSlot->getTimestamp()
                            );
                            $slots[] = new AppuntamentoSindacoTimeSlot( $singleSlot );
                        }
                    }
                }
            }
            catch( Exception $e )
            {
                eZDebug::writeError( $e->getMessage(), __METHOD__ );
            }
        }
        return $slots;
    }

    function getGroupedTimeSlots()
    {
        $eventsByDay = array();
        if ( $this->isValidSindaco() )
        {
            try
            {
                $slots = $this->getTimeSlots();
                $byDayInterval = new DateInterval( 'P1D' );
                $startDateTime = DateTime::createFromFormat(
                    'U',
                    $this->container->attributesHandlers['booking_start']->attribute(
                        'contentobject_attribute'
                    )->toString(),
                    OpenPACalendarData::timezone()
                );
                $endDateTime = DateTime::createFromFormat(
                    'U',
                    $this->container->attributesHandlers['booking_end']->attribute(
                        'contentobject_attribute'
                    )->toString(),
                    OpenPACalendarData::timezone()
                );
                /** @var DateTime[] $byDayPeriod */
                $byDayPeriod = new DatePeriod( $startDateTime, $byDayInterval, $endDateTime );
                foreach ( $byDayPeriod as $date )
                {
                    $identifier = $date->format( OpenPACalendarData::FULLDAY_IDENTIFIER_FORMAT );
                    $calendarDay = new OpenPACalendarDay( $identifier );
                    $calendarDay->addEvents( $slots );
                    $eventsByDay[$identifier] = $calendarDay;
                }
            }
            catch( Exception $e )
            {
                eZDebug::writeError( $e->getMessage(), __METHOD__ );
            }
        }
        return $eventsByDay;
    }

    public function getApproverIds()
    {
        if ( $this->container->getContentObject()->attribute( 'main_parent_node_id' ) )
        {
            $currentObject = eZContentObject::fetchByNodeID( $this->container->getContentObject()->attribute( 'main_parent_node_id' ) );
            $openpaObject = OpenPAObjectHandler::instanceFromContentObject( $currentObject );
            $service = $openpaObject->service( 'control_booking_appuntamento_sindaco' );
            if ( $service instanceof ObjectHandlerServiceControlBookingAppuntamentoSindaco && $service->isValidSindaco() )
            {
                return explode( '-', $service->container->attributesHandlers['approvers']->attribute(
                    'contentobject_attribute'
                )->toString() );
            }

        }
        return array( 14 );
    }

    public function getObserversIds()
    {
        return array();
    }

    public function isValidDate( $start, $end, eZContentObject $parentObject )
    {
        // check remoteID
        $remote = AppuntamentoSindacoTimeSlot::generateBookingRemoteId( $parentObject->attribute( 'id' ), $start, $end );
        if ( $remote ) return false;

        // check timeslots

        return $start > 0 && $end > 0;
    }

    public function prenotazioneClassIdentifier()
    {
        return 'prenotazione_appuntamento_sindaco';
    }

    public function createObject( eZContentObject $parentObject, $start, $end )
    {
        $object = null;
        if ( $parentObject->attribute( 'class_identifier' ) == 'sindaco' )
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
                $object->setAttribute( 'section_id', OpenPABase::initSection( 'Prenotazioni', 'booking' )->attribute( 'id' ) );
                $object->setAttribute( 'remote_id', AppuntamentoSindacoTimeSlot::generateBookingRemoteId( $parentObject->attribute( 'id' ), $start, $end ) );
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
            }
            else
            {
                throw new Exception( "Impossibile creare oggetto di classe $classIdentifier" );
            }
        }
        return $object;
    }

    public static function init( $options = array() )
    {
        $self = new self();
        $classes = array(
            'sindaco',
            $self->prenotazioneClassIdentifier()
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
        $sindacoClass = eZContentClass::fetchByIdentifier( 'sindaco' );

        $roleName = 'Prenotazione appuntamento sindaco';
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
                'ModuleName' => 'content',
                'FunctionName' => 'create',
                'Limitation' => array(
                    'Class' => $prenotazioneClass->attribute( 'id' ),
                    'ParentClass' => $sindacoClass->attribute( 'id' )
                )
            );
            $policies[] =  array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Owner' => 1,
                    'Class' => $prenotazioneClass->attribute( 'id' ),
                    'Section' => OpenPABase::initSection( 'Prenotazioni', 'booking' )->attribute( 'id' )
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

    public function templateDirectory()
    {
        return 'appuntamento_sindaco';
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

}

class AppuntamentoSindacoTimeSlot extends OpenPACalendarItem
{
    public function __construct( $data )
    {
        $this->data = $data;
        $this->data['identifier'] = $this->data['remote_id'] = self::generateBookingRemoteId( $this->data['referrer_contentobject_id'], $this->data['from'], $this->data['to'] );
        $booked = eZContentObject::fetchByRemoteID( $this->data['remote_id'] );
        $this->data['busy'] = $booked ? $booked : false;
    }

    public static function generateBookingRemoteId( $objectId, $fromTimestamp, $toTimestamp )
    {
        return implode( '-', array( $objectId, $fromTimestamp, $toTimestamp ) );
    }
}
