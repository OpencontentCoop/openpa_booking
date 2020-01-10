<?php

class OpenPABookingIndexPlugin implements ezfIndexPlugin
{
    /**
     * @param eZContentObject $contentObject
     * @param eZSolrDoc[] $docList
     */
    public function modify(eZContentObject $contentObject, &$docList)
    {
        $currentVersion = $contentObject->currentVersion();
        if ($currentVersion === false) {
            return;
        }

        $openpaObject = OpenPAObjectHandler::instanceFromContentObject($contentObject);
        /** @var ObjectHandlerServiceControlBooking $service */
        $service = $openpaObject->attribute('control_booking_sala_pubblica');

        if ($service && $service->isValid()) {

            $collaborationItem = $service->getCollaborationItem();
            if (!$collaborationItem instanceof eZCollaborationItem) {
                /** @var eZContentObjectTreeNode $mainNode */
                $mainNode = $contentObject->mainNode();
                if ($mainNode) {
                    $parentNode = $mainNode->fetchParent();
                    if ($parentNode) {
                        $parentObject = $parentNode->object();
                        $openpaParentObject = OpenPAObjectHandler::instanceFromContentObject($parentObject);
                        /** @var ObjectHandlerServiceControlBookingSalaPubblica $serviceObject */
                        $serviceObject = $openpaParentObject->serviceByClassName('ObjectHandlerServiceControlBookingSalaPubblica');
                        $collaborationItem = $serviceObject->getCollaborationItem();
                    }
                }
            }
            if ($collaborationItem instanceof eZCollaborationItem) {

                $participants = OpenPABookingCollaborationParticipants::instanceFrom($collaborationItem);
                $userIdList = $participants->getUserIdList();
                if (!empty( $userIdList )) {
                    $availableLanguages = $currentVersion->translationList(false, false);
                    foreach ($availableLanguages as $languageCode) {
                        $docList[$languageCode]->addField('extra_booking_users_lk', implode(',', $userIdList));
                    }
                }
            }
        }

    }
}
