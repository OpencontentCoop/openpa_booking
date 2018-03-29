<?php

class ObjectHandlerServiceControlBookingSalaPubblica extends ObjectHandlerServiceControlBooking
{
    const STATUS_RETURN_OK = 10;
    const STATUS_RETURN_KO = 20;

    const STUFF_PENDING = 'pending';
    const STUFF_APPROVED = 'approved';
    const STUFF_DENIED = 'denied';
    const STUFF_EXPIRED = 'expired';

    const ROLE_MEMBER = 'Booking Member';
    const ROLE_ADMIN = 'Booking Admin';
    const ROLE_ANONYM = 'Booking Anonymous';

    protected static $stateIdentifiers = array(
        self::STATUS_PENDING                => 'in_attesa_di_approvazione',
        self::STATUS_WAITING_FOR_CHECKOUT   => 'in_attesa_di_pagamento',
        self::STATUS_WAITING_FOR_PAYMENT    => 'in_attesa_di_verifica_pagamento',
        self::STATUS_APPROVED               => 'confermato',
        self::STATUS_DENIED                 => 'rifiutato',
        self::STATUS_EXPIRED                => 'scaduto',
        self::STATUS_RETURN_OK              => 'restituzione_ok',
        self::STATUS_RETURN_KO              => 'restituzione_ko',
    );

    public static function getStateColors()
    {
        $data = array();
        $data[self::STATUS_APPROVED] = "#419641";
        $data[self::STATUS_DENIED] = "#666666";
        $data[self::STATUS_PENDING] = "#e38d13";
        $data[self::STATUS_EXPIRED] = "#000000";
        $data[self::STATUS_WAITING_FOR_CHECKOUT] = "#0000FF";
        $data[self::STATUS_WAITING_FOR_PAYMENT] = "#CC66FF";
        $data[self::STATUS_RETURN_OK] = "#419641";
        $data[self::STATUS_RETURN_KO] = "#666666";
        $data['current'] = '#ff0000';
        $data['none'] = '#333';

        return $data;
    }

    public static function getStateObject($stateCode)
    {
        $states = OpenPABase::initStateGroup(static::$stateGroupIdentifier, static::$stateIdentifiers);
        $stateObject = null;
        foreach ($states as $state) {
            switch ($stateCode) {
                case static::STATUS_RETURN_OK: {
                    if ($state->attribute('identifier') == 'restituzione_ok') {
                        $stateObject = $state;
                    }
                }
                    break;

                case static::STATUS_RETURN_KO: {
                    if ($state->attribute('identifier') == 'restituzione_ko') {
                        $stateObject = $state;
                    }
                }
                    break;

                default:
                    $stateObject = parent::getStateObject($stateCode);
            }
        }

        return $stateObject;
    }

    function run()
    {
        $this->fnData['sala'] = 'getSala';
        $this->fnData['has_stuff'] = 'hasStuff';
        $this->fnData['stuff'] = 'getStuffList';
        $this->fnData['stuff_statuses'] = 'getStuffStatuses';
        $this->fnData['has_manual_price'] = 'hasManualPrice';
        $this->fnData['timeslot_count'] = 'getTimeSlotCount';
        $this->data['all_day'] = false; //@todo
        $this->fnData['concurrent_requests'] = 'getConcurrentRequests';
        $this->fnData['all_concurrent_requests'] = 'getAllConcurrentRequests';
        $this->fnData['is_stuff_approved'] = 'isStuffApproved';
        $this->fnData['is_stuff_not_pending'] = 'isStuffNotPending';

        parent::run();
    }

    protected static function fetchConcurrentItems(
        DateTime $start,
        DateTime $end,
        $states = array(),
        $sala = array(),
        $id = null,
        $count = false
    ) {
        $filters = array();
        if ($id) {
            $filters['-meta_id_si'] = $id;
        }
        if (!empty( $states )) {
            $stateFilters = array();
            if (count($states) > 1) {
                $stateFilters = array('or');
            }
            foreach ($states as $stateCode) {
                $state = self::getStateObject($stateCode);
                if ($state instanceof eZContentObjectState) {
                    $stateFilters[] = 'meta_object_states_si:' . $state->attribute('id');
                }
            }
            if (!empty( $stateFilters )) {
                $filters[] = $stateFilters;
            }
        }

        $fromField = OpenPASolr::generateSolrField('from_time', 'date');
        $fromValue = strftime('%Y-%m-%dT%H:%M:%SZ', (int)$start->format('U') + 1);
        $toField = OpenPASolr::generateSolrField('to_time', 'date');
        $toValue = strftime('%Y-%m-%dT%H:%M:%SZ', (int)$end->format('U') - 1) ;

        $dateFilter = array(
            'or',
            array(
                'and',
                $fromField => '[ ' . $fromValue . ' TO ' . $toValue . ' ]',
                $toField => '[ ' . $fromValue . ' TO ' . $toValue . ' ]'
            ),
            array(
                'and',
                $fromField => '[ * TO ' . $fromValue . ' ]',
                $toField => '[ ' . $toValue . ' TO * ]'
            ),
            array(
                'and',
                $fromField => '[ ' . $fromValue . ' TO ' . $toValue . ' ]',
                $toField => '[ ' . $toValue . ' TO * ]'
            ),
            array(
                'and',
                $fromField => '[ * TO ' . $fromValue . ' ]',
                $toField => '[ ' . $fromValue . ' TO ' . $toValue . ' ]'
            ),
            array(
                'and',
                $fromField => '[' . $fromValue . ' TO * ]',
                $toField => '[ * TO ' . $toValue . ' ]'
            )
        );
        $filters[] = $dateFilter;
        $sortBy = array($fromField => 'desc', 'published' => 'asc');
        $solrSearch = new eZSolr();
        $search = $solrSearch->search('', array(
            'SearchSubTreeArray' => $sala,
            'SearchLimit' => $count ? 1 : 1000,
            'SortBy' => $sortBy,
            'Limitation' => array(),
            'Filter' => $filters
        ));

        return $count ? $search['SearchCount'] : $search['SearchResult'];
    }

    protected function getConcurrentRequests()
    {
        $data = array();
        if ($this->isValid()) {
            $sala = array();
            if ($this->getSala() instanceof eZContentObject) {
                $sala[] = $this->getSala()->attribute('main_node_id');
            }
            $data = self::fetchConcurrentItems(
                $this->getStartDateTime(),
                $this->getEndDateTime(),
                array(self::STATUS_PENDING, self::STATUS_WAITING_FOR_CHECKOUT, self::STATUS_WAITING_FOR_PAYMENT),
                $sala,
                $this->container->getContentObject()->attribute('id')
            );
        }

        return $data;
    }

    protected function getAllConcurrentRequests()
    {
        $data = array();
        if ($this->isValid()) {
            $sala = array();
            if ($this->getSala() instanceof eZContentObject) {
                $sala[] = $this->getSala()->attribute('main_node_id');
            }
            $data = self::fetchConcurrentItems(
                $this->getStartDateTime(),
                $this->getEndDateTime(),
                array(),
                $sala,
                $this->container->getContentObject()->attribute('id')
            );
        }

        return $data;
    }

    protected function getTimeSlotCount()
    {
        $e = new DateTime('00:00');
        $f = clone $e;
        $e->add($this->getEndDateTime()->diff($this->getStartDateTime()));
        $hourMinutes = $f->diff($e)->format("%h,%i"); //@todo
        return round($hourMinutes);
    }

    public function getApproverIds()
    {
        $list = array();
        $sala = $this->getSala();
        if ($sala instanceof eZContentObject) {
            /** @var eZContentObjectAttribute[] $salaDataMap */
            $salaDataMap = $sala->attribute('data_map');
            if (isset( $salaDataMap['reservation_manager'] )) {
                $items = explode('-', $salaDataMap['reservation_manager']->toString());
                foreach ($items as $item) {
                    $id = (int)trim($item);
                    if ($id > 0) {
                        $list[] = (int)$id;
                    }

                }
            }
        }
        if (empty( $list )) {
            $admin = eZUser::fetchByName('admin');
            if ($admin instanceof eZUser) {
                $list[] = $admin->attribute('contentobject_id');
                eZDebug::writeNotice("Add admin user as fallback empty partecipant list", __METHOD__);
            }
        }

        return $list;
    }

    public function getObserversIds()
    {
        $list = array();
        $stuffList = $this->getStuffList();
        foreach($stuffList as $stuff){
            $list = array_merge(
                $list,
                self::getStuffManagerIds($stuff['object'])
            );
        }

        return $list;
    }

    public static function getStuffManagerIds(eZContentObject $stuff)
    {
        $list = array();
        /** @var eZContentObjectAttribute[] $dataMap */
        $dataMap = $stuff->attribute('data_map');
        if (isset( $dataMap['reservation_manager'] )) {
            $items = explode('-', $dataMap['reservation_manager']->toString());
            foreach ($items as $item) {
                $id = (int)trim($item);
                if ($id > 0) {
                    $list[] = (int)$id;
                }

            }
        }

        return $list;
    }

    protected function getStartDateTime()
    {
        $date = new DateTime();
        if (isset( $this->container->attributesHandlers['from_time'] )) {
            $date->setTimestamp($this->container->attributesHandlers['from_time']->attribute('contentobject_attribute')->toString());
        }

        return $date;
    }

    protected function getEndDateTime()
    {
        $date = new DateTime();
        if (isset( $this->container->attributesHandlers['to_time'] )) {
            $date->setTimestamp($this->container->attributesHandlers['to_time']->attribute('contentobject_attribute')->toString());
        }

        return $date;
    }

    private $sala;

    /**
     * @return eZContentObject
     */
    protected function getSala()
    {
        if ($this->sala === null) {
            if ($this->isValid()) {
                if (isset( $this->container->attributesHandlers['sala'] )) {
                    $this->sala = $this->container->attributesHandlers['sala']->attribute('contentobject_attribute')->attribute('content');
                }
            }
        }
        return $this->sala;
    }

    private $stuff;

    /**
     * @return eZContentObject[]
     */
    protected function getStuffList()
    {
        if ($this->stuff === null) {
            $stuffList = array();
            if ($this->isValid()) {
                if (isset( $this->container->attributesHandlers['stuff'] )) {
                    $stuffRelationList = $this->container->attributesHandlers['stuff']->attribute('contentobject_attribute')->attribute('content');
                    foreach ($stuffRelationList['relation_list'] as $item) {
                        if (isset($item['contentobject_id'])) {
                            $object = eZContentObject::fetch((int)$item['contentobject_id']);
                            if ($object instanceof eZContentObject) {
                                $stuffList[$item['contentobject_id']]['object'] = $object;
                                if (isset($item['extra_fields']['booking_status']['identifier'])) {
                                    $stuffList[$item['contentobject_id']]['status'] = $item['extra_fields']['booking_status']['identifier'];
                                }else{
                                    $stuffList[$item['contentobject_id']]['status'] = null;
                                }
                            }
                        }
                    }
                }
            }
            $this->stuff = $stuffList;
        }

        return $this->stuff;
    }

    public function hasStuff()
    {
        return (isset( $this->container->attributesHandlers['stuff'] )
                && $this->container->attributesHandlers['stuff']->attribute('has_content'));
    }


    protected function getStuffStatuses()
    {
        $data = array();
        $stuffList = $this->getStuffList();
        foreach($stuffList as $id => $stuff){
            $data[$id] = $stuff['status'];
        }
        return $data;
    }

    protected function isStuffApproved()
    {
        $this->stuff = null; //reset memory
        $stuffList = $this->getStuffList();
        foreach($stuffList as $stuff){
            if ($stuff['status'] != self::STUFF_APPROVED){
                return false;
            }
        }
        return true;
    }

    protected function isStuffNotPending()
    {
        $this->stuff = null; //reset memory
        $stuffList = $this->getStuffList();
        foreach($stuffList as $stuff){
            if ($stuff['status'] == self::STUFF_PENDING){
                return false;
            }
        }
        return true;
    }

    protected function hasManualPrice()
    {
        $sala = $this->getSala();
        if ($sala instanceof eZContentObject) {
            $salaDataMap = $sala->attribute('data_map');
            if (isset( $salaDataMap['manual_price'] ) && $salaDataMap['manual_price']->attribute('data_int') == 1) {
                return true;
            } else {
                return false;
            }
        }
        throw new Exception("Sala non trovata");
    }

    public function addOrder(eZOrder $order)
    {
        if (isset( $this->container->attributesHandlers['order_id'] )) {
            /** @var eZContentObjectAttribute $orderAttribute */
            $orderAttribute = $this->container->attributesHandlers['order_id']->attribute('contentobject_attribute');
            $orderAttribute->fromString($order->attribute('id'));
            $orderAttribute->store();
        }
        if ($order->attribute('status_id') != eZOrderStatus::DELIVERED) {
            $this->changeState(self::STATUS_WAITING_FOR_PAYMENT);
        } else {
            $this->changeState(self::STATUS_APPROVED);
        }
    }

    public function templateDirectory()
    {
        return 'sala_pubblica';
    }

    public function bookableClassIdentifiers()
    {
        return array('sala_pubblica','attrezzatura_sala');
    }

    public function salaPubblicaClassIdentifiers()
    {
        return array('sala_pubblica');
    }

    public function prenotazioneClassIdentifier()
    {
        return 'prenotazione_sala';
    }

    public static function stuffClassIdentifiers()
    {
        return array('attrezzatura_sala');
    }

    public function createObject(eZContentObject $parentObject, $start, $end)
    {
        $object = null;
        if (in_array($parentObject->attribute('class_identifier'), $this->bookableClassIdentifiers())) {
            $classIdentifier = $this->prenotazioneClassIdentifier();
            $class = eZContentClass::fetchByIdentifier($classIdentifier);
            if (!$class instanceof eZContentClass) {
                throw new Exception("Classe $classIdentifier non trovata");
            }

            $languageCode = eZINI::instance()->variable('RegionalSettings', 'Locale');
            $object = eZContentObject::createWithNodeAssignment(
                $this->getBookingLocationNode($parentObject),
                $class->attribute('id'),
                $languageCode,
                false);

            if ($object) {

                /** @var eZContentObjectAttribute[] $parentObjectDataMap */
                $parentObjectDataMap = $parentObject->attribute('data_map');

                $object->setAttribute('section_id',
                    OpenPABase::initSection('Prenotazioni', 'booking')->attribute('id'));
                $object->store();

                /** @var eZContentObjectAttribute[] $dataMap */
                $dataMap = $object->attribute('data_map');

                if (isset( $dataMap['from_time'] ) && $start) {
                    $dataMap['from_time']->fromString($start);
                    $dataMap['from_time']->store();
                }

                if (isset( $dataMap['to_time'] ) && $start) {
                    $dataMap['to_time']->fromString($end);
                    $dataMap['to_time']->store();
                }

                if (isset( $dataMap['stuff'] )
                    && eZHTTPTool::instance()->hasGetVariable('stuff')
                    && OpenPABooking::instance()->isStuffSubWorkflowEnabled()) {

                    $stuffIdList = explode('-', eZHTTPTool::instance()->getVariable('stuff'));
                    $stuffStringParts = array();
                    foreach($stuffIdList as $stuffId){
                        $stuffStringParts[] = "{$stuffId}|booking_status:pending|modified:" . time();
                    }
                    $stuffString = implode('&', $stuffStringParts);

                    $dataMap['stuff']->fromString($stuffString);
                    $dataMap['stuff']->store();
                }

                if (isset( $dataMap['sala'] )) {
                    $dataMap['sala']->fromString($parentObject->attribute('id'));
                    $dataMap['sala']->store();
                } else {
                    throw new Exception("Missing contentclass attribute 'sala' in {$classIdentifier} class");
                }

                if (isset( $dataMap['price'] )) {
                    if (isset( $parentObjectDataMap['price'] )) {
                        $dataMap['price']->fromString($parentObjectDataMap['price']->toString());
                        $dataMap['price']->store();
                    }
                }
            }
        }

        return $object;
    }

    public function subRequestCount()
    {
        if ($this->isValid()) {
            return $this->container->getContentMainNode()->childrenCount();
        }
        return false;
    }

    public function getCalculatedPrice()
    {
        $price = $this->getPrice();

        if ($this->isValid()) {

            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $this->container->getContentObject()->attribute('data_map');
            if ($dataMap['range_user']->hasContent()) {
                $identifier = $dataMap['range_user']->toString();
                $sala = $this->getSala();
                /** @var eZContentObjectAttribute[] $salaDataMap */
                $salaDataMap = $sala->attribute('data_map');
                $priceRangeMatrix = isset( $salaDataMap['price_range'] ) ? $salaDataMap['price_range']->content() : new eZMatrix('null');
                if (isset( $priceRangeMatrix->Matrix['rows'] )) {
                    foreach ((array)$priceRangeMatrix->Matrix['rows']['sequential'] as $row) {
                        if ($row['columns'][0] == $identifier) {
                            $price = floatval($row['columns'][2]);
                        }
                    }
                }
            }
        }

        if ($count = $this->subRequestCount()){
            $price = $price + ($price * $count);
        }
        return $price;
    }

    public function setCalculatedPrice()
    {
        if ($this->isValid() && !$this->hasManualPrice()){
            $price = $this->getCalculatedPrice();
            return $this->setPrice($price);
        }
        return false;
    }

    public function getPrice()
    {
        if ($this->isValid()) {
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $this->container->getContentObject()->attribute('data_map');
            $parts = explode('|', $dataMap['price']->toString());
            return $parts[0];
        }

        return 0;
    }

    public function setPrice($price)
    {
        if ($this->isValid()) {
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $this->container->getContentObject()->attribute('data_map');
            $parts = explode('|', $dataMap['price']->toString());
            $parts[0] = $price;
            $dataMap['price']->fromString(implode('|', $parts));
            $dataMap['price']->store();

            $this->container->getContentObject()->setAttribute('modified', time());
            $this->container->getContentObject()->store();
            eZSearch::addObject($this->container->getContentObject(), true);
            $this->container->getContentObject()->resetDataMap();
            return true;
        }

        return false;
    }

    protected  static function setExtraFieldRelationAttribute(eZContentObjectAttribute $stuffAttribute, eZContentObject $stuff, $key, $value)
    {
        if ($stuffAttribute->attribute('data_type_string') == MugoObjectRelationListType::DATA_TYPE_STRING) {
            $string = $stuffAttribute->toString();

            // Explode out extra fields (attribute-level)
            $splitExtraFields = eZStringUtils::explodeStr($string, MugoObjectRelationListType::EXTRAFIELDSSEPARATOR);

            // Explode by relations
            $relationList = eZStringUtils::explodeStr($splitExtraFields[0],
                MugoObjectRelationListType::RELATIONSEPARATOR);

            $data = array();
            $store = false;
            $beforeStateName = '';

            //Explode by fields
            foreach ($relationList as $relationKey => $relation) {

                $data[$relationKey] = array();

                $extraFields = eZStringUtils::explodeStr($relation, MugoObjectRelationListType::FIELDSEPARATOR);
                $objectID = array_shift($extraFields);

                if ($objectID == $stuff->attribute('id')) {

                    $store = true;

                    $newExtraFields = array();
                    foreach ($extraFields as $extraField) {

                        // Note that it does not enforce whether a field is required
                        $extraFieldElements = eZStringUtils::explodeStr($extraField,
                            MugoObjectRelationListType::OPTIONSEPARATOR);

                        if ($extraFieldElements[0] == $key) {
                            $beforeStateName = $extraFieldElements[1];
                            $newExtraFields[] = eZStringUtils::implodeStr(array($extraFieldElements[0], $value),
                                MugoObjectRelationListType::OPTIONSEPARATOR);
                        } else {
                            $newExtraFields[] = $extraField;
                        }
                    }
                    array_unshift($newExtraFields, $objectID);
                    $data[$relationKey] = eZStringUtils::implodeStr($newExtraFields,
                        MugoObjectRelationListType::FIELDSEPARATOR);
                } else {
                    array_unshift($extraFields, $objectID);
                    $data[$relationKey] = eZStringUtils::implodeStr($extraFields,
                        MugoObjectRelationListType::FIELDSEPARATOR);
                }
            }

            $relationString = eZStringUtils::implodeStr($data, MugoObjectRelationListType::RELATIONSEPARATOR);
            $attributeLevelString = eZStringUtils::implodeStr(array('modified', time()),
                MugoObjectRelationListType::OPTIONSEPARATOR);

            $newString = eZStringUtils::implodeStr(array($relationString, $attributeLevelString),
                MugoObjectRelationListType::EXTRAFIELDSSEPARATOR);

            if ($store) {
                $stuffAttribute->fromString($newString);
                $stuffAttribute->store();
                eZSearch::addObject( $stuffAttribute->object(), true );
                return array(
                    $key . '_before' => $beforeStateName,
                    $key . '_after' => $value
                );
            }
        }

        return false;
    }

    public function createSubRequest(
        eZContentObject $object
    ) {
        /** @var eZContentObjectAttribute[] $dataMap */
        $dataMap = $object->attribute('data_map');
        if (isset( $dataMap['scheduler'] ) && $dataMap['scheduler']->hasContent()) {
            $data = json_decode($dataMap['scheduler']->content(), 1);
            foreach ($data as $item) {

                eZDebug::writeNotice("Create sub request " . var_export($item, 1), __METHOD__);

                $params = array(
                    'class_identifier' => $object->attribute('class_identifier'),
                    'parent_node_id' => $object->attribute('main_node_id'),
                    'attributes' => array(
                        'from_time' => $item['from'] / 1000,
                        'to_time' => $item['to'] / 1000,
                        'stuff' => $dataMap['stuff']->toString(),
                        'sala' => $dataMap['sala']->toString(),
                        'subrequest' => 1
                    )
                );
                $subRequest = eZContentFunctions::createAndPublishObject($params);
                if (!$subRequest instanceof eZContentObject){
                    eZDebug::writeError("Fail on creating subrequest", __METHOD__);
                }

            }
        }
    }

    public function changeStuffApprovalState(eZContentObject $stuff, $status)
    {
        if ($this->isValid()) {
            if (isset( $this->container->attributesHandlers['stuff'] )) {
                /** @var eZContentObjectAttribute $stuffAttribute */

                $stuffAttribute = $this->container->attributesHandlers['stuff']->attribute('contentobject_attribute');

                if ($result = self::setExtraFieldRelationAttribute($stuffAttribute, $stuff, 'booking_status', $status)){

                    if ($this->container->getContentMainNode()->childrenCount()) {
                        /** @var eZContentObjectTreeNode[] $children */
                        $children = $this->container->getContentMainNode()->children();
                        foreach($children as $child){
                            $dataMap = $child->dataMap();
                            if( isset($dataMap['stuff'])){
                                self::setExtraFieldRelationAttribute($dataMap['stuff'], $stuff, 'booking_status', $status);
                            }
                        }
                    }

                    $this->notify('change_stuff_state', array(
                        'stuff_state_before' => $result['booking_status_before'],
                        'stuff_state_after' => $result['booking_status_after'],
                        'stuff' => $stuff
                    ));

                    return true;
                }
            }
        }

        return false;
    }

    public function setOrderStatus($status)
    {
        if ($this->isValid()) {
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $this->container->getContentObject()->attribute('data_map');
            $orderId = $dataMap['order_id']->toString();
            $order = eZOrder::fetch($orderId);
            if ($order instanceof eZOrder) {
                $order->modifyStatus($status);

                return true;
            }
        }

        return false;
    }

    /**
     * @param int|DateTime $start unixtimestamp
     * @param int|DateTime $end unixtimestamp
     * @param eZContentObject $sala
     *
     * @throws Exception
     */
    public function isValidDate($start, $end, eZContentObject $sala)
    {
        if ($start instanceof DateTime) {
            $startDateTime = $start;
        } else {
            $startDateTime = new DateTime('now', new DateTimeZone('Europe/Rome'));
            $startDateTime->setTimestamp($start);
        }

        if ($end instanceof DateTime) {
            $endDateTime = $end;
        } else {
            $endDateTime = new DateTime('now', new DateTimeZone('Europe/Rome'));
            $endDateTime->setTimestamp($end);
        }

        $salaSubtree = array();
        if ($sala instanceof eZContentObject) {
            $salaSubtree[] = $sala->attribute('main_node_id');
        }

        $data = self::fetchConcurrentItems(
            $startDateTime,
            $endDateTime,
            array(self::STATUS_APPROVED),
            $salaSubtree,
            null,
            true
        );

        $locale = eZLocale::instance();
        $dayString = $locale->formatDate($startDateTime->format('U'));
        $dayString .= ' dalle ' . $locale->formatShortTime($startDateTime->format('U')) . ' alle ' . $locale->formatShortTime($endDateTime->format('U'));

        if ($data > 0) {
            throw new Exception("Giorno o orario non disponibile: $dayString");
        }

        $now = time();
        if ($startDateTime->format('U') < $now) {
            throw new Exception("Giorno o orario non prenotabile: $dayString");
        }

        if (!self::isValidDay($startDateTime, $endDateTime, $sala)) {
            throw new Exception("Giorno o orario non disponibile per la sala selezionata: $dayString");
        }
    }

    /**
     * @param DateTime $startDateTime
     * @param DateTime $endDateTime
     * @param eZContentObject $sala
     *
     * @return bool
     */
    public static function isValidDay(DateTime $startDateTime, DateTime $endDateTime, eZContentObject $sala)
    {
        if ($startDateTime >= $endDateTime){
            return false;
        }

        $isInValidOpeningHours = true;
        $openingHours = self::getOpeningHours($sala);        
        if (!empty( $openingHours )) {
            $weekDayNumber = $startDateTime->format('w');            
            if (isset( $openingHours[$weekDayNumber] )) {
                $isInValidOpeningHours = false;
                foreach ($openingHours[$weekDayNumber] as $dayValues) {
                    $testStart = clone $startDateTime;
                    $testStart->setTime($dayValues['from_time']['hour'], $dayValues['from_time']['minute']);
                    $testEnd = clone $endDateTime;
                    $testEnd->setTime($dayValues['to_time']['hour'], $dayValues['to_time']['minute']);
                    $isInRange = $startDateTime >= $testStart && $endDateTime <= $testEnd;                    
                    if ($isInRange) {                        
                        $isInValidOpeningHours = true;
                    }
                }
            } else {
                $isInValidOpeningHours = false;
            }
        }   

        if (!$isInValidOpeningHours){
            return false;
        }

        $closingDays = self::getClosingDays($sala);        
        foreach ($closingDays as $closingDay) {
            if (self::isInClosingDay($closingDay, $startDateTime, $endDateTime)) {
                return false;
            }
        }

        return true;
    }

    public static function isInClosingDay($closingDay, $startDateTime, $endDateTime)
    {
        $isInRange = false;
        if ($closingDay instanceof DateTime){
            $testEnd = clone $closingDay;
            $testEnd->setTime(23, 59);
            $isInRange = $startDateTime >= $closingDay && $endDateTime <= $testEnd;
        }

        return $isInRange;
    }

    public static function init($options = array())
    {
        $parentNodeId = OpenPAAppSectionHelper::instance()->rootNode()->attribute('node_id');
        $rootObject = eZContentObject::fetchByRemoteID(OpenPABooking::rootRemoteId());
        if (!$rootObject instanceof eZContentObject) {

            OpenPALog::warning('Install root');
            // root
            $params = array(
                'parent_node_id' => $parentNodeId,
                'remote_id' => OpenPABooking::rootRemoteId(),
                'class_identifier' => 'booking_root',
                'attributes' => array(
                    'name' => 'Booking',
                    'logo' => 'extension/openpa_booking/design/standard/images/logo_default.png',
                    'logo_title' => 'Prenota[Città]',
                    'logo_subtitle' => 'Prenotazione spazi pubblici',
                    'banner' => null,
                    'banner_title' => "Le [sale pubbliche] del tuo comune",
                    'banner_subtitle' => "... a tua disposizione!",
                    'faq' => '',
                    'privacy' => '',
                    'terms' => '',
                    'footer' => '',
                    'contacts' => ''
                )
            );
            /** @var eZContentObject $rootObject */
            $rootObject = eZContentFunctions::createAndPublishObject($params);
            if (!$rootObject instanceof eZContentObject) {
                throw new Exception('Failed creating Booking root node');
            }
        }

        $object = eZContentObject::fetchByRemoteID(OpenPABooking::locationsRemoteId());
        if (!$object instanceof eZContentObject) {
            OpenPALog::warning(OpenPABooking::locationsRemoteId());
            $params = array(
                'parent_node_id' => $rootObject->attribute('main_node_id'),
                'remote_id' => OpenPABooking::locationsRemoteId(),
                'class_identifier' => 'folder',
                'attributes' => array(
                    'name' => 'Sale pubbliche',
                    'tags' => 'sala_pubblica'
                )
            );
            /** @var eZContentObject $object */
            $object = eZContentFunctions::createAndPublishObject($params);
            if (!$object instanceof eZContentObject) {
                throw new Exception('Failed creating ' . $params['remote_id'] . ' root node');
            }

            /** @var eZContentObjectTreeNode[] $sale */
            $sale = eZContentObjectTreeNode::subTreeByNodeID(array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array('sala_pubblica')
            ), 1);

            foreach ($sale as $sala) {
                OpenPALog::warning("Add assignment to " . $sala->attribute('name'));
                $mainNodeID = $sala->attribute('main_node_id');
                $objectId = $sala->attribute('contentobject_id');
                $targetNodeId = OpenPABooking::locationsNodeId();
                eZContentOperationCollection::addAssignment($mainNodeID, $objectId, array($targetNodeId));
            }
        }

        $object = eZContentObject::fetchByRemoteID(OpenPABooking::stuffRemoteId());
        if (!$object instanceof eZContentObject) {
            OpenPALog::warning(OpenPABooking::stuffRemoteId());
            $params = array(
                'parent_node_id' => $rootObject->attribute('main_node_id'),
                'remote_id' => OpenPABooking::stuffRemoteId(),
                'class_identifier' => 'folder',
                'attributes' => array(
                    'name' => 'Attrezzatura',
                    'tags' => 'attrezzatura_sala'
                )
            );
            /** @var eZContentObject $object */
            $object = eZContentFunctions::createAndPublishObject($params);
            if (!$object instanceof eZContentObject) {
                throw new Exception('Failed creating ' . $params['remote_id'] . ' root node');
            }
        }

        OpenPABooking::moderatorGroupNodeId(true, array(
            'class_identifier' => 'user_group',
            'attributes' => array( 'name' => 'Utenti responsabili' )
        ));


        $self = new self();

        OpenPALog::warning("Init states");
        OpenPABase::initStateGroup(self::$stateGroupIdentifier, self::$stateIdentifiers);

        OpenPALog::warning("Init section");
        $section = OpenPABase::initSection('Prenotazioni', 'booking');

        $classes = array_merge(
            array(
                $self->prenotazioneClassIdentifier(),
            ), $self->bookableClassIdentifiers(), self::stuffClassIdentifiers()
        );

        OpenPALog::warning("ATTENZIONE ALLINEAMENTO CLASSI DISATTIVATO PER QUESTO INSTALLER");
        //foreach ($classes as $identifier) {
        //    $tools = new OpenPAClassTools($identifier, true);
        //    if (!$tools->isValid()) {
        //        $tools->sync(true);
        //        OpenPALog::warning("La classe $identifier è stata aggiornata");
        //    }
        //}

        $stuffClassIdList = array();
        foreach (self::stuffClassIdentifiers() as $stuffIdentifier) {
            $stuffClassIdList[] = eZContentClass::classIDByIdentifier($stuffIdentifier);
        }

        $stuffClassIdList[] = eZContentClass::classIDByIdentifier('associazione');

        OpenPALog::warning("Init roles");
        $prenotazioneClass = eZContentClass::fetchByIdentifier($self->prenotazioneClassIdentifier());

        $parentClasses = array();
        foreach ($self->bookableClassIdentifiers() as $identifier) {
            $class = eZContentClass::fetchByIdentifier($identifier);
            if ($class) {
                $stuffClassIdList[] = $class->attribute('id');
                $parentClasses[] = $class->attribute('id');
            }
        }

        $stuffClassIdList = array_unique($stuffClassIdList);

        $defaultMemberRole = eZRole::fetchByName('Member');

        $policies = array(
            array(
                'ModuleName' => 'user',
                'FunctionName' => 'login',
                'Limitation' => array(
                    'SiteAccess' => eZSys::ezcrc32(OpenPABase::getCustomSiteaccessName('booking', false))
                )
            ),
            array(
                'ModuleName' => 'collaboration',
                'FunctionName' => '*'
            ),
            array(
                'ModuleName' => 'collaboration',
                'FunctionName' => '*'
            ),
            array(
                'ModuleName' => 'openpa_booking',
                'FunctionName' => '*'
            ),
            array(
                'ModuleName' => 'openpa',
                'FunctionName' => 'data'
            ),
            array(
                'ModuleName' => 'shop',
                'FunctionName' => 'buy'
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'create',
                'Limitation' => array(
                    'Class' => $prenotazioneClass->attribute('id'),
                    'ParentClass' => $parentClasses
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => $stuffClassIdList
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Node' => array(
                        OpenPABooking::locationsNodeId(),
                        OpenPABooking::stuffNodeId(),
                    )
                )
            )
        );

        $memberPolicies = $policies;
        $memberPolicies[] = array(
            'ModuleName' => 'content',
            'FunctionName' => 'read',
            'Limitation' => array(
                'Owner' => 1,
                'Class' => $prenotazioneClass->attribute('id'),
                'Section' => $section->attribute('id')
            )
        );

        $memberRole = OpenPABase::initRole(self::ROLE_MEMBER, $memberPolicies, true);
        $defaultUserPlacement = (int)eZINI::instance()->variable("UserSettings", "DefaultUserPlacement");
        $membersGroup = eZContentObject::fetchByNodeID($defaultUserPlacement);
        if ($membersGroup instanceof eZContentObject) {
            $memberRole->assignToUser($membersGroup->attribute('id'));
            if ($defaultMemberRole instanceof eZRole) {
                $defaultMemberRole->assignToUser($membersGroup->attribute('id'));
            }
        }

        $adminPolicies = $policies;
        $adminPolicies[] = array(
            'ModuleName' => 'content',
            'FunctionName' => 'read',
            'Limitation' => array(
                'Class' => $prenotazioneClass->attribute('id'),
                'Section' => $section->attribute('id')
            )
        );
        $adminRole = OpenPABase::initRole(self::ROLE_ADMIN, $adminPolicies, true);
        $adminGroup = eZContentObject::fetchByRemoteID(OpenPABooking::moderatorGroupRemoteId());
        if ($adminGroup instanceof eZContentObject) {
            $adminRole->assignToUser($adminGroup->attribute('id'));
            if ($defaultMemberRole instanceof eZRole) {
                $defaultMemberRole->assignToUser($adminGroup->attribute('id'));
            }
        }

        $anonymousPolicies = array(
            array(
                'ModuleName' => 'user',
                'FunctionName' => 'login',
                'Limitation' => array(
                    'SiteAccess' => eZSys::ezcrc32(OpenPABase::getCustomSiteaccessName('booking', false))
                )
            ),
            array(
                'ModuleName' => 'openpa_booking',
                'FunctionName' => 'read'
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => $stuffClassIdList
                )
            ),
            array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Node' => array(
                        OpenPABooking::locationsNodeId(),
                        OpenPABooking::stuffNodeId(),
                    )
                )
            )
        );;

        $anonymousRole = OpenPABase::initRole(self::ROLE_ANONYM, $anonymousPolicies, true);

        $anonymousUserId = eZINI::instance()->variable('UserSettings', 'AnonymousUserID');
        $anonymousRole->assignToUser($anonymousUserId);

        OpenPALog::error("Attiva il workflow Prenotazioni in post_publish, in pre_delete e in post_checkout");
    }

    public static function getOpeningHours(eZContentObject $object, $attributeIdentifier = 'opening_hours')
    {
        $dataMap = $object->attribute('data_map');
        if (isset( $dataMap[$attributeIdentifier] )
            && $dataMap[$attributeIdentifier] instanceof eZContentObjectAttribute
            && $dataMap[$attributeIdentifier]->attribute('has_content')
        ) {
            $timeTableContent = $dataMap[$attributeIdentifier]->attribute('content')->attribute('matrix');
            $timeTable = array();
            foreach ($timeTableContent['columns']['sequential'] as $column) {
                foreach ($column['rows'] as $row) {
                    $parts = explode('-', $row);
                    if (count($parts) == 2) {
                        $fromParts = explode(':', $parts[0]);
                        if (count($fromParts) != 2) {
                            $fromParts = explode('.', $parts[0]);
                        }

                        $toParts = explode(':', $parts[1]);
                        if (count($toParts) != 2) {
                            $toParts = explode('.', $parts[1]);
                        }

                        if (count($fromParts) == 2 && count($toParts) == 2) {
                            if (!isset( $timeTable[$column['identifier']] )) {
                                $timeTable[$column['identifier']] = array();
                            }
                            $timeTable[$column['identifier']][] = array(
                                'from_time' => array('hour' => trim($fromParts[0]), 'minute' => trim($fromParts[1])),
                                'to_time' => array('hour' => trim($toParts[0]), 'minute' => trim($toParts[1])),
                            );
                        }
                    }
                }
            }

            return $timeTable;
        }

        return array();
    }

    public static function getClosingDays(eZContentObject $object, $attributeIdentifier = 'closing_days')
    {
        $dataMap = $object->attribute('data_map');
        if (isset( $dataMap[$attributeIdentifier] )
            && $dataMap[$attributeIdentifier] instanceof eZContentObjectAttribute
            && $dataMap[$attributeIdentifier]->attribute('has_content')
        ) {
            $closingDaysContent = $dataMap[$attributeIdentifier]->attribute('content')->attribute('matrix');
            $closingDays = array();
            foreach ($closingDaysContent['columns']['sequential'] as $column) {
                foreach ($column['rows'] as $row) {
                    if (!empty( $row )) {
                        $closingDayString = str_replace('/', '-', $row);
                        if (strpos($closingDayString, 'festivi') !== false) {
                            // @todo
                            eZDebug::writeError("@todo implementare soluzione per closing_day 'festivi'");

                        } else {
                            $closingDay = new DateTime($closingDayString);
                            if ($closingDay instanceof DateTime) {
                                $closingDays[] = $closingDay;
                            }
                        }
                    }
                }
            }

            return $closingDays;
        }

        return array();
    }

    public function notify($action, $params = array())
    {
        if ($action == 'change_state' && $params['state_after']->attribute('identifier') == self::$stateIdentifiers[self::STATUS_DENIED]){
            foreach($this->getStuffList() as $stuff){
                $this->changeStuffApprovalState($stuff['object'], self::STUFF_DENIED);
            }
        }
        parent::notify($action, $params);
    }

    /**
     * @param eZContentObject $location
     * @return eZContentObjectTreeNode
     */
    public function getBookingLocationNode(eZContentObject $location)
    {
        $assignedNodes = $location->assignedNodes();
        foreach ($assignedNodes as $node){
            if (in_array($node->attribute('parent_node_id'), array(OpenPABooking::locationsNodeId(), OpenPABooking::stuffNodeId()))){
                return $node;
            }
        }

        return $location->mainNode();
    }
}
