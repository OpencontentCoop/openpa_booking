<?php

use Opencontent\Opendata\Api\ContentSearch;
use Opencontent\Opendata\GeoJson\FeatureCollection;
use Opencontent\Opendata\GeoJson\Feature;
use Opencontent\Opendata\Api\Values\Content;


class DataHandlerBookingSalaPubblica implements OpenPADataHandlerInterface
{
    /**
     * @var eZContentObject
     */
    private $currentSalaObject;

    /**
     * @var eZContentObject
     */
    private $currentStuffObject;

    private $dataFunction;

    private $language = 'ita-IT';

    public function __construct(array $Params)
    {
        if (isset( $Params['UserParameters']['availability'] )) {
            $this->dataFunction = 'getAvailability';
        } else {
            $salaId = eZHTTPTool::instance()->getVariable('sala', false);
            if ($salaId) {
                $this->currentSalaObject = eZContentObject::fetch(intval($salaId));
                if (!$this->currentSalaObject instanceof eZContentObject) {
                    throw new Exception("Sala pubblica $salaId non trovata");
                }
            }
            $stuffId = eZHTTPTool::instance()->getVariable('stuff', false);
            if ($stuffId) {
                $this->currentStuffObject = eZContentObject::fetch(intval($stuffId));
                if (!$this->currentStuffObject instanceof eZContentObject) {
                    throw new Exception("Attrezzatura $salaId non trovata");
                }
            }

            $this->dataFunction = 'getCalendarData';

        }
    }

    public function getData()
    {
        return $this->{$this->dataFunction}();
    }

    /**
     * @param $query
     * @param array $limitation
     * @param bool $asObject
     * @param null $languageCode
     *
     * @return array()
     */
    private function findAll($query, array $limitation = null, $asObject = true, $languageCode = null)
    {
        $currentEnvironment = new FullEnvironmentSettings();
        $hits = array();
        $query .= ' and limit ' . $currentEnvironment->getMaxSearchLimit();
        eZDebug::writeNotice($query, __METHOD__);
        while ($query) {
            $results = $this->search($query, $limitation);
            $hits = array_merge($hits, $results->searchHits);
            $query = $results->nextPageQuery;
        }

        return $hits;
    }

    private function search($query, array $limitation = null)
    {
        $contentSearch = new ContentSearch();
        $contentSearch->setCurrentEnvironmentSettings(new FullEnvironmentSettings());
        try {
            return $contentSearch->search($query, $limitation);
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
            $error = new \Opencontent\Opendata\Api\Values\SearchResults();
            $error->nextPageQuery = null;
            $error->searchHits = array();
            $error->totalCount = 0;
            $error->facets = array();
            $error->query = $query;

            return $error;
        }
    }

    private function getAvailabilityRequest()
    {
        $request = urldecode(eZHTTPTool::instance()->getVariable('q', false));
        parse_str($request, $requestData);
        $cleanRequestData = function ($value) {
            return str_replace('*', ' ', $value);
        };
        $requestData = array_map($cleanRequestData, $requestData);
        if (isset( $requestData['stuff'] )) {
            $stuffList = array();
            if (!empty( $requestData['stuff'] )) {
                $stuffList = explode('-', trim($requestData['stuff']));
            }
            $requestData['stuff'] = $stuffList;
        } else {
            $requestData['stuff'] = array();
        }
        $filters = array();
        if (isset( $requestData['numero_posti'] )) {
            switch ($requestData['numero_posti']) {
                case '1':
                    $filters[] = 'raw[attr_numero_posti_si] range [*,100]';
                    break;
                case '2':
                    $filters[] = 'raw[attr_numero_posti_si] range [100,200]';
                    break;
                case '3':
                    $filters[] = 'raw[attr_numero_posti_si] range [200,400]';
                    break;
                case '4':
                    $filters[] = 'raw[attr_numero_posti_si] range [400,*]';
                    break;
            }
        }
        if (isset( $requestData['location'] )) {
            $location = (int)$requestData['location'];
            if ($location > 0) {
                $filters[] = 'id = ' . $location;
            }
        }

        $requestData['filter_query'] = implode(' and ', $filters);

        return $requestData;
    }

    private function getAvailability()
    {
        $filterContent = new DefaultEnvironmentSettings();

        $request = $this->getAvailabilityRequest();

        $dateFilter = "calendar[] = [{$request['from']},{$request['to']}]";

        $stateIdList = array(
            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject(ObjectHandlerServiceControlBookingSalaPubblica::STATUS_APPROVED)->attribute('id'),
            //            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject( ObjectHandlerServiceControlBookingSalaPubblica::STATUS_DENIED )->attribute( 'id' ),
            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject(ObjectHandlerServiceControlBookingSalaPubblica::STATUS_PENDING)->attribute('id'),
            //            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject( ObjectHandlerServiceControlBookingSalaPubblica::STATUS_EXPIRED )->attribute( 'id' ),
            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject(ObjectHandlerServiceControlBookingSalaPubblica::STATUS_WAITING_FOR_CHECKOUT)->attribute('id'),
            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject(ObjectHandlerServiceControlBookingSalaPubblica::STATUS_WAITING_FOR_PAYMENT)->attribute('id')
        );
        $statusFilter = "and state in [" . implode(',', $stateIdList) . "]";

        $bookingQuery = "$dateFilter $statusFilter classes [prenotazione_sala]";
        $bookings = $this->findAll($bookingQuery, array());

        $bookedLocations = array();
        $bookedStuff = array();
        foreach ($bookings as $item) {

            $content = new Content($item);
            $booking = $filterContent->filterContent($content);

            $status = array_reduce($booking['metadata']['stateIdentifiers'], function ($carry, $item) {
                $carry = '';
                if (strpos($item, 'booking') === 0) {
                    $carry = str_replace('booking.', '', $item);
                }

                return $carry;
            });
            $status = ObjectHandlerServiceControlBookingSalaPubblica::getStateCodeFromIdentifier($status);

            if (isset( $booking['data'][$this->language]['sala'] ) && !empty( $booking['data'][$this->language]['sala'] )) {
                foreach ($booking['data'][$this->language]['sala'] as $location) {
                    if (!isset( $bookedLocations[$location['id']] )) {
                        $bookedLocations[$location['id']] = array();
                    }
                    $bookedLocations[$location['id']][] = $status;
                }
            }
            if (isset( $booking['data'][$this->language]['stuff'] ) && !empty( $booking['data'][$this->language]['stuff'] )) {
                foreach ($booking['data'][$this->language]['stuff'] as $stuff) {
                    $bookedStuff[$stuff['id']][] = $status;
                }
            }
        }

        $service = new ObjectHandlerServiceControlBookingSalaPubblica();
        $classes = implode(',', $service->salaPubblicaClassIdentifiers());

        $locationQuery = "{$request['filter_query']} classes [{$classes}]";
        $locations = $this->findAll($locationQuery, array());
        $availableLocations = array();
        $geoJson = new FeatureCollection();
        if (eZINI::instance()->variable('DebugSettings', 'DebugOutput') == 'enabled') {
            $geoJson->debug = array(
                'bookings_query' => $bookingQuery,
                'locations_query' => $locationQuery
            );
        }
        foreach ($locations as $item) {

            $content = new Content($item);

            $object = $content->getContentObject($this->language);
            if (ObjectHandlerServiceControlBookingSalaPubblica::isValidDay(new DateTime($request['from']),
                new DateTime($request['to']), $object)
            ) {
                $geoFeature = $content->geoJsonSerialize();
                $location = $filterContent->filterContent($content);
                $location['location_available'] = (bool)!isset( $bookedLocations[$location['metadata']['id']] );
                $location['location_bookings'] = (int)isset( $bookedLocations[$location['metadata']['id']] ) ? count($bookedLocations[$location['metadata']['id']]) : 0;
                $location['location_busy_level'] = isset( $bookedLocations[$location['metadata']['id']] ) ? array_sum($bookedLocations[$location['metadata']['id']]) : -1;
                $location['stuff_available'] = (bool)count(array_intersect(array_keys($bookedStuff),
                        $request['stuff'])) == 0;
                $location['stuff_bookings'] = array();
                $location['stuff_busy_level'] = array();
                foreach ($request['stuff'] as $requestStaff) {
                    $location['stuff_bookings'][$requestStaff] = (int)isset( $bookedStuff[$requestStaff] ) ? count($bookedStuff[$requestStaff]) : 0;
                    $location['stuff_busy_level'][$requestStaff] = (int)isset( $bookedStuff[$requestStaff] ) ? array_sum($bookedStuff[$requestStaff]) : -1;
                }
                $location['stuff_global_busy_level'] = array_sum($location['stuff_busy_level']);
                if ($location['location_busy_level'] <= 0) {
                    $availableLocations[] = $location;
                    $geoFeature->properties->add('content', $location);
                    $geoJson->add($geoFeature);
                }
            }
        }

        return $geoJson;
    }

    private function getCalendarData()
    {
        $data = array();

        $queryParts = array();

        $from = eZHTTPTool::instance()->getVariable('start', false);
        $end = eZHTTPTool::instance()->getVariable('end', false);
        $queryParts['calendar'] = "calendar[] = [{$from},{$end}]";

        $stateIdList = eZHTTPTool::instance()->getVariable('states', array(
            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject(ObjectHandlerServiceControlBookingSalaPubblica::STATUS_APPROVED)->attribute('id'),
            //          ObjectHandlerServiceControlBookingSalaPubblica::getStateObject( ObjectHandlerServiceControlBookingSalaPubblica::STATUS_DENIED )->attribute( 'id' ),
            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject(ObjectHandlerServiceControlBookingSalaPubblica::STATUS_PENDING)->attribute('id'),
            //          ObjectHandlerServiceControlBookingSalaPubblica::getStateObject( ObjectHandlerServiceControlBookingSalaPubblica::STATUS_EXPIRED )->attribute( 'id' ),
            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject(ObjectHandlerServiceControlBookingSalaPubblica::STATUS_WAITING_FOR_CHECKOUT)->attribute('id'),
            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject(ObjectHandlerServiceControlBookingSalaPubblica::STATUS_WAITING_FOR_PAYMENT)->attribute('id')
        ));
        $queryParts['state'] = "state in [" . implode(',', $stateIdList) . "]";

        if ($this->currentSalaObject instanceof eZContentObject) {
            $queryParts['subree'] = "subtree [{$this->currentSalaObject->attribute( 'main_node_id' )}]";
        }

        if ($this->currentStuffObject instanceof eZContentObject) {
            $field = 'raw[' . OpenPASolr::generateSolrSubMetaField('stuff', 'id') . ']';
            $queryParts['stuff'] = "$field = '{$this->currentStuffObject->attribute( 'id' )}'";
        }

        $queryParts['sort'] = "classes [prenotazione_sala] sort [from_time=>desc]";

        $query = implode(' and ', $queryParts);

        $hits = $this->findAll($query, array());

        foreach ($hits as $hit) {
            $item = $this->convertToCalendarItem($hit);
            if ($item) {
                $data[] = $item;
            }
        }

        return $data;
    }

    private function convertToCalendarItem($hit)
    {
        $current = eZHTTPTool::instance()->getVariable('current', array());
        $content = new Content($hit);
        $object = $content->getContentObject($this->language);

        $colors = ObjectHandlerServiceControlBookingSalaPubblica::getStateColors();
        $openpaObject = OpenPAObjectHandler::instanceFromObject($object);
        if ($openpaObject->hasAttribute('control_booking_sala_pubblica')
            && $openpaObject->attribute('control_booking_sala_pubblica')->attribute('is_valid')
        ) {
            $state = ObjectHandlerServiceControlBookingSalaPubblica::getStateObject($openpaObject->attribute('control_booking_sala_pubblica')->attribute('current_state_code'));
            if ($state instanceof eZContentObjectState) {
                $url = 'openpa_booking/view/sala_pubblica/' . $object->attribute('id');
                eZURI::transformURI($url);
                $item = new stdClass();
                $item->id = $object->attribute('id');
                $item->url = $url;

                if ($this->currentStuffObject instanceof eZContentObject) {
                    $statuses = $openpaObject->attribute('control_booking_sala_pubblica')->attribute('stuff_statuses');
                    $stuffStateName = '?';
                    if (isset($statuses[$this->currentStuffObject->attribute('id')])){
                        $stuffStateName = $statuses[$this->currentStuffObject->attribute('id')];
                    }
                    $item->title = $stuffStateName;
                    if ($stuffStateName == 'pending'){
                        $item->color = $colors[ObjectHandlerServiceControlBooking::STATUS_PENDING];
                    }elseif ($stuffStateName == 'approved'){
                        $item->color = $colors[ObjectHandlerServiceControlBooking::STATUS_APPROVED];
                    }elseif ($stuffStateName == 'denied'){
                        $item->color = $colors[ObjectHandlerServiceControlBooking::STATUS_DENIED];
                    }elseif ($stuffStateName == 'expired'){
                        $item->color = $colors[ObjectHandlerServiceControlBooking::STATUS_EXPIRED];
                    }else{
                        $item->color = $colors['none'];
                    }
                }else{
                    $item->title = $state->attribute('current_translation')->attribute('name');
                    if (in_array($object->attribute('id'), $current)) {
                        $item->color = $colors['current'];
                        //$item->title = $object->attribute( 'owner' )->attribute( 'name' );
                    } elseif ($object->attribute('can_read')) {
                        $item->color = $colors[$openpaObject->attribute('control_booking_sala_pubblica')->attribute('current_state_code')];
                    } else {
                        $item->color = $colors['none'];
                    }
                }

                $item->start = $openpaObject->attribute('control_booking_sala_pubblica')->attribute('start')->format('c');
                $item->end = $openpaObject->attribute('control_booking_sala_pubblica')->attribute('end')->format('c');
                $item->allDay = $openpaObject->attribute('control_booking_sala_pubblica')->attribute('all_day');

                return $item;
            }
        }

        return null;
    }
}
