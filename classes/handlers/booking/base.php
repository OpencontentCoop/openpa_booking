<?php

abstract class BookingHandlerBase implements OpenPABookingHandlerInterface
{
    /**
     * @var eZTemplate
     */
    protected $tpl;

    /**
     * @var eZModule
     */
    protected $module;

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @param eZContentObject
     */
    protected $currentObject;

    /**
     * @var bool
     */
    protected $hasRedirect;

    /**
     *
     * In base al primo parametero che deve essere un ObjectId esegue una funzione
     *
     * @param array $Params
     *
     * @throws Exception
     */
    public function __construct(array $Params = null)
    {
        $this->tpl = $tpl = eZTemplate::factory();
        if (is_array($Params)) {
            $this->parameters = isset( $Params['Parameters'] ) ? $Params['Parameters'] : array();
            $this->currentObject = isset( $this->parameters[1] ) ? eZContentObject::fetch($this->parameters[1]) : false;

            $this->module = isset( $Params['Module'] ) ? $Params['Module'] : false;

            if ($this->currentObject instanceof eZContentObject) {
                if (!$this->currentObject->attribute('can_read')) {
                    throw new Exception("User can not read object {$this->currentObject->attribute( 'id' )}");
                }
            }
        }
    }

    function getCurrentObject()
    {
        return $this->currentObject;
    }

    public function add()
    {
        $serviceClass = $this->serviceClass();
        if ($this->currentObject instanceof eZContentObject) {
            $http = eZHTTPTool::instance();

            $start = $http->getVariable('start', false);
            $end = $http->getVariable('end', false);

            try {
                $serviceClass->isValidDate($start, $end, $this->currentObject);
            } catch (Exception $e) {
                if ($this->module instanceof eZModule) {
                    $this->module->redirectTo('content/view/full/' . $this->currentObject->attribute('main_node_id') . '/(error)/' . urlencode($e->getMessage()) . '#error');
                }else{
                    throw $e;
                }

                return null;
            }

            $object = $serviceClass->createObject($this->currentObject, $start, $end);

            if ($object instanceof eZContentObject) {
                if ($this->module instanceof eZModule) {
                    $this->module->redirectTo('content/edit/' . $object->attribute('id') . '/' . $object->attribute('current_version'));
                }

                return null;
            } else {
                throw new Exception("Non è possibile creare l'oggetto prenotazione");
            }
        }
    }

    public function view()
    {
        if ($this->currentObject instanceof eZContentObject) {
            $service = $this->serviceObject($this->currentObject);
            if ($service
                && $this->currentObject->attribute('can_read')
            ) {
                $collaborationItem = $service->getCollaborationItem();
                if ($collaborationItem instanceof eZCollaborationItem) {
                    $module = eZModule::exists("collaboration");

                    return $module->run('item', array('full', $collaborationItem->attribute('id')));

                } else {
                    throw new Exception("eZCollaborationItem not found for object {$this->currentObject->attribute( 'id' )}");
                }
            }
        } else {
            $Result = array();
            $Result['content'] = $this->tpl->fetch('design:booking/' . $this->serviceClass()->templateDirectory() . '/list.tpl');
            $Result['path'] = array(array('text' => $this->name(), 'url' => false));

            return $Result;
        }

        return false;
    }

    public function workflow($parameters, $process, $event)
    {
        $trigger = $parameters['trigger_name'];
        eZDebug::writeNotice("Run workflow", __METHOD__);
        if ($trigger == 'post_publish') {
            $currentObject = eZContentObject::fetch($parameters['object_id']);
            $serviceObject = $this->serviceObject($currentObject);
            if ($serviceObject) {
                if ($currentObject->attribute('current_version') == 1
                    && $currentObject->attribute('class_identifier') == $serviceObject->prenotazioneClassIdentifier()
                ) {
                    $id = $currentObject->attribute('id');
                    $authorId = $currentObject->attribute('owner_id');
                    $approveIdArray = $serviceObject->getApproverIds();

                    $participants = new OpenPABookingCollaborationParticipants();
                    $participants->addAuthor($authorId)->addApprovers($approveIdArray);

                    OpenPABookingCollaborationHandler::createApproval($id, static::identifier(), $participants);
                    $serviceObject->notify('create_approval');
                    eZDebug::writeNotice("Create collaboration item", __METHOD__);
                }
            }
        }
    }

    public function approve(eZCollaborationItem $item, $parameters = array())
    {
        $prenotazione = OpenPABookingCollaborationHandler::contentObject($item);
        $serviceObject = $this->serviceObject($prenotazione);
        if ($serviceObject->isValid()
            && $prenotazione->attribute('can_read')
        ) {
            $serviceObject->changeState(ObjectHandlerServiceControlBooking::STATUS_APPROVED);
        }
    }

    public function deny(eZCollaborationItem $item, $parameters = array())
    {
        $prenotazione = OpenPABookingCollaborationHandler::contentObject($item);
        $serviceObject = $this->serviceObject($prenotazione);
        if ($serviceObject->isValid()
            && $prenotazione->attribute('can_read')
        ) {
            $serviceObject->changeState(ObjectHandlerServiceControlBooking::STATUS_DENIED);
        }
    }

    public function redirectToItem(eZModule $module, eZCollaborationItem $item, $parameters = array())
    {
        if ($module->exitStatus() != eZModule::STATUS_REDIRECT){
            $id = $item->attribute("data_int1");
            $suffix = '';
            if (isset( $parameters['error'] )) {
                $suffix = '/?error=' . urlencode($parameters['error']);
            }

            return $module->redirectTo('openpa_booking/view/' . $item->contentAttribute('openpabooking_handler') . '/' . $id . $suffix);
        }
    }

    public function redirectToSummary(eZModule $module, eZCollaborationItem $item, $parameters = array())
    {
        $id = $item->attribute("data_int1");
        $suffix = '';
        if (isset( $parameters['error'] )) {
            $suffix = '/?error=' . urlencode($parameters['error']);
        }
        return $module->redirectTo('openpa_booking/view/' . $item->contentAttribute('openpabooking_handler') . $suffix);
    }

    public function defer(eZCollaborationItem $item, $parameters = array())
    {

    }

    public function addComment(eZCollaborationItem $item, $messageText)
    {
        $prenotazione = OpenPABookingCollaborationHandler::contentObject($item);
        $serviceObject = $this->serviceObject($prenotazione);
        if ($serviceObject){
            $serviceObject->addMessage($messageText);
            $serviceObject->notify('add_comment', array('text' => $messageText));
        }
    }

    public function handleCustomAction(eZModule $module, eZCollaborationItem $item)
    {
        return false;
    }

    protected function isCustomAction($name)
    {
        $http = eZHTTPTool::instance();
        $postVariable = 'CollaborationAction_' . $name;

        return $http->hasPostVariable($postVariable);
    }

    protected function hasCustomInput($name)
    {
        $http = eZHTTPTool::instance();
        $postVariable = 'Collaboration_' . $name;

        return $http->hasPostVariable($postVariable);
    }

    function customInput($name)
    {
        $http = eZHTTPTool::instance();
        $postVariable = 'Collaboration_' . $name;

        return $http->postVariable($postVariable);
    }

    /**
     * @return ObjectHandlerServiceControlBooking
     */
    abstract protected function serviceClass();

    /**
     * @param eZCollaborationItem|eZContentObject $item
     *
     * @return null|ObjectHandlerServiceControlBooking
     */
    protected function serviceObject($item){
        $service = null;
        if ($item instanceof eZCollaborationItem){
            $item = OpenPABookingCollaborationHandler::contentObject($item);
        }
        if ($item instanceof eZContentObject){
            $openpaObject = OpenPAObjectHandler::instanceFromContentObject($item);
            /** @var ObjectHandlerServiceControlBooking $service */
            $service = $openpaObject->serviceByClassName(get_class($this->serviceClass()));
        }
        return $service;
    }
}
