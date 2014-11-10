<?php

class ObjectHandlerServiceControlBookingSalaPubblica extends ObjectHandlerServiceBase
{
    const STATUS_PENDING = 0;
    const STATUS_WAITING_FOR_CHECKOUT = 1;
    const STATUS_WAITING_FOR_PAYMENT = 2;
    const STATUS_APPROVED = 3;
    const STATUS_DENIED = 4;
    const STATUS_EXPIRED = 5;

    protected static $stateGroupIdentifier = 'booking';

    protected static $stateIdentifiers = array(
        'in_attesa_di_approvazione',
        'in_attesa_di_pagamento',
        'in_attesa_di_verifica_pagamento',
        'confermato',
        'rifutato',
        'scaduto'
    );

    function run()
    {
        $this->data['is_valid'] = $this->isValid();
        $this->data['current_state_code'] = $this->getCurrentStateCode();
        $this->data['current_state'] = $this->getCurrentState();
        $this->data['sala'] = $this->getSala();
        $this->data['reservation_manager_ids'] = $this->getIdReferentSala();
        $this->data['start'] = $this->getStartDateTime();
        $this->data['end'] = $this->getEndDateTime();
        $this->data['timeslot_count'] = $this->getTimeSlotCount();
        $this->data['all_day'] = false; //@todo
        $this->data['collaboration_item'] = $this->getCollaborationItem();
    }

    protected function getTimeSlotCount()
    {
        $e = new DateTime('00:00');
        $f = clone $e;
        $e->add( $this->data['end']->diff( $this->data['start'] ) );
        $hourMinutes = $f->diff( $e )->format( "%h,%i" ); //@todo
        return round( $hourMinutes );
    }

    protected function getIdReferentSala()
    {
        $sala = $this->data['sala'];
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


    /**
     * @return eZCollaborationItem|null
     */
    protected function getCollaborationItem()
    {
        $data = null;
        if ( $this->isValid() )
        {
            $data = eZPersistentObject::fetchObject(
                eZCollaborationItem::definition(),
                null,
                array( 'data_int1' => $this->container->getContentObject()->attribute( 'id' ) ),
                true
            );
        }
        return $data;
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

    protected function isValid()
    {
        return $this->container->getContentObject() instanceof eZContentObject && $this->container->getContentObject()->attribute( 'class_identifier' ) == self::prenotazioneClassIdentifier();
    }

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

    protected function getCurrentState()
    {
        return self::getStateObject( $this->getCurrentStateCode() );
    }

    protected function getCurrentStateCode()
    {
        $current = self::STATUS_PENDING;
        if ( $this->isValid() )
        {
            $states = OpenPABase::initStateGroup( self::$stateGroupIdentifier, self::$stateIdentifiers );
            $currentStates = $this->container->getContentObject()->attribute( 'state_id_array' );
            foreach( $states as $state )
            {
                if ( in_array( $state->attribute( 'id' ), $currentStates  ) )
                {
                    switch( $state->attribute( 'identifier' ) )
                    {
                        case 'in_attesa_di_approvazione':
                            $current = self::STATUS_PENDING; break;

                        case 'in_attesa_di_pagamento':
                            $current =  self::STATUS_WAITING_FOR_CHECKOUT; break;

                        case 'in_attesa_di_verifica_pagamento':
                            $current =  self::STATUS_WAITING_FOR_PAYMENT; break;

                        case 'confermato':
                            $current =  self::STATUS_APPROVED; break;

                        case 'rifutato':
                            $current = self::STATUS_DENIED; break;

                        case 'scaduto':
                            $current = self::STATUS_EXPIRED; break;
                    }
                }
            }
        }
        return $current;
    }

    public static function getStateObject( $stateCode )
    {
        $states = OpenPABase::initStateGroup( self::$stateGroupIdentifier, self::$stateIdentifiers );
        $stateObject = null;
        foreach( $states as $state )
        {
            switch( $stateCode )
            {
                case self::STATUS_PENDING:
                {
                    if ( $state->attribute( 'identifier' ) == 'in_attesa_di_approvazione' )
                    {
                        $stateObject = $state;
                    }
                } break;

                case self::STATUS_WAITING_FOR_CHECKOUT:
                {
                    if ( $state->attribute( 'identifier' ) == 'in_attesa_di_pagamento' )
                    {
                        $stateObject = $state;
                    }
                } break;

                case self::STATUS_WAITING_FOR_PAYMENT:
                {
                    if ( $state->attribute( 'identifier' ) == 'in_attesa_di_verifica_pagamento' )
                    {
                        $stateObject = $state;
                    }
                } break;

                case self::STATUS_APPROVED:
                {
                    if ( $state->attribute( 'identifier' ) == 'confermato' )
                    {
                        $stateObject = $state;
                    }
                } break;

                case self::STATUS_DENIED:
                {
                    if ( $state->attribute( 'identifier' ) == 'rifutato' )
                    {
                        $stateObject = $state;
                    }
                } break;

                case self::STATUS_EXPIRED:
                {
                    if ( $state->attribute( 'identifier' ) == 'scaduto' )
                    {
                        $stateObject = $state;
                    }
                } break;
            }
        }
        return $stateObject;
    }

    public function changeState( $stateCode )
    {
        if ( $this->isValid())
        {
            $currentState = $this->getCurrentStateCode();
            if ( $currentState != $stateCode )
            {
                $params = array( 'before' => $currentState );
                $state = self::getStateObject( $stateCode );
                if ( $state instanceof eZContentObjectState )
                {
                    if ( eZOperationHandler::operationIsAvailable( 'content_updateobjectstate' ) )
                    {
                        eZOperationHandler::execute( 'content', 'updateobjectstate',
                            array( 'object_id' => $this->container->getContentObject()->attribute( 'id' ),
                                   'state_id_list' => array( $state->attribute( 'id' ) ) ) );
                    }
                    else
                    {
                        eZContentOperationCollection::updateObjectState( $this->container->getContentObject()->attribute( 'id' ), array( $state->attribute( 'id' ) ) );
                    }
                }
                if ( $this->getCurrentStateCode() == $stateCode )
                {
                    $params['after'] = $stateCode;
                    $this->notify( 'change_state', $params );
                }
            }
        }
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

    public function notify( $action, $params = array() )
    {
        $tpl = eZTemplate::factory();
        $tpl->setVariable( 'scope', $action );
        foreach( $params as $key => $value )
        {
            $tpl->setVariable( $key, $value );
        }
        $tpl->fetch( 'design:booking/sala_pubblica/messages.tpl' );

        $subject = $tpl->variable( 'email_subject' );
        $body = $tpl->variable( 'email_body' );
        $ccReceivers = $tpl->variable( 'email_cc_receivers' );
        $bccReceivers = $tpl->variable( 'email_bcc_receivers' );
        $replyTo = $tpl->variable( 'email_reply_to' );
        $messageText = $tpl->variable( 'comment' );

        $ini = eZINI::instance();

        // invia mail
        if ( trim( $subject ) != '' && trim( $body ) != '' )
        {
            $mail = new eZMail();

            if ( $tpl->hasVariable( 'content_type' ) )
                $mail->setContentType( $tpl->variable( 'content_type' ) );

            $owner = eZUser::fetch( $this->container->getContentObject()->attribute( 'owner_id' ) );
            $receiver = $owner instanceof eZUser ? $owner->attribute( 'email' ) : false;
            if ( !$mail->validate( $receiver ) )
            {
                $receiver = $ini->variable( "MailSettings", "AdminEmail" );
            }
            $mail->setReceiver( $receiver );

            $sender = $ini->variable( "MailSettings", "EmailSender" );
            $mail->setSender( $sender );

            if ( $mail->validate( $replyTo ) )
            {
                $replyTo = $ini->variable( "MailSettings", "EmailReplyTo" );
                if ( !$mail->validate( $replyTo ) )
                {
                    // If replyTo address is not set in the settings, use the sender address
                    $replyTo = $sender;
                }
            }
            $mail->setReplyTo( $replyTo );

            // Handle CC recipients
            if ( $ccReceivers )
            {
                if ( !is_array( $ccReceivers ) )
                    $ccReceivers = array( $ccReceivers );
                foreach ( $ccReceivers as $ccReceiver )
                {
                    if ( $mail->validate( $ccReceiver ) )
                        $mail->addCc( $ccReceiver );
                }
            }

            // Handle BCC recipients
            if ( $bccReceivers )
            {
                if ( !is_array( $bccReceivers ) )
                    $bccReceivers = array( $bccReceivers );

                foreach ( $bccReceivers as $bccReceiver )
                {
                    if ( $mail->validate( $bccReceiver ) )
                        $mail->addBcc( $bccReceiver );
                }
            }

            $mail->setSubject( $subject );
            $mail->setBody( $body );
            $mailResult = eZMailTransport::send( $mail );
        }

        // registra messaggio
        $collaborationItem = $this->getCollaborationItem();
        if ( $collaborationItem instanceof eZCollaborationItem && trim( $messageText ) != '' )
        {
            $message = eZCollaborationSimpleMessage::create( 'openpabookingapprove_comment', $body );
            $message->store();
            eZCollaborationItemMessageLink::addMessage( $collaborationItem, $message, OpenPABookingCollaborationHandler::MESSAGE_TYPE_APPROVE );
        }
    }

    public static function salaPubblicaClassIdentifier()
    {
        return 'sala_pubblica';
    }

    public static function prenotazioneClassIdentifier()
    {
        return 'prenotazione_sala';
    }

    public static function createObject( eZContentObject $parentObject, $start, $end )
    {
        $object = null;
        if ( $parentObject->attribute( 'class_identifier' ) == self::salaPubblicaClassIdentifier() )
        {
            $classIdentifier = self::prenotazioneClassIdentifier();
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

                if ( isset( $dataMap['sala'] ) )
                {
                    $dataMap['sala']->fromString( $parentObject->attribute( 'id' ) );
                    $dataMap['sala']->store();
                }

                if ( isset( $dataMap['price'] ) )
                {
                    /** @var eZContentObjectAttribute[] $parentObjectDataMap */
                    $parentObjectDataMap = $parentObject->attribute( 'data_map' );
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

    public static function isValidDate( $start, $end, $sala )
    {
        return true;
    }


}