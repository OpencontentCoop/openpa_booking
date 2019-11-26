<?php

use Opencontent\Opendata\Api\Values\Content;

class OpenPABookingSalaPubblicaCalendar
{
    private $query;

    private $start;

    private $end;

    private $location;

    private $stuff;

    private $currentBookings = array();

    private $language;

    private $urlTransformerClosure;

    private $showAvailableSlots;

    public function __construct(
        $start,
        $end,
        eZContentObject $location = null,
        eZContentObject $stuff = null,
        $currentBookings = array(),
        Closure $urlTransformerClosure,
        $showAvailableSlots = true
    ) {
        $this->currentBookings = $currentBookings;
        $this->urlTransformerClosure = $urlTransformerClosure;
        $this->language = eZLocale::currentLocaleCode();
        $this->showAvailableSlots = $showAvailableSlots;

        $queryParts = array();

        $this->start = $start;
        $this->end = $end;
        $queryParts[] = "calendar[] = [{$start},{$end}]";

        $stateIdList = OpenPABookingSalaPubblicaAvailabilityFinder::getStateIdList();
        $queryParts[] = "state in [" . implode(',', $stateIdList) . "]";

        $serviceClass = new ObjectHandlerServiceControlBookingSalaPubblica();
        if ($location instanceof eZContentObject) {
            $this->location = $location;
            $locationNodeId = $serviceClass->getBookingLocationNode($location)->attribute('node_id');
            $queryParts[] = "subtree [{$locationNodeId}]";
        }

        if ($stuff instanceof eZContentObject) {
            $this->stuff = $stuff;
            $field = 'raw[' . OpenPASolr::generateSolrSubMetaField('stuff', 'id') . ']';
            $queryParts[] = "$field = '{$stuff->attribute( 'id' )}'";
        }

        $queryParts[] = "classes [prenotazione_sala] sort [from_time=>desc]";

        $this->query = implode(' and ', $queryParts);
    }

    public function getData()
    {
        $data = array();

        $hits = OpenPABookingSalaPubblicaAvailabilityFinder::findAll($this->query, array());
        foreach ($hits as $hit) {
            $item = $this->convertToCalendarItem($hit);
            if ($item) {
                $data[] = $item;
            }
        }

        if ($this->showAvailableSlots) {
            $data = array_merge($data, $this->getAvailableData($this->start, $this->end));
        }

        return $data;
    }

    private function getAvailableData($start, $end)
    {
        $events = array();
        if ($this->location instanceof eZContentObject) {
            $startDate = new DateTime($start, new DateTimeZone('UTC'));
            $endDate = new DateTime($end, new DateTimeZone('UTC'));
            if ($startDate instanceof DateTime && $endDate instanceof DateTime) {

                $openingHours = ObjectHandlerServiceControlBookingSalaPubblica::getOpeningHours($this->location);
                $closingDays = ObjectHandlerServiceControlBookingSalaPubblica::getClosingDays($this->location);

                $byDayInterval = new DateInterval('P1D');
                $byDayPeriod = new DatePeriod($startDate, $byDayInterval, $endDate);
                /** @var DateTime[] $byDayPeriod */
                foreach ($byDayPeriod as $date) {

                    $do = ObjectHandlerServiceControlBookingSalaPubblica::isValidStartDateTime($date, $this->location);

                    if ($do) {
                        if (!empty($closingDays)) {
                            foreach ($closingDays as $closingDay) {
                                if (ObjectHandlerServiceControlBookingSalaPubblica::isInClosingDay($closingDay, $date,
                                    $date)
                                ) {
                                    $do = false;
                                }
                            }
                        }
                    }

                    if ($do) {
                        if (!empty( $openingHours )) {
                            $weekDayNumber = $date->format('w');
                            if (isset( $openingHours[$weekDayNumber] )) {
                                foreach ($openingHours[$weekDayNumber] as $dayValues) {
                                    $testStart = clone $date;
                                    $testStart->setTime($dayValues['from_time']['hour'],
                                        $dayValues['from_time']['minute']);
                                    $testEnd = clone $date;
                                    $testEnd->setTime($dayValues['to_time']['hour'], $dayValues['to_time']['minute']);

                                    $item = new stdClass();
                                    $item->id = md5($testStart->format('U'));
                                    $item->start = $testStart->format('c');
                                    $item->end = $testEnd->format('c');
                                    $item->rendering = 'background';
                                    $item->type = 'availability';
                                    $events[] = $item;

                                    $cloneItem = clone $item;
                                    $cloneItem->id = '_' . $item->id;
                                    $cloneItem->allDay = true;
                                    $events[] = $cloneItem;
                                }
                            }
                        } else {
                            $item = new stdClass();
                            $item->id = md5($date->format('U'));
                            $item->start = $date->format('c');
                            $item->end = $date->setTime(23, 59)->format('c');
                            $item->rendering = 'background';
                            $item->type = 'availability';
                            $events[] = $item;

                            $cloneItem = clone $item;
                            $cloneItem->id = '_' . $item->id;
                            $cloneItem->allDay = true;
                            $events[] = $cloneItem;
                        }

                    }
                }
            }

        }

        return $events;
    }

    private function convertToCalendarItem($hit)
    {
        if ($hit['metadata']['classIdentifier'] == '') { //@todo @workaround
            $hit['metadata']['classIdentifier'] = 'prenotazione_sala';
        }
        $content = new Content($hit);
        $object = $content->getContentObject($this->language);

        $colors = ObjectHandlerServiceControlBookingSalaPubblica::getStateColors();
        $openpaObject = OpenPAObjectHandler::instanceFromObject($object);
        if ($openpaObject->hasAttribute('control_booking_sala_pubblica')
            && $openpaObject->attribute('control_booking_sala_pubblica')->attribute('is_valid')
        ) {
            /** @var ObjectHandlerServiceControlBooking $service */
            $service = $openpaObject->attribute('control_booking_sala_pubblica');

            $state = ObjectHandlerServiceControlBookingSalaPubblica::getStateObject($service->attribute('current_state_code'));
            if ($state instanceof eZContentObjectState) {

                $url = null;
                if (is_callable($this->urlTransformerClosure)) {
                    $urlTransformerClosure = $this->urlTransformerClosure;
                    $url = $urlTransformerClosure($object);
                }
                $item = new stdClass();
                $item->id = $object->attribute('id');
                $item->url = null;
                $item->type = 'booking';
                $item->is_current_user_booking = false;

                $participants = null;
                $collaborationItem = $service->getCollaborationItem();
                if (!$collaborationItem instanceof eZCollaborationItem && $service->isSubrequest()) {
                    $collaborationItem = $service->getMainRequestCollaborationItem();
                }

                if ($collaborationItem instanceof eZCollaborationItem) {
                    $participants = OpenPABookingCollaborationParticipants::instanceFrom($collaborationItem);
                }

                if ($this->stuff instanceof eZContentObject) {
                    $statuses = $service->attribute('stuff_statuses');
                    $stuffStateName = '?';
                    if (isset( $statuses[$this->stuff->attribute('id')] )) {
                        $stuffStateName = $statuses[$this->stuff->attribute('id')];
                    }
                    $item->title = $stuffStateName;
                    if ($stuffStateName == 'pending') {
                        $item->color = $colors[ObjectHandlerServiceControlBooking::STATUS_PENDING];
                    } elseif ($stuffStateName == 'approved') {
                        $item->color = $colors[ObjectHandlerServiceControlBooking::STATUS_APPROVED];
                    } elseif ($stuffStateName == 'denied') {
                        $item->color = $colors[ObjectHandlerServiceControlBooking::STATUS_DENIED];
                    } elseif ($stuffStateName == 'expired') {
                        $item->color = $colors[ObjectHandlerServiceControlBooking::STATUS_EXPIRED];
                    } else {
                        $item->color = $colors['none'];
                    }
                } else {
                    $item->title = $state->attribute('current_translation')->attribute('name');
                    $item->status = $state->attribute('identifier');
                    if (in_array($object->attribute('id'), $this->currentBookings)) {
                        $item->color = $colors['current'];
                        $item->url = $url;
                    } elseif ($participants && $participants->currentUserIsParticipant()) {
                        $item->color = $colors[$service->attribute('current_state_code')];
                        $item->url = $url;
                        $item->is_current_user_booking = true;
                    } else {
                        $item->color = $colors['none'];
                    }
                }

                $item->start = $service->attribute('start')->format('c');
                $item->end = $service->attribute('end')->format('c');
                $item->allDay = $service->attribute('all_day');

                return $item;
            }
        }

        return null;
    }
}
