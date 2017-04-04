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
        $dateFilter = array(
            'or',
            array(
                'and',
                OpenPASolr::generateSolrField('from_time',
                    'date') => '[ ' . ezfSolrDocumentFieldBase::preProcessValue($start->getTimestamp(),
                        'date') . ' TO ' . ezfSolrDocumentFieldBase::preProcessValue($end->getTimestamp(),
                        'date') . ' ]',
                OpenPASolr::generateSolrField('to_time',
                    'date') => '[ ' . ezfSolrDocumentFieldBase::preProcessValue($start->getTimestamp(),
                        'date') . ' TO ' . ezfSolrDocumentFieldBase::preProcessValue($end->getTimestamp(),
                        'date') . ' ]'
            ),
            array(
                'and',
                OpenPASolr::generateSolrField('from_time',
                    'date') => '[ * TO ' . ezfSolrDocumentFieldBase::preProcessValue($start->getTimestamp(),
                        'date') . ' ]',
                OpenPASolr::generateSolrField('to_time',
                    'date') => '[ ' . ezfSolrDocumentFieldBase::preProcessValue($end->getTimestamp(),
                        'date') . ' TO * ]'
            ),
            array(
                'and',
                OpenPASolr::generateSolrField('from_time',
                    'date') => '[ ' . ezfSolrDocumentFieldBase::preProcessValue($start->getTimestamp(),
                        'date') . ' TO ' . ezfSolrDocumentFieldBase::preProcessValue($end->getTimestamp(),
                        'date') . ' ]',
                OpenPASolr::generateSolrField('to_time',
                    'date') => '[ ' . ezfSolrDocumentFieldBase::preProcessValue($end->getTimestamp(),
                        'date') . ' TO * ]'
            ),
            array(
                'and',
                OpenPASolr::generateSolrField('from_time',
                    'date') => '[ * TO ' . ezfSolrDocumentFieldBase::preProcessValue($start->getTimestamp(),
                        'date') . ' ]',
                OpenPASolr::generateSolrField('to_time',
                    'date') => '[ ' . ezfSolrDocumentFieldBase::preProcessValue($start->getTimestamp(),
                        'date') . ' TO ' . ezfSolrDocumentFieldBase::preProcessValue($end->getTimestamp(),
                        'date') . ' ]'
            )
            ,
            array(
                'and',
                OpenPASolr::generateSolrField('from_time',
                    'date') => '[' . ezfSolrDocumentFieldBase::preProcessValue($start->getTimestamp(),
                        'date') . ' TO * ]',
                OpenPASolr::generateSolrField('to_time',
                    'date') => '[ * TO ' . ezfSolrDocumentFieldBase::preProcessValue($end->getTimestamp(),
                        'date') . ' ]'
            )
        );
        $filters[] = $dateFilter;
        $sortBy = array(OpenPASolr::generateSolrField('from_time', 'date') => 'desc', 'published' => 'asc');
        $solrSearch = new eZSolr();
        $search = $solrSearch->search('', array(
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

    /**
     * @return eZContentObject
     */
    protected function getSala()
    {
        $sala = null;
        if ($this->isValid()) {
            if (isset( $this->container->attributesHandlers['sala'] )) {
                $sala = $this->container->attributesHandlers['sala']->attribute('contentobject_attribute')->attribute('content');
            }
        }

        return $sala;
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
            $this->changeState(self::STATUS_WAITING_FOR_PAYMENT, false);
        } else {
            $this->changeState(self::STATUS_APPROVED, false);
        }
    }

    public function templateDirectory()
    {
        return 'sala_pubblica';
    }

    public function salaPubblicaClassIdentifiers()
    {
        return array('sala_pubblica');
    }

    public function prenotazioneClassIdentifier()
    {
        return 'prenotazione_sala';
    }

    public function stuffClassIdentifiers()
    {
        return array('attrezzatura_sala');
    }

    public function createObject(eZContentObject $parentObject, $start, $end)
    {
        $object = null;
        if (in_array($parentObject->attribute('class_identifier'), $this->salaPubblicaClassIdentifiers())) {
            $classIdentifier = $this->prenotazioneClassIdentifier();
            $class = eZContentClass::fetchByIdentifier($classIdentifier);
            if (!$class instanceof eZContentClass) {
                throw new Exception("Classe $classIdentifier non trovata");
            }

            $languageCode = eZINI::instance()->variable('RegionalSettings', 'Locale');
            $object = eZContentObject::createWithNodeAssignment(
                $parentObject->attribute('main_node'),
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

                if (isset( $dataMap['stuff'] ) && eZHTTPTool::instance()->hasGetVariable('stuff')) {
                    $dataMap['stuff']->fromString(eZHTTPTool::instance()->getVariable('stuff'));
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

    public function setPrice($price)
    {
        if ($this->isValid()) {
            $dataMap = $this->container->getContentObject()->attribute('data_map');
            $parts = explode('|', $dataMap['price']->toString());
            $parts[0] = $price;
            $dataMap['price']->fromString(implode('|', $parts));
            $dataMap['price']->store();

            return true;
        }

        return false;
    }

    public function setOrderStatus($status)
    {
        if ($this->isValid()) {
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
        if ($data > 0) {
            throw new Exception("Giorno o orario non disponibile");
        }

        $now = time();
        if ($start < $now) {
            throw new Exception("Giorno o orario non disponibile");
        }

        if (!self::isValidDay($startDateTime, $endDateTime, $sala)) {
            throw new Exception("Giorno o orario non disponibile");
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
        $openingHours = self::getOpeningHours($sala);
        if (!empty( $openingHours )) {
            $weekDayNumber = $startDateTime->format('w');
            if (isset( $openingHours[$weekDayNumber] )) {
                foreach ($openingHours[$weekDayNumber] as $dayValues) {
                    $testStart = clone $startDateTime;
                    $testStart->setTime($dayValues['from_time']['hour'], $dayValues['from_time']['minute']);
                    $testEnd = clone $endDateTime;
                    $testEnd->setTime($dayValues['to_time']['hour'], $dayValues['to_time']['minute']);
                    $isInRange = $startDateTime >= $testStart && $endDateTime <= $testEnd;
                    if (!$isInRange) {
                        return false;
                    }
                }
            } else {
                return false;
            }
        }
        $closingDays = self::getClosingDays($sala);
        foreach ($closingDays as $closingDay) {
            if (self::isInClosingDay($closingDay, $startDateTime, $endDateTime)) {
                return false;
            }
        }

        return true;
    }

    private static function isInClosingDay($closingDay, $startDateTime, $endDateTime)
    {
        $isInRange = false;
        if (strpos($closingDay, 'festivi') !== false) {

            // @todo
            eZDebug::writeError("@todo implementare soluzione per closing_day 'festivi'");

        } else {
            $closingDay = str_replace('/', '-', $closingDay);
            $testStart = new DateTime($closingDay);
            if ($testStart instanceof DateTime) {
                $testEnd = clone $testStart;
                $testEnd->setTime(23, 59);
                $isInRange = $startDateTime >= $testStart && $endDateTime <= $testEnd;
            }
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


        $self = new self();

        OpenPALog::warning("Init states");
        OpenPABase::initStateGroup(self::$stateGroupIdentifier, self::$stateIdentifiers);

        OpenPALog::warning("Init section");
        $section = OpenPABase::initSection('Prenotazioni', 'booking');

        $classes = array_merge(
            array(
                $self->prenotazioneClassIdentifier(),
            ), $self->salaPubblicaClassIdentifiers(), $self->stuffClassIdentifiers()
        );

        OpenPALog::warning("Update classes");
        foreach ($classes as $identifier) {
            $tools = new OpenPAClassTools($identifier, true);
            if (!$tools->isValid()) {
                $tools->sync(true);
                OpenPALog::warning("La classe $identifier è stata aggiornata");
            }
        }

        $stuffClassIdList = array();
        foreach ($self->stuffClassIdentifiers() as $stuffIdentifier) {
            $stuffClassIdList[] = eZContentClass::classIDByIdentifier($stuffIdentifier);
        }

        OpenPALog::warning("Init roles");
        $prenotazioneClass = eZContentClass::fetchByIdentifier($self->prenotazioneClassIdentifier());

        $classes = $parentClasses = array();
        foreach ($self->salaPubblicaClassIdentifiers() as $identifier) {
            $class = eZContentClass::fetchByIdentifier($identifier);
            if ($class) {
                $classes[$identifier] = $class;
                $parentClasses[] = $class->attribute('id');
            }
        }

        $policies = array(
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

        $roleName = 'Booking Member';
        $role = eZRole::fetchByName($roleName);
        if (!$role instanceof eZRole) {
            $role = eZRole::create($roleName);
            $role->store();

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

            foreach ($memberPolicies as $policy) {
                $role->appendPolicy($policy['ModuleName'], $policy['FunctionName'], $policy['Limitation']);
            }

            $defaultUserPlacement = (int)eZINI::instance()->variable("UserSettings", "DefaultUserPlacement");
            $membersGroup = eZContentObject::fetchByNodeID($defaultUserPlacement);
            if ($membersGroup instanceof eZContentObject) {
                $role->assignToUser($membersGroup->attribute('id'));
            }
        }

        $roleName = 'Booking Admin';
        $role = eZRole::fetchByName($roleName);
        if (!$role instanceof eZRole) {
            $role = eZRole::create($roleName);
            $role->store();

            $adminPolicies = $policies;
            $adminPolicies[] = array(
                'ModuleName' => 'content',
                'FunctionName' => 'read',
                'Limitation' => array(
                    'Class' => $prenotazioneClass->attribute('id'),
                    'Section' => $section->attribute('id')
                )
            );

            foreach ($adminPolicies as $policy) {
                $role->appendPolicy($policy['ModuleName'], $policy['FunctionName'], $policy['Limitation']);
            }
        }

        $roleName = 'Booking Anonymous';
        $role = eZRole::fetchByName($roleName);
        if (!$role instanceof eZRole) {
            $role = eZRole::create($roleName);
            $role->store();

            $policies = array(
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

            foreach ($policies as $policy) {
                $role->appendPolicy($policy['ModuleName'], $policy['FunctionName'], $policy['Limitation']);
            }

            $anonymousUserId = eZINI::instance()->variable('UserSettings', 'AnonymousUserID');
            $role->assignToUser($anonymousUserId);
        }

        OpenPALog::error("Attiva il workflow Prenotazioni in post_publish, in pre_delete e in post_checkout");
    }

    private static function getOpeningHours(eZContentObject $object, $attributeIdentifier = 'opening_hours')
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

    private static function getClosingDays(eZContentObject $object, $attributeIdentifier = 'closing_days')
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
                    if (!empty( $row )) {
                        $timeTable[] = $row;
                    }
                }
            }

            return $timeTable;
        }

        return array();
    }
}
