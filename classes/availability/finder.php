<?php

use Opencontent\Opendata\Api\ContentSearch;
use Opencontent\Opendata\GeoJson\FeatureCollection;
use Opencontent\Opendata\Api\Values\Content;

class OpenPABookingSalaPubblicaAvailabilityFinder
{
    private $filterContent;

    private $language;

    public function __construct()
    {
        $this->filterContent = new BookingEnvironmentSettings();
        $this->language = eZLocale::currentLocaleCode();
    }

    public static function getStateIdList()
    {
        return array(
            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject(ObjectHandlerServiceControlBookingSalaPubblica::STATUS_APPROVED)->attribute('id'),
            //ObjectHandlerServiceControlBookingSalaPubblica::getStateObject( ObjectHandlerServiceControlBookingSalaPubblica::STATUS_DENIED )->attribute( 'id' ),
            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject(ObjectHandlerServiceControlBookingSalaPubblica::STATUS_PENDING)->attribute('id'),
            //ObjectHandlerServiceControlBookingSalaPubblica::getStateObject( ObjectHandlerServiceControlBookingSalaPubblica::STATUS_EXPIRED )->attribute( 'id' ),
            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject(ObjectHandlerServiceControlBookingSalaPubblica::STATUS_WAITING_FOR_CHECKOUT)->attribute('id'),
            ObjectHandlerServiceControlBookingSalaPubblica::getStateObject(ObjectHandlerServiceControlBookingSalaPubblica::STATUS_WAITING_FOR_PAYMENT)->attribute('id')
        );
    }

    public function request(OpenPABookingSalaPubblicaAvailabilityRequest $request)
    {
        $bookingQuery = null;
        $bookedLocations = array();
        $bookedStuff = array();

        $service = new ObjectHandlerServiceControlBookingSalaPubblica();
        $classes = implode(',', $service->bookableClassIdentifiers());

        if ($request->hasCalendarFilter()) {
            $dateFilter = $request->getCalendarFilter();
            $stateIdList = self::getStateIdList();
            $statusFilter = "and state in [" . implode(',', $stateIdList) . "]";

            $bookingQuery = "$dateFilter $statusFilter classes [{$classes}]";
            $bookings = self::findAll($bookingQuery, array());

            foreach ($bookings as $item) {

                $content = new Content($item);
                $booking = $this->filterContent->filterContent($content);

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
                        $bookedLocations[$location['id']][] = array(
                            'id' => $booking['metadata']['id'],
                            'status' => $status,
                            'owner' => $booking['metadata']['ownerId']
                        );
                    }
                }
                if (isset( $booking['data'][$this->language]['stuff'] ) && !empty( $booking['data'][$this->language]['stuff'] )) {
                    foreach ($booking['data'][$this->language]['stuff'] as $stuff) {
                        $bookedStuff[$stuff['id']][] = $stuff['extra']['in_context']['booking_status'];
                    }
                }
            }
        }

        $locationQuery = "{$request->getQueryFilters()} classes [{$classes}]";
        $locations = self::findAll($locationQuery, array());

        $geoJson = new FeatureCollection();
        $availableLocations = array();

        foreach ($locations as $item) {

            $content = new Content($item);

            if ($request->hasCalendarFilter()){
                $object = $content->getContentObject($this->language);
                if (ObjectHandlerServiceControlBookingSalaPubblica::isValidDay($request->getFrom(), $request->getTo(), $object)) {
                    $geoFeature = $content->geoJsonSerialize();
                    $locationAvailabilityInfo = $this->getLocationAvailabilityInfo($content, $bookedLocations);
                    $stuffAvailabilityInfo = $this->getStuffAvailabilityInfo($request->getStuff(), $bookedLocations);
                    $location = $this->filterContent->filterContent($content);
                    $location['is_availability_request'] = true;
                    $location['location_available'] = (bool)$locationAvailabilityInfo['is_available'];
                    $location['location_self_booked'] = (int)$locationAvailabilityInfo['is_self_booked'];
                    $location['location_bookings'] = (int)$locationAvailabilityInfo['booking_count'];
                    $location['location_busy_level'] = (int)$locationAvailabilityInfo['busy_level'];
                    $location['stuff_available'] = (bool)$stuffAvailabilityInfo['is_available'];
                    $location['stuff_bookings'] = $stuffAvailabilityInfo['bookings'];
                    $location['stuff_busy_level'] = $stuffAvailabilityInfo['busy_level'];
                    $location['stuff_global_busy_level'] = $stuffAvailabilityInfo['global_busy_level'];
                    if ($location['location_busy_level'] <= 0 || $request->isShowUnavailable()) {

                        $geoFeature->properties->add('location', $location);
                        if ($geoFeature->geometry->coordinates
                            && ( (int)$geoFeature->geometry->coordinates[0] != 0 && (int)$geoFeature->geometry->coordinates[1] != 0 )
                        ) {
                            $geoJson->add($geoFeature);
                        }
                        $availableLocations[] = $location;
                    }
                }
            } else {
                $geoFeature = $content->geoJsonSerialize();
                $location = $this->filterContent->filterContent($content);
                $location['is_availability_request'] = false;
                $geoFeature->properties->add('location', $location);
                if ($geoFeature->geometry->coordinates
                    && ( (int)$geoFeature->geometry->coordinates[0] != 0 && (int)$geoFeature->geometry->coordinates[1] != 0 )
                ) {
                    $geoJson->add($geoFeature);
                }
                $availableLocations[] = $location;
            }
        }

        $requestArray = $request->jsonSerialize();
        $requestArray['bookings_query'] = $bookingQuery;
        $requestArray['locations_query'] = $locationQuery;

        return array(
            'request' => $requestArray,
            'geojson' => $geoJson,
            'locations' => $availableLocations,
        );
    }

    /**
     * @param $query
     * @param array $limitation
     *
     * @return array()
     */
    public static function findAll($query, array $limitation = null)
    {
        $currentEnvironment = new FullEnvironmentSettings();
        $hits = array();
        $query .= ' and limit ' . $currentEnvironment->getMaxSearchLimit();
        eZDebug::writeNotice($query, __METHOD__);
        while ($query) {
            $results = self::find($query, $limitation);
            $hits = array_merge($hits, $results->searchHits);
            $query = $results->nextPageQuery;
        }

        return $hits;
    }

    private static function find($query, array $limitation = null)
    {
        $contentSearch = new ContentSearch();
        $contentSearch->setCurrentEnvironmentSettings(new FullEnvironmentSettings());
        try {
            return $contentSearch->search($query, $limitation);
        } catch (Exception $e) {
            eZDebug::writeError($query . "\n" . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
            $error = new \Opencontent\Opendata\Api\Values\SearchResults();
            $error->nextPageQuery = null;
            $error->searchHits = array();
            $error->totalCount = 0;
            $error->facets = array();
            $error->query = $query;

            return $error;
        }
    }

    private function getLocationAvailabilityInfo(Content $content, $bookedLocations)
    {
        $isAvailable = true;
        $bookingCount = 0;
        $busyLevel = -1;
        $isSelfBooked = 0;
        foreach ($bookedLocations as $id => $values) {
            if ($id == $content->metadata->id) {
                $isAvailable = false;
                $bookingCount = count($values);
                $busyLevel = 0;
                foreach ($values as $value) {
                    $status = $value['status'];
                    if ($status == ObjectHandlerServiceControlBookingSalaPubblica::STATUS_APPROVED
                        || $status == ObjectHandlerServiceControlBookingSalaPubblica::STATUS_WAITING_FOR_CHECKOUT
                        || $status == ObjectHandlerServiceControlBookingSalaPubblica::STATUS_WAITING_FOR_PAYMENT
                    ) {
                        $busyLevel = 1;
                    }
                    $owner = $value['owner'];
                    if ($owner == eZUser::currentUserID()) {
                        $isSelfBooked = $value['id'];
                    }
                }
            }
        }

        return array(
            'is_available' => $isAvailable,
            'booking_count' => $bookingCount,
            'busy_level' => $busyLevel,
            'is_self_booked' => $isSelfBooked
        );
    }

    private function getStuffAvailabilityInfo($requestStuffIdList, $bookedStuff)
    {

        $isAvailable = true;
        $bookingCount = array();
        $busyLevel = array();
        $globalBusyLevel = -1;

        foreach ($requestStuffIdList as $requestStuffId) {
            $bookingCount[$requestStuffId] = 0;
            $busyLevel[$requestStuffId] = -1;
            if (isset( $bookedStuff[$requestStuffId] )) {
                $isAvailable = false;
                $bookingCount[$requestStuffId] = count($bookedStuff[$requestStuffId]);
                foreach ($bookedStuff[$requestStuffId] as $status) {
                    switch ($status) {
                        case 'pending':
                            $busyLevel[$requestStuffId] = 0;
                            if ($globalBusyLevel < 0) {
                                $globalBusyLevel = 0;
                            }
                            break;

                        case 'approved':
                            $busyLevel[$requestStuffId] = 1;
                            if ($globalBusyLevel < 1) {
                                $globalBusyLevel = 1;
                            }
                            break;

                        case 'denied':
                        case 'expired':
                            break;
                    }
                }
            }
        }

        return array(
            'is_available' => $isAvailable,
            'bookings' => $bookingCount,
            'busy_level' => $busyLevel,
            'global_busy_level' => $globalBusyLevel
        );
    }

}
