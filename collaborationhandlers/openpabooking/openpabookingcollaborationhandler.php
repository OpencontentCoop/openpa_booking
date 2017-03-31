<?php

class OpenPABookingCollaborationHandler extends eZCollaborationItemHandler
{
    const TYPE_STRING = 'openpabooking';

    /// Approval message type
    const MESSAGE_TYPE_DEFAULT = 1;

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
            array(
                'use-messages' => true,
                'notification-types' => true,
                'notification-collection-handling' => eZCollaborationItemHandler::NOTIFICATION_COLLECTION_PER_PARTICIPATION_ROLE
            )
        );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     *
     * @return string
     */
    function title($collaborationItem)
    {
        return 'Prenotazioni';
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     *
     * @return array|null
     */
    function content($collaborationItem)
    {
        $participants = OpenPABookingCollaborationParticipants::instanceFrom($collaborationItem);

        return array(
            "content_object_id" => $collaborationItem->attribute("data_int1"),
            "openpabooking_handler" => $collaborationItem->attribute("data_text1"),
            "approval_status" => $collaborationItem->attribute("data_int3"),
            "is_author" => $participants->currentUserIsAuthor(),
            "is_observer" => $participants->currentUserIsObserver(),
            "is_approver" => $participants->currentUserIsApprover()
        );
    }

    function notificationParticipantTemplate($participantRole)
    {
        if ($participantRole == eZCollaborationItemParticipantLink::ROLE_APPROVER) {
            return 'approve.tpl';
        } else if ($participantRole == eZCollaborationItemParticipantLink::ROLE_AUTHOR) {
            return 'author.tpl';
        } else {
            return false;
        }
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     *
     * @return OpenPABookingHandlerInterface
     * @throws Exception
     */
    static function handler($collaborationItem)
    {
        $identifier = $collaborationItem->contentAttribute('openpabooking_handler');
        $handler = OpenPABookingHandler::handler($identifier, array());
        if ($handler instanceof OpenPABookingHandlerInterface) {
            return $handler;
        }
        throw new Exception("Handler $identifier not found or not implement OpenPABookingHandlerInterface");
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     *
     * @return eZContentObject
     */
    static function contentObject($collaborationItem)
    {
        $contentObjectID = $collaborationItem->contentAttribute('content_object_id');

        return eZContentObject::fetch($contentObjectID);
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @param bool $viewMode
     */
    function readItem($collaborationItem, $viewMode = false)
    {
        $collaborationItem->setLastRead();
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     *
     * @return int
     */
    function messageCount($collaborationItem)
    {
        return eZCollaborationItemMessageLink::fetchItemCount(array('item_id' => $collaborationItem->attribute('id')));
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     *
     * @return int
     */
    function unreadMessageCount($collaborationItem)
    {
        $lastRead = 0;
        /** @var eZCollaborationItemStatus $status */
        $status = $collaborationItem->attribute('user_status');
        if ($status) {
            $lastRead = $status->attribute('last_read');
        }

        return eZCollaborationItemMessageLink::fetchItemCount(array(
            'item_id' => $collaborationItem->attribute('id'),
            'conditions' => array('modified' => array('>', $lastRead))
        ));
    }

    /**
     * @param $approvalID
     *
     * @return bool
     */
    static function checkApproval($approvalID)
    {
        /** @var eZCollaborationItem $collaborationItem */
        $collaborationItem = eZCollaborationItem::fetch($approvalID);
        if ($collaborationItem !== null) {
            return $collaborationItem->attribute('data_int3');
        }

        return false;
    }

    /**
     * @param int $contentObjectID
     * @param string $handlerString
     * @param OpenPABookingCollaborationParticipants $participants
     * @param eZCollaborationItem|null $collaborationItem
     *
     * @return eZCollaborationItem
     */
    static function createApproval(
        $contentObjectID,
        $handlerString,
        OpenPABookingCollaborationParticipants $participants,
        eZCollaborationItem $collaborationItem = null
    ) {
        if (!$collaborationItem instanceof eZCollaborationItem) {
            $collaborationItem = eZCollaborationItem::create(self::TYPE_STRING, eZUser::currentUserID());
            $collaborationItem->setAttribute('data_int1', $contentObjectID);
            $collaborationItem->setAttribute('data_text1', $handlerString);
            $collaborationItem->setAttribute('data_int3', false);
            $collaborationItem->store();
        }

        $participants->subscribeTo($collaborationItem);

        // Create the notification
        // al momento non vengono create notifiche ma tutto viene gestito da ObjectHandlerServiceControlBookingSalaPubblica::notify
        //$collaborationItem->createNotificationEvent();        

        // activate
        $collaborationItem->setAttribute('data_int3', self::STATUS_WAITING);
        $collaborationItem->setAttribute('status', eZCollaborationItem::STATUS_ACTIVE);
        $timestamp = time();
        $collaborationItem->setAttribute('modified', $timestamp);
        $collaborationItem->store();

        $participants->activateSubscription($collaborationItem);

        return $collaborationItem;
    }

    /**
     * @param eZModule $module
     * @param eZCollaborationItem $collaborationItem
     *
     * @return mixed
     */
    function handleCustomAction($module, $collaborationItem)
    {
        if ($this->isCustomAction('Comment')) {
            $messageText = $this->customInput('OpenpaBookingComment');
            self::handler($collaborationItem)->addComment($collaborationItem, $messageText);
        } else if ($this->isCustomAction('Accept') or
                   $this->isCustomAction('Deny') or
                   $this->isCustomAction('Defer')
        ) {

            $participants = OpenPABookingCollaborationParticipants::instanceFrom($collaborationItem);
            $approveAllowed = $participants->currentUserIsApprover();
            try {
                if (!$approveAllowed) {
                    throw new Exception('Not allowed');
                }

                if ($this->isCustomAction('Accept')) {
                    self::handler($collaborationItem)->approve($collaborationItem,
                        $this->customInput('OpenpaBookingActionParameters'));
                    self::changeApprovalStatus($collaborationItem, self::STATUS_ACCEPTED);
                } elseif ($this->isCustomAction('Deny')) {
                    self::handler($collaborationItem)->deny($collaborationItem,
                        $this->customInput('OpenpaBookingActionParameters'));
                    self::changeApprovalStatus($collaborationItem, self::STATUS_DENIED);
                } elseif ($this->isCustomAction('Defer')) {
                    self::handler($collaborationItem)->defer($collaborationItem,
                        $this->customInput('OpenpaBookingActionParameters'));
                    self::changeApprovalStatus($collaborationItem, self::STATUS_DEFERRED);
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                self::handler($collaborationItem)->redirectToSummary($module, $collaborationItem,
                    array('error' => $error));

                return;
            }

            $messageText = $this->customInput('OpenpaBookingComment');
            self::handler($collaborationItem)->addComment($collaborationItem, $messageText);
            $collaborationItem->sync();
        } else {
            if (self::handler($collaborationItem)->handleCustomAction($module, $collaborationItem)) {
                $collaborationItem->sync();
            }
        }
        self::handler($collaborationItem)->redirectToItem($module, $collaborationItem);
    }

    public static function changeApprovalStatus(eZCollaborationItem $collaborationItem, $status)
    {
        $collaborationItem->setAttribute('data_int3', $status);
        if ($status == self::STATUS_ACCEPTED || $status == self::STATUS_DENIED) {
            $collaborationItem->setAttribute('status', eZCollaborationItem::STATUS_INACTIVE);

            $timestamp = time();
            $collaborationItem->setAttribute('modified', $timestamp);
            $collaborationItem->setIsActive(false);
            $collaborationItem->sync();
        }
    }
}
