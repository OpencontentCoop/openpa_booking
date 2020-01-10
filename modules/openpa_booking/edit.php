<?php

/** @var eZModule $module */
$module = $Params['Module'];
$identifier = $Params['HandlerIdentifier'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

try {
    $handler = OpenPABookingHandler::handler($identifier, $Params);
    $object = $handler->getCurrentObject();
    $openpaObject = OpenPAObjectHandler::instanceFromContentObject($object);
    /** @var ObjectHandlerServiceControlBookingSalaPubblica $serviceObject */
    $serviceObject = $openpaObject->serviceByClassName('ObjectHandlerServiceControlBookingSalaPubblica');
    $currentStateCode = $serviceObject->attribute('current_state_code');
    $start = $serviceObject->attribute('start_timestamp');

    if ($object->attribute('class_identifier') == $serviceObject->prenotazioneClassIdentifier()) {

        $collaborationItem = $serviceObject->getCollaborationItem();
        if (!$collaborationItem instanceof eZCollaborationItem) {
            $parentObject = $object->mainNode()->fetchParent()->object();
            $tpl->setVariable('parent_object', $parentObject);
            $openpaParentObject = OpenPAObjectHandler::instanceFromContentObject($parentObject);
            /** @var ObjectHandlerServiceControlBookingSalaPubblica $serviceObject */
            $serviceObject = $openpaParentObject->serviceByClassName('ObjectHandlerServiceControlBookingSalaPubblica');
            $parentCurrentStateCode = $serviceObject->attribute('current_state_code');
            $collaborationItem = $serviceObject->getCollaborationItem();
        }

        $collaborationItemContent = $collaborationItem->content();

        if (!$collaborationItemContent['is_approver']) {
            throw new Exception('Invalid user');
        }

        if ($currentStateCode > 3) {
            throw new Exception('Invalid status');
        }

        if (isset($parentCurrentStateCode) && $parentCurrentStateCode > 3) {
            throw new Exception('Invalid status');
        }

        if ($start <= time()) {
            throw new Exception('Invalid date');
        }

        if ($http->hasPostVariable('NewDay') && $http->hasPostVariable('NewFrom') && $http->hasPostVariable('NewTo')) {

            /** @var eZContentObject $sala */
            $sala = $serviceObject->attribute('sala');

            try {
                $newFrom = DateTime::createFromFormat('d-m-Y H:i', $http->postVariable('NewDay') . ' ' . $http->postVariable('NewFrom'));
                $newTo = DateTime::createFromFormat('d-m-Y H:i', $http->postVariable('NewDay') . ' ' . $http->postVariable('NewTo'));

                if (!$newFrom instanceof DateTime || !$newTo instanceof DateTime) {
                    throw new Exception("Date non valide");
                }

                if ($newTo < $newFrom) {
                    throw new Exception("Date non consistenti");
                }

                $serviceObject->isValidDate($newFrom, $newTo, $sala, $object->attribute('id'), [
                    ObjectHandlerServiceControlBooking::STATUS_PENDING,
                    ObjectHandlerServiceControlBooking::STATUS_WAITING_FOR_CHECKOUT,
                    ObjectHandlerServiceControlBooking::STATUS_WAITING_FOR_PAYMENT,
                    ObjectHandlerServiceControlBooking::STATUS_APPROVED,
                ]);

                /** @var eZContentObjectAttribute[] $dataMap */
                $dataMap = $object->attribute('data_map');

                $commentText = "La prenotazione #" . $object->attribute('id') . " è stata ricalendarizzata"
                    . " dal " . date('d-m-Y H:i', $dataMap['from_time']->toString()) . ' - ' . date('H:i', $dataMap['to_time']->toString())
                    . " al " . $newFrom->format('d-m-Y H:i') . ' - ' . $newTo->format('d-m-Y H:i');
                $handler->addComment($collaborationItem, $commentText);

                if (isset($dataMap['from_time'])) {
                    $dataMap['from_time']->fromString($newFrom->getTimestamp());
                    $dataMap['from_time']->store();
                }

                if (isset($dataMap['to_time'])) {
                    $dataMap['to_time']->fromString($newTo->getTimestamp());
                    $dataMap['to_time']->store();
                }

                eZSearch::addObject($object, true);

                $module->redirectTo('openpa_booking/view/sala_pubblica/' . $object->attribute('id'));
                return;

            } catch (Exception $e) {
                $tpl->setVariable('error', $e->getMessage());
            }
        }

        $tpl->setVariable('collab_item', $collaborationItem);
        $tpl->setVariable('content_object', $object);
        if (!$serviceObject->getCollaborationItem() instanceof eZCollaborationItem) {
            $tpl->setVariable('parent_object', $object->mainNode()->fetchParent()->object());
        }
        $tpl->setVariable('openpa_object', $openpaObject);

        $Result = array();
        $Result['persistent_variable'] = $tpl->variable('persistent_variable');
        $Result['content'] = $tpl->fetch('design:booking/sala_pubblica/edit.tpl');
        $Result['node_id'] = 0;

        $contentInfoArray = array('url_alias' => 'booking/config');
        $contentInfoArray['persistent_variable'] = false;
        if ($tpl->variable('persistent_variable') !== false) {
            $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
        }
        $Result['content_info'] = $contentInfoArray;
        $Result['path'] = array();


    } else {
        throw new Exception('Invalid object');
    }
} catch (Exception $e) {
    eZDebug::writeError($e->getMessage(), __FILE__);
    return $module->handleError(eZError::KERNEL_ACCESS_DENIED, 'kernel');
}