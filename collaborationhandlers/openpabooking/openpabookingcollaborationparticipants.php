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
}
