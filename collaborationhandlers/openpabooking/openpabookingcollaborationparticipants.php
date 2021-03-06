<?php

class OpenPABookingCollaborationParticipants
{
    private static $instances = array();

    private $usersByRole = array();

    public function addAuthor($id)
    {
        $this->addAuthors(array($id));

        return $this;
    }

    public function addAuthors(array $idList)
    {
        $this->add(eZCollaborationItemParticipantLink::ROLE_AUTHOR, $idList);

        return $this;
    }

    public function addObserver($id)
    {
        $this->addObservers(array($id));

        return $this;
    }

    public function addObservers(array $idList)
    {
        $this->add(eZCollaborationItemParticipantLink::ROLE_OBSERVER, $idList);

        return $this;
    }

    public function addApprover($id)
    {
        $this->addApprovers(array($id));

        return $this;
    }

    public function addApprovers(array $idList)
    {
        $this->add(eZCollaborationItemParticipantLink::ROLE_APPROVER, $idList);

        return $this;
    }

    public function getAuthors()
    {
        return $this->get(eZCollaborationItemParticipantLink::ROLE_AUTHOR);
    }

    public function getObservers()
    {
        return $this->get(eZCollaborationItemParticipantLink::ROLE_OBSERVER);
    }

    public function getApprovers()
    {
        return $this->get(eZCollaborationItemParticipantLink::ROLE_APPROVER);
    }

    public function getList()
    {
        $data = array();
        foreach ($this->usersByRole as $role => $list) {
            $data[] = array(
                'id' => $list,
                'role' => $role
            );
        }

        return $data;
    }

    public function getUserIdList()
    {
        $data = array();
        foreach ($this->usersByRole as $role => $list) {
            $data = array_merge($data, $list);
        }

        return array_unique($data);
    }

    public function subscribeTo(eZCollaborationItem $collaborationItem)
    {
        $collaborationID = $collaborationItem->attribute('id');
        foreach ($this->usersByRole as $role => $list) {
            foreach ($list as $participantID) {
                if ((int)$participantID > 0) {
                    $link = eZCollaborationItemParticipantLink::create(
                        $collaborationID,
                        (int)$participantID,
                        $role,
                        eZCollaborationItemParticipantLink::TYPE_USER
                    );
                    $link->store();
                    $profile = eZCollaborationProfile::instance((int)$participantID);
                    $groupID = $profile->attribute('main_group');
                    eZCollaborationItemGroupLink::addItem($groupID, $collaborationID, (int)$participantID);
                }
            }
        }
        self::$instances[$collaborationItem->attribute('id')] = $this;
        return self::$instances[$collaborationItem->attribute('id')];
    }

    public function activateSubscription(eZCollaborationItem $collaborationItem)
    {
        foreach ($this->usersByRole as $role => $list) {
            foreach ($list as $participantID) {
                $collaborationItem->setIsActive(true, $participantID);
            }
        }

        return $this;
    }

    public function deactivateSubscription(eZCollaborationItem $collaborationItem)
    {
        foreach ($this->usersByRole as $role => $list) {
            foreach ($list as $participantID) {
                $collaborationItem->setIsActive(false, $participantID);
            }
        }

        return $this;
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     *
     * @return OpenPABookingCollaborationParticipants
     */
    public static function instanceFrom(eZCollaborationItem $collaborationItem)
    {
        if (!isset(self::$instances[$collaborationItem->attribute('id')])) {
            $instance = new OpenPABookingCollaborationParticipants();
            $linkList = eZPersistentObject::fetchObjectList(eZCollaborationItemParticipantLink::definition(),
                null,
                array("collaboration_id" => $collaborationItem->attribute('id'))
            );
            foreach ($linkList as $link) {
                $instance->add($link->attribute('participant_role'), $link->attribute('participant_id'));
            }
            self::$instances[$collaborationItem->attribute('id')] = $instance;
        }
        return self::$instances[$collaborationItem->attribute('id')];
    }

    public function currentUserIsParticipant()
    {
        return $this->userIsParticipant(eZUser::currentUserID());
    }

    public function currentUserIsApprover()
    {
        return $this->userIsApprover(eZUser::currentUserID());
    }

    public function currentUserIsAuthor()
    {
        return $this->userIsAuthor(eZUser::currentUserID());
    }

    public function currentUserIsObserver()
    {
        return $this->userIsObserver(eZUser::currentUserID());
    }

    public function userIsParticipant($userID)
    {
        return in_array($userID, $this->getUserIdList());
    }

    public function userIsAuthor($userID)
    {
        return in_array($userID, $this->getAuthors());
    }

    public function userIsObserver($userID)
    {
        return in_array($userID, $this->getObservers());
    }

    public function userIsApprover($userID)
    {
        return in_array($userID, $this->getApprovers());
    }

    private function get($role)
    {
        if (isset( $this->usersByRole[$role] )) {
            return $this->usersByRole[$role];
        }

        return array();
    }

    private function add($role, $idList)
    {
        if (!isset( $this->usersByRole[$role] )) {
            $this->usersByRole[$role] = array();
        }

        if (!is_array($idList)) {
            $idList = array($idList);
        }

        foreach ($idList as $userId) {

            $collaborationNotificationRule = eZPersistentObject::fetchObject(
                eZCollaborationNotificationRule::definition(),
                null,
                array(
                'user_id' => $userId,
                'collab_identifier' => OpenPABookingCollaborationHandler::TYPE_STRING
                )
            );
            if (!$collaborationNotificationRule instanceof eZCollaborationNotificationRule){
                $collaborationNotificationRule =
                    eZCollaborationNotificationRule::create(OpenPABookingCollaborationHandler::TYPE_STRING,
                    $userId
                );
                $collaborationNotificationRule->store();
                eZDebug::writeNotice("Create notification rule for user $userId", __METHOD__);
            }
        }

        $this->usersByRole[$role] = array_unique(
            array_merge(
                $idList,
                $this->usersByRole[$role]
            )
        );
    }

    /**
     * @param int[] $idList
     * @param eZCollaborationItem $collaborationItem
     */
    public static function removeUsersFrom($idList, eZCollaborationItem $collaborationItem)
    {
        if (!is_array($idList)) {
            $idList = array($idList);
        }

        $db = eZDB::instance();
        $db->begin();
        foreach ($idList as $userId) {

            $participant = eZCollaborationItemParticipantLink::fetch(
                $collaborationItem->attribute('id'),
                $userId
            );
            if ($participant instanceof eZCollaborationItemParticipantLink){
                $participant->remove();
            }

            $groupLink = eZPersistentObject::fetchObject(
                eZCollaborationItemGroupLink::definition(),
                null,
                array(
                    'collaboration_id' => $collaborationItem->attribute('id'),
                    'user_id' => $userId
                )
            );
            if ( $groupLink instanceof eZCollaborationItemGroupLink ) {
                $groupLink->remove();
            }

            $itemStatus = eZCollaborationItemStatus::fetch($collaborationItem->attribute('id'), $userId);
            if ($itemStatus instanceof eZCollaborationItemStatus){
                $itemStatus->remove();
            }
        }
        $db->commit();
    }

    public static function refresh(eZContentObject $object, $dryRun = true)
    {
        $result = array();
        if ($object->attribute('status') == eZContentObject::STATUS_PUBLISHED) {
            $openpaObject = OpenPAObjectHandler::instanceFromContentObject($object);
            /** @var ObjectHandlerServiceControlBookingSalaPubblica $service */
            $service = $openpaObject->serviceByClassName('ObjectHandlerServiceControlBookingSalaPubblica');
            if (!$service->isSubrequest()) {

                $result['info'] = $object->attribute('id');

                $collaborationItem = $service->getCollaborationItem();
                if ($collaborationItem) {
                    $ownerId = $object->attribute('owner_id');
                    $participants = OpenPABookingCollaborationParticipants::instanceFrom($collaborationItem);
                    $approverIds = $service->getApproverIds();

                    $registeredApproverIds = $participants->getApprovers();
                    $registeredAuthorsIds = $participants->getAuthors();

                    $result['users'] = array(
                        'approvers ' . implode('-', $approverIds),
                        'authors (' . $ownerId . ') ' . implode('-', $registeredAuthorsIds),
                        'observers ' . implode('-', $participants->getObservers())
                    );

                    $addList = array_diff($approverIds, $registeredApproverIds);
                    $removeList = array_diff($registeredApproverIds, $approverIds);
                    $fixAuthor = !in_array($ownerId, $registeredAuthorsIds) && !in_array($ownerId, $approverIds);

                    if (!empty($addList) || !empty($removeList) || $fixAuthor) {

                        if (!empty($removeList)) {
                            $result['actions'][] = 'remove approvers' . implode('-', $removeList);
                            if (!$dryRun) {
                                OpenPABookingCollaborationParticipants::removeUsersFrom($removeList, $collaborationItem);
                            }
                        }

                        if (!empty($addList) || $fixAuthor) {
                            $addParticipants = new OpenPABookingCollaborationParticipants();
                            if (!empty($addList)) {

                                $result['actions'][] = 'add approvers ' . implode('-', $addList);
                                if (!$dryRun) {
                                    $addParticipants->addApprovers($addList);
                                }

                                if (!empty($removeList)) {
                                    $result['actions'][] = 'add observers ' . implode('-', $removeList);
                                    if (!$dryRun) {
                                        $addParticipants->addObservers($addList);
                                    }
                                }
                            }
                            if ($fixAuthor) {
                                $result['actions'][] = 'add author ' . $ownerId;
                                if (!$dryRun) {
                                    $addParticipants->addAuthor($ownerId);
                                }
                            }
                            if (!$dryRun) {
                                $addParticipants->subscribeTo($collaborationItem);
                            }
                        }

                        if (!$dryRun) {
                            eZSearch::addObject($object, true);
                        }
                    }
                } else {
                    $result['error'] = true;
                }
            }

            $openpaObject->flush(false, false);
        }

        return $result;
    }
}
