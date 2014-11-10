<?php

class OpenPABookingCollaborationHandler extends eZCollaborationItemHandler
{
    const TYPE_STRING = 'openpabooking';

    /// Approval message type
    const MESSAGE_TYPE_APPROVE = 1;

    /// Default status, no approval decision has been made
    const STATUS_WAITING = 0;

    /// The location was approved and will be published.
    const STATUS_ACCEPTED = 1;

    /// The location was denied and will be archived.
    const STATUS_DENIED = 2;

    /// The location was deferred and will deleted.
    const STATUS_DEFERRED = 3;

    /*!
     Initializes the handler
    */
    function OpenPABookingCollaborationHandler()
    {
        $this->eZCollaborationItemHandler(
            OpenPABookingCollaborationHandler::TYPE_STRING,
            'Prenotazioni',
            array( 'use-messages' => true,
                'notification-types' => true,
                'notification-collection-handling' => eZCollaborationItemHandler::NOTIFICATION_COLLECTION_PER_PARTICIPATION_ROLE ) );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return string
     */
    function title( $collaborationItem )
    {
        return 'Prenotazioni';
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return array|null
     */
    function content( $collaborationItem )
    {
        return array(
            "content_object_id" => $collaborationItem->attribute( "data_int1" ),
            "openpabooking_handler" => $collaborationItem->attribute( "data_text1" ),
            "approval_status" => $collaborationItem->attribute( "data_int3" )
        );
    }

    function notificationParticipantTemplate( $participantRole )
    {
        if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_APPROVER )
        {
            return 'approve.tpl';
        }
        else if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_AUTHOR )
        {
            return 'author.tpl';
        }
        else
            return false;
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return OpenPABookingHandlerInterface
     * @throws Exception
     */
    static function handler( $collaborationItem )
    {
        $handlerString = $collaborationItem->contentAttribute( 'openpabooking_handler' );
        if ( class_exists( $handlerString ) )
        {
            $handler = new $handlerString();
            if ( $handler instanceof OpenPABookingHandlerInterface )
            {
                return $handler;
            }
        }
        throw new Exception( "Handler $handlerString not found or not implement OpenPABookingHandlerInterface" );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return eZContentObject
     */
    static function contentObject( $collaborationItem )
    {
        $contentObjectID = $collaborationItem->contentAttribute( 'content_object_id' );
        return eZContentObject::fetch( $contentObjectID );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @param bool $viewMode
     */
    function readItem( $collaborationItem, $viewMode = false )
    {
        $collaborationItem->setLastRead();
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return int
     */
    function messageCount( $collaborationItem )
    {
        return eZCollaborationItemMessageLink::fetchItemCount( array( 'item_id' => $collaborationItem->attribute( 'id' ) ) );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return int
     */
    function unreadMessageCount( $collaborationItem )
    {
        $lastRead = 0;
        /** @var eZCollaborationItemStatus $status */
        $status = $collaborationItem->attribute( 'user_status' );
        if ( $status )
            $lastRead = $status->attribute( 'last_read' );
        return eZCollaborationItemMessageLink::fetchItemCount( array( 'item_id' => $collaborationItem->attribute( 'id' ),
            'conditions' => array( 'modified' => array( '>', $lastRead ) ) ) );
    }

    /**
     * @param $approvalID
     * @return bool
     */
    static function checkApproval( $approvalID )
    {
        /** @var eZCollaborationItem $collaborationItem */
        $collaborationItem = eZCollaborationItem::fetch( $approvalID );
        if ( $collaborationItem !== null )
        {
            return $collaborationItem->attribute( 'data_int3' );
        }
        return false;
    }

    /**
     * @param $contentObjectID
     * @param string $handlerString
     * @param $authorID
     * @param $approverIDArray
     * @return eZCollaborationItem
     */
    static function createApproval( $contentObjectID, $handlerString, $authorID, $approverIDArray )
    {
        $collaborationItem = eZCollaborationItem::create( self::TYPE_STRING, $authorID );
        $collaborationItem->setAttribute( 'data_int1', $contentObjectID );
        $collaborationItem->setAttribute( 'data_text1', $handlerString );
        $collaborationItem->setAttribute( 'data_int3', false );
        $collaborationItem->store();
        $collaborationID = $collaborationItem->attribute( 'id' );

        $participantList = array( array( 'id' => array( $authorID ),
            'role' => eZCollaborationItemParticipantLink::ROLE_AUTHOR ),
            array( 'id' => $approverIDArray,
                'role' => eZCollaborationItemParticipantLink::ROLE_APPROVER ) );
        foreach ( $participantList as $participantItem )
        {
            foreach( $participantItem['id'] as $participantID )
            {
                $participantRole = $participantItem['role'];
                $link = eZCollaborationItemParticipantLink::create( $collaborationID, $participantID,
                    $participantRole, eZCollaborationItemParticipantLink::TYPE_USER );
                $link->store();

                $profile = eZCollaborationProfile::instance( $participantID );
                $groupID = $profile->attribute( 'main_group' );
                eZCollaborationItemGroupLink::addItem( $groupID, $collaborationID, $participantID );
            }
        }

        // Create the notification
        $collaborationItem->createNotificationEvent();

        // activate
        $collaborationItem->setAttribute( 'data_int3', self::STATUS_WAITING );
        $collaborationItem->setAttribute( 'status', eZCollaborationItem::STATUS_ACTIVE );
        $timestamp = time();
        $collaborationItem->setAttribute( 'modified', $timestamp );
        $collaborationItem->store();
        /** @var eZCollaborationItemParticipantLink[] $participantList */
        $participantList = eZCollaborationItemParticipantLink::fetchParticipantList( array( 'item_id' => $collaborationItem->attribute( 'id' ) ) );
        foreach( $participantList as $participantLink )
        {
            $collaborationItem->setIsActive( true, $participantLink->attribute( 'participant_id' ) );
        }
        return $collaborationItem;
    }

    /**
     * @param eZModule $module
     * @param eZCollaborationItem $collaborationItem
     * @return mixed
     */
    function handleCustomAction( $module, $collaborationItem )
    {
        $redirectView = 'item';
        $redirectParameters = array( 'full', $collaborationItem->attribute( 'id' ) );
        $addComment = false;

        if ( $this->isCustomAction( 'Comment' ) )
        {
            $addComment = true;
        }
        else if ( $this->isCustomAction( 'Accept' ) or
            $this->isCustomAction( 'Deny' ) or
            $this->isCustomAction( 'Defer' ) )
        {
            // check user's rights to approve
            $user = eZUser::currentUser();
            $userID = $user->attribute( 'contentobject_id' );
            $participantList = eZCollaborationItemParticipantLink::fetchParticipantList( array( 'item_id' => $collaborationItem->attribute( 'id' ) ) );

            $approveAllowed = false;
            foreach( $participantList as $participant )
            {
                if ( $participant->ParticipantID == $userID &&
                    $participant->ParticipantRole == eZCollaborationItemParticipantLink::ROLE_APPROVER )
                {
                    $approveAllowed = true;
                    break;
                }
            }
            if ( !$approveAllowed )
            {
                return $module->redirectToView( $redirectView, $redirectParameters );
            }

            if ( $this->isCustomAction( 'Accept' ) )
            {
                self::handler( $collaborationItem )->approve( $collaborationItem );
                self::changeApprovalStatus( $collaborationItem, self::STATUS_ACCEPTED );
            }
            elseif ( $this->isCustomAction( 'Deny' ) )
            {
                self::handler( $collaborationItem )->deny( $collaborationItem );
                self::changeApprovalStatus( $collaborationItem, self::STATUS_DENIED );
            }
            elseif ( $this->isCustomAction( 'Defer' ) )
            {
                self::handler( $collaborationItem )->defer( $collaborationItem );
                self::changeApprovalStatus( $collaborationItem, self::STATUS_DEFERRED );
            }

            $redirectView = 'view';
            $redirectParameters = array( 'summary' );
            $addComment = true;
        }
        if ( $addComment )
        {
            $messageText = $this->customInput( 'OpenpaBookingComment' );
            if ( trim( $messageText ) != '' )
            {
                $message = eZCollaborationSimpleMessage::create( self::TYPE_STRING.'_comment', $messageText );
                $message->store();
                eZCollaborationItemMessageLink::addMessage( $collaborationItem, $message, self::MESSAGE_TYPE_APPROVE );
            }
        }
        $collaborationItem->sync();
        return $module->redirectToView( $redirectView, $redirectParameters );
    }

    public static function changeApprovalStatus( eZCollaborationItem $collaborationItem, $status )
    {
        $collaborationItem->setAttribute( 'data_int3', $status );
        if ( $status == self::STATUS_ACCEPTED || $status == self::STATUS_DENIED )
        {
            $collaborationItem->setAttribute( 'status', eZCollaborationItem::STATUS_INACTIVE );

            $timestamp = time();
            $collaborationItem->setAttribute( 'modified', $timestamp );
            $collaborationItem->setIsActive( false );
        }
    }
}