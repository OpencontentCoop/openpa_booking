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
        self::STATUS_PENDING                => 'in_attesa_di_approvazione',
        self::STATUS_WAITING_FOR_CHECKOUT   => 'in_attesa_di_pagamento',
        self::STATUS_WAITING_FOR_PAYMENT    => 'in_attesa_di_verifica_pagamento',
        self::STATUS_APPROVED               => 'confermato',
        self::STATUS_DENIED                 => 'rifiutato',
        self::STATUS_EXPIRED                => 'scaduto'
    );

    function run()
    {
        $this->fnData['is_valid'] = 'isValid';
        $this->fnData['current_state_code'] = 'getCurrentStateCode';
        $this->fnData['current_state'] = 'getCurrentState';
        $this->fnData['collaboration_item'] = 'getCollaborationItem';
        $this->data['state_colors'] = static::getStateColors();
        $this->fnData['reservation_manager_ids'] = 'getApproverIds';
        $this->fnData['start'] = 'getStartDateTime';
        $this->fnData['start_moment'] = 'getStartMoment';
        $this->fnData['start_timestamp'] = 'getStartTimestamp';
        $this->fnData['end'] = 'getEndDateTime';
        $this->fnData['end_timestamp'] = 'getEndMoment';
        $this->fnData['end_moment'] = 'getEndTimestamp';
    }

    public static function getStateColors()
    {
        $data = array();
        $data[static::STATUS_APPROVED] = "#419641";
        $data[static::STATUS_DENIED] = "#666666";
        $data[static::STATUS_PENDING] = "#e38d13";
        $data[static::STATUS_EXPIRED] = "#000000";
        $data[static::STATUS_WAITING_FOR_CHECKOUT] = "#0000FF";
        $data[static::STATUS_WAITING_FOR_PAYMENT] = "#CC66FF";
        $data['current'] = '#ff0000';
        $data['none'] = '#333';

        return $data;
    }

    protected function getStartMoment()
    {
        return $this->getStartDateTime()->format('c');
    }

    protected function getStartTimestamp()
    {
        return $this->getStartDateTime()->getTimestamp();
    }

    protected function getEndMoment()
    {
        return $this->getEndDateTime()->getTimestamp();
    }

    protected function getEndTimestamp()
    {
        return $this->getEndDateTime()->format('c');
    }

    /**
     * @return eZCollaborationItem|null
     */
    public function getCollaborationItem()
    {
        $data = null;
        if ($this->isValid()) {
            $data = eZPersistentObject::fetchObject(
                eZCollaborationItem::definition(),
                null,
                array('data_int1' => $this->container->getContentObject()->attribute('id')),
                true
            );
        }

        return $data;
    }

    public static function getStates()
    {
        return OpenPABase::initStateGroup(static::$stateGroupIdentifier, static::$stateIdentifiers);
    }

    public static function getStateObject($stateCode)
    {
        $states = OpenPABase::initStateGroup(static::$stateGroupIdentifier, static::$stateIdentifiers);
        $stateObject = null;
        foreach ($states as $state) {
            switch ($stateCode) {
                case static::STATUS_PENDING: {
                    if ($state->attribute('identifier') == 'in_attesa_di_approvazione') {
                        $stateObject = $state;
                    }
                }
                    break;

                case static::STATUS_WAITING_FOR_CHECKOUT: {
                    if ($state->attribute('identifier') == 'in_attesa_di_pagamento') {
                        $stateObject = $state;
                    }
                }
                    break;

                case static::STATUS_WAITING_FOR_PAYMENT: {
                    if ($state->attribute('identifier') == 'in_attesa_di_verifica_pagamento') {
                        $stateObject = $state;
                    }
                }
                    break;

                case static::STATUS_APPROVED: {
                    if ($state->attribute('identifier') == 'confermato') {
                        $stateObject = $state;
                    }
                }
                    break;

                case static::STATUS_DENIED: {
                    if ($state->attribute('identifier') == 'rifiutato') {
                        $stateObject = $state;
                    }
                }
                    break;

                case static::STATUS_EXPIRED: {
                    if ($state->attribute('identifier') == 'scaduto') {
                        $stateObject = $state;
                    }
                }
                    break;
            }
        }

        return $stateObject;
    }

    protected function getCurrentState()
    {
        return static::getStateObject($this->getCurrentStateCode());
    }

    protected function getCurrentStateCode()
    {
        $current = static::STATUS_PENDING;
        if ($this->isValid()) {
            $states = OpenPABase::initStateGroup(static::$stateGroupIdentifier, static::$stateIdentifiers);
            $currentStates = $this->container->getContentObject()->attribute('state_id_array');
            foreach ($states as $state) {
                if (in_array($state->attribute('id'), $currentStates)) {
                    $current = static::getStateCodeFromIdentifier($state->attribute('identifier'));
                }
            }
        }

        return $current;
    }

    public static function getStateCodeFromIdentifier($identifier)
    {
        $code = static::STATUS_PENDING;
        foreach(static::$stateIdentifiers as $index => $value){
            if ($value == $identifier){
                return $index;
            }
        }

        return $code;
    }

    public static function getStateIdentifierFromCode($code)
    {
        $value = null;
        foreach(static::$stateIdentifiers as $index => $value){
            if (is_numeric($index) && $index == $code){
                return $value;
            }
        }

        return $code;
    }

    public static function assignState(eZContentObject $object, $stateCode, $checkPermission = false)
    {
        if ($object instanceof eZContentObject) {
            $state = static::getStateObject($stateCode);
            eZDebug::writeDebug('Change (' . $checkPermission . ')  state to ' . $stateCode . ' for object ' . $object->attribute('id'), __METHOD__);
            if ($state instanceof eZContentObjectState) {
                if ($checkPermission) {
                    eZContentOperationCollection::updateObjectState(
                        $object->attribute('id'),
                        array($state->attribute('id')));
                } else {
                    $object->assignState($state);
                    $object->setAttribute('modified', time());
                    $object->store();
                    eZSearch::updateObjectState(
                        $object->attribute('id'),
                        array($state->attribute('id'))
                    );
                    eZContentCacheManager::clearContentCacheIfNeeded($object->attribute('id'));
                    eZContentOperationCollection::updateObjectState(
                        $object->attribute('id'),
                        array($state->attribute('id'))
                    );
                }
            }
        }
    }

    public function changeState($stateCode, $checkPermission = false)
    {
        $currentState = $this->getCurrentStateCode();
        $params = array('state_before' => static::getStateObject($currentState));
        if ($currentState != $stateCode) {
            static::assignState($this->container->getContentObject(), $stateCode, $checkPermission);
            if ($this->container->getContentMainNode()->childrenCount()) {
                /** @var eZContentObjectTreeNode[] $children */
                $children = $this->container->getContentMainNode()->children();
                foreach($children as $child){
                    static::assignState($child->object(), $stateCode, false);
                }
            }
            $params['state_after'] = static::getStateObject($stateCode);
            $this->notify('change_state', $params);
        }
    }

    private function getMessageTemplate($action, $scope)
    {
        $oneFilePath = 'design:booking/' . $this->templateDirectory() . '/messages.tpl';

        $multipleFilePath = "design:booking/" . $this->templateDirectory() . "/messages/$action/$scope.tpl";

        foreach (array($oneFilePath, $multipleFilePath) as $templateUri) {
            $currentErrorReporting = error_reporting();
            error_reporting(0);
            $tpl = eZTemplate::factory();
            $result = $tpl->loadURIRoot($templateUri, false, $extraParameters);
            error_reporting($currentErrorReporting);
            if ($result) {
                return $templateUri;
            }
        }

        return false;
    }

    public function addMessage($messageText = '')
    {
        // registra messaggio
        $collaborationItem = $this->getCollaborationItem();
        if ($collaborationItem instanceof eZCollaborationItem && trim($messageText) != '') {

            $message = eZCollaborationSimpleMessage::create(
                'openpabooking_comment',
                $messageText
            );
            $message->store();

            $messageLink = eZCollaborationItemMessageLink::addMessage(
                $collaborationItem,
                $message,
                OpenPABookingCollaborationHandler::MESSAGE_TYPE_DEFAULT
            );
            eZCollaborationItemStatus::setLastRead(
                $collaborationItem->attribute('id'),
                eZUser::currentUserID(),
                $messageLink->attribute('modified') + 1
            );
            eZDebug::writeDebug("Add message \"$messageText\"", __METHOD__);
        }
    }

    public function notify($action, $params = array())
    {
        $scopes = array('author', 'approver', 'observer', 'messages');

        foreach ($scopes as $scope) {

            $subject = null;
            $body = null;
            $replyTo = null;
            $messageText = null;

            $mail = new eZMail();
            $templateUri = $this->getMessageTemplate($action, $scope);

            if ($templateUri) {
                $tpl = eZTemplate::factory();
                $tpl->resetVariables();
                $tpl->setVariable('object', $this->container->getContentObject());
                $tpl->setVariable('action', $action);
                $tpl->setVariable('scope', $scope);
                foreach ($params as $key => $value) {
                    $tpl->setVariable($key, $value);
                }
                $tpl->fetch($templateUri);

                $subject = $tpl->variable('email_subject');
                $body = $tpl->variable('email_body');
                $replyTo = $tpl->variable('email_reply_to');
                $messageText = $tpl->variable('comment');


                if ($tpl->hasVariable('content_type')) {
                    $mail->setContentType($tpl->variable('content_type'));
                } else {
                    $mail->setContentType('text/html');
                }
            }

            if ($messageText) {
                $this->addMessage($messageText);
            }

            if (trim($subject) != '' && trim($body) != '') {

                $mail->setSubject($subject);
                $mail->setBody($body);

                $receiverIdList = array();
                if ($scope == 'author') {
                    $receiverIdList = array($this->container->getContentObject()->attribute('owner_id'));
                } elseif ($scope == 'approver') {
                    $receiverIdList = array_unique($this->getApproverIds());
                } elseif ($scope == 'observer') {
                    $receiverIdList = array_unique($this->getObserversIds());
                }

                if (!empty($receiverIdList)) {
                    $receivers = (array)eZPersistentObject::fetchObjectList(
                        eZUser::definition(),
                        null,
                        array('contentobject_id' => array($receiverIdList)),
                        true
                    );

                    foreach ($receivers as $index => $receiver) {
                        if ($receiver instanceof eZUser && $mail->validate($receiver->attribute('email'))) {
                            if ($index == 0) {
                                $mail->setReceiver($receiver->attribute('email'));
                            } else {
                                $mail->addCc($receiver->attribute('email'));
                            }
                        }
                    }

                    if (count($receivers) == 0) {
                        eZDebug::writeError("Receivers users not found sending notification mail to $scope for action $action",
                            __METHOD__);
                    }

                    $ini = eZINI::instance();
                    $sender = $ini->variable("MailSettings", "EmailSender");
                    $mail->setSender($sender, $ini->variable("SiteSettings", "SiteName"));

                    if (!$mail->validate($replyTo)) {
                        $replyTo = $ini->variable("MailSettings", "EmailReplyTo");
                    }
                    if (!$mail->validate($replyTo)) {
                        $replyTo = $sender;
                    }
                    $mail->setReplyTo($replyTo);

                    $result = eZMailTransport::send($mail);
                    if ($result) {
                        eZDebug::writeDebug("Send notification mail to $scope (" . implode(', ',
                                $receiverIdList) . ") for action $action", __METHOD__);
                    } else {
                        eZDebug::writeError("Fail sending notification mail to $scope for action $action", __METHOD__);
                    }
                }

            }
        }
    }

    public function isValid()
    {
        return $this->container->getContentObject() instanceof eZContentObject
               && $this->container->getContentObject()->attribute('class_identifier') == $this->prenotazioneClassIdentifier();
    }

    abstract public function templateDirectory();

    abstract public function getApproverIds();

    abstract public function getObserversIds();

    abstract public function prenotazioneClassIdentifier();

    abstract public function createObject(eZContentObject $parentObject, $start, $end);

    abstract public function isValidDate($start, $end, eZContentObject $object);

    abstract protected function getStartDateTime();

    abstract protected function getEndDateTime();
}
