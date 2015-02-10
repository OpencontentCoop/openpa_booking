<?php

abstract class ObjectHandlerServiceControlBooking extends ObjectHandlerServiceBase
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
        'rifiutato',
        'scaduto'
    );

    function run()
    {
        $this->fnData['is_valid'] = 'isValid';
        $this->fnData['current_state_code'] = 'getCurrentStateCode';
        $this->fnData['current_state'] = 'getCurrentState';
        $this->fnData['collaboration_item'] = 'getCollaborationItem';
        $this->data['state_colors'] = self::getStateColors();
        $this->fnData['reservation_manager_ids'] = 'getApproverIds';
        $this->fnData['start'] = 'getStartDateTime';
        $this->data['start_moment'] = $this->getStartDateTime()->format( 'c' );
        $this->data['start_timestamp'] = $this->getStartDateTime()->getTimestamp();
        $this->fnData['end'] = 'getEndDateTime';
        $this->data['end_timestamp'] = $this->getEndDateTime()->getTimestamp();
    }

    public static function getStateColors()
    {
        $data = array();
        $data[self::STATUS_APPROVED] = "#008000";
        $data[self::STATUS_DENIED] = "#666666";
        $data[self::STATUS_PENDING] = "#FF8000";
        $data[self::STATUS_EXPIRED] = "#000000";
        $data[self::STATUS_WAITING_FOR_CHECKOUT] = "#0000FF";
        $data[self::STATUS_WAITING_FOR_PAYMENT] = "#CC66FF";
        $data['current'] = '#ff0000';
        $data['none'] = '#cccccc';
        return $data;
    }

    /**
     * @return eZCollaborationItem|null
     */
    public function getCollaborationItem()
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
                    if ( $state->attribute( 'identifier' ) == 'rifiutato' )
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

                        case 'rifiutato':
                            $current = self::STATUS_DENIED; break;

                        case 'scaduto':
                            $current = self::STATUS_EXPIRED; break;
                    }
                }
            }
        }
        return $current;
    }

    public function changeState( $stateCode )
    {
        if ( $this->isValid())
        {
            $currentState = $this->getCurrentStateCode();
            if ( $currentState != $stateCode )
            {
                $params = array( 'state_before' => self::getStateObject( $currentState ) );
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
                $params['state_after'] = self::getStateObject( $stateCode );
                $this->notify( 'change_state', $params );
            }
        }
    }

    public function notify( $action, $params = array() )
    {
        $scopes = array( 'author', 'approver', 'messages' );

        $authorId = $this->container->getContentObject()->attribute( 'owner_id' );
        $approveIdArray = $this->getApproverIds();

        foreach( $scopes as $scope )
        {
            $tpl = eZTemplate::factory();
            $tpl->resetVariables();
            $tpl->setVariable( 'object', $this->container->getContentObject() );
            $tpl->setVariable( 'action', $action );
            $tpl->setVariable( 'scope', $scope );
            foreach( $params as $key => $value )
            {
                $tpl->setVariable( $key, $value );
            }
            $tpl->fetch( 'design:booking/' . $this->templateDirectory() . '/messages.tpl' );

            $subject = $tpl->variable( 'email_subject' );
            $body = $tpl->variable( 'email_body' );
            $replyTo = $tpl->variable( 'email_reply_to' );
            $messageText = $tpl->variable( 'comment' );

            $ini = eZINI::instance();

            if ( $scope == 'messages' )
            {
                // registra messaggio
                $collaborationItem = $this->getCollaborationItem();
                if ( $collaborationItem instanceof eZCollaborationItem && trim( $messageText ) != '' )
                {
                    $referenti = $this->getApproverIds();
                    $message = eZCollaborationSimpleMessage::create( 'openpabooking_comment', $messageText, $referenti[0] );
                    $message->store();
                    $messageLink = eZCollaborationItemMessageLink::addMessage( $collaborationItem, $message, OpenPABookingCollaborationHandler::MESSAGE_TYPE_APPROVE );
                    eZCollaborationItemStatus::setLastRead( $collaborationItem->attribute( 'id' ), eZUser::currentUserID(), $messageLink->attribute( 'modified' ) + 1 );
                }
            }

            if ( trim( $subject ) != '' && trim( $body ) != '' )
            {
                $mail = new eZMail();

                if ( $tpl->hasVariable( 'content_type' ) )
                {
                    $mail->setContentType( $tpl->variable( 'content_type' ) );
                }
                else
                {
                    $mail->setContentType( 'text/html' );
                }

                $sendMail = true;
                if ( $scope == 'author' )
                {

                    if ( !eZPersistentObject::fetchObject( eZCollaborationNotificationRule::definition(), null, array( 'user_id' => $authorId, 'collab_identifier' => OpenPABookingCollaborationHandler::TYPE_STRING ), null, null, true ) )
                    {
                        $sendMail = false;
                    }

                    $owner = eZUser::fetch( $authorId );
                    $receiver = $owner instanceof eZUser ? $owner->attribute( 'email' ) : false;
                    if ( !$mail->validate( $receiver ) ) $receiver = $ini->variable( "MailSettings", "AdminEmail" );
                    $mail->setReceiver( $receiver );
                }
                elseif( $scope == 'approver' )
                {
                    $receiver = false;
                    foreach( $approveIdArray as $approverId )
                    {
                        if ( !$receiver )
                        {
                            $approver = eZUser::fetch( $approverId );
                            if ( !eZPersistentObject::fetchObject( eZCollaborationNotificationRule::definition(), null, array( 'user_id' => $approverId, 'collab_identifier' => OpenPABookingCollaborationHandler::TYPE_STRING ), null, null, true ) )
                            {
                                $approver = false;
                            }
                            $receiver = $approver instanceof eZUser ? $approver->attribute( 'email' ) : false;
                            if ( $receiver )
                            {
                                $mail->setReceiver( $receiver );
                            }
                        }
                        else
                        {
                            $approver = eZUser::fetch( $approverId );
                            if ( !eZPersistentObject::fetchObject( eZCollaborationNotificationRule::definition(), null, array( 'user_id' => $approverId, 'collab_identifier' => OpenPABookingCollaborationHandler::TYPE_STRING ), null, null, true ) )
                            {
                                $approver = false;
                            }
                            $ccReceiver = $approver instanceof eZUser ? $approver->attribute( 'email' ) : false;
                            if ( $mail->validate( $ccReceiver ) )
                            {
                                $mail->addCc( $ccReceiver );
                            }
                        }
                    }
                    if ( !$receiver )
                    {
                        $sendMail = false;
                    }
                }

                $sender = $ini->variable( "MailSettings", "EmailSender" );
                $mail->setSender( $sender, $ini->variable( "SiteSettings", "SiteName" ) );

                if ( $mail->validate( $replyTo ) )
                {
                    $replyTo = $ini->variable( "MailSettings", "EmailReplyTo" );
                    if ( !$mail->validate( $replyTo ) ) $replyTo = $sender;
                }
                $mail->setReplyTo( $replyTo );

                $mail->setSubject( $subject );
                $mail->setBody( $body );
                if ( $sendMail ) eZMailTransport::send( $mail );
            }
        }
    }

    public function isValid()
    {
        return $this->container->getContentObject() instanceof eZContentObject && $this->container->getContentObject()->attribute( 'class_identifier' ) == $this->prenotazioneClassIdentifier();
    }

    abstract public function templateDirectory();

    abstract public function getApproverIds();

    abstract public function prenotazioneClassIdentifier();

    abstract public function createObject( eZContentObject $parentObject, $start, $end );

    abstract public function isValidDate( $start, $end, eZContentObject $object );

    abstract protected function getStartDateTime();

    abstract protected function getEndDateTime();
}