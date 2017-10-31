<?php

use Opencontent\Opendata\Rest\Client\PayloadBuilder;

class BookingApiBookingRequest implements JsonSerializable
{
    /**
     * @var int
     */
    private $locationId;

    /**
     * @var BookingApiBookingRequestDate[]
     */
    private $dates = array();

    /**
     * @var string
     */
    private $purposeDescription;

    /**
     * @var string
     */
    private $userType;

    /**
     * @var int
     */
    private $associationId;

    /**
     * @var string
     */
    private $recipientsDescription;

    /**
     * @var boolean
     */
    private $patronageRequired;

    /**
     * @var boolean
     */
    private $communicationServicesRequired;

    public static function fromHash(array $data)
    {
        $request = new BookingApiBookingRequest();
        foreach ($data as $key => $value) {
            if (property_exists($request, $key)) {
                if (is_array($value)) {
                    $dates = array();
                    foreach ($value as $item) {
                        $dates[] = BookingApiBookingRequestDate::fromHash($item);
                    }
                    $value = $dates;
                }
                $request->{$key} = $value;
            }
        }

        return $request;
    }

    public function validate()
    {
        $serviceClass = new ObjectHandlerServiceControlBookingSalaPubblica();

        if ((int)$this->locationId == 0) {
            throw new Exception("Location not found");
        }

        $location = eZContentObject::fetch((int)$this->locationId);
        if ($location instanceof eZContentObject) {
            if (!$location->attribute('can_read')) {
                throw new Exception("User can not read location {$location->attribute( 'id' )}");
            }
        } else {
            throw new Exception("Location {$this->locationId} not found");
        }

        $classes = $serviceClass->bookableClassIdentifiers();
        if (!in_array($location->attribute('class_identifier'), $classes)) {
            throw new Exception("Location not valid");
        }

        if ((int)$this->associationId > 0) {
            $association = eZContentObject::fetch((int)$this->associationId);
            if (!$association instanceof eZContentObject) {
                throw new Exception("Association {$this->associationId} not found");
            }
            if ($association->attribute('class_identifier') != 'associazione') {
                throw new Exception("Association {$this->associationId} not found");
            }
        }

        if (empty( $this->purposeDescription )) {
            throw new Exception("Purpose description not found");
        }

        $locationDataMap = $location->dataMap();
        if (isset( $locationDataMap['price_range'] )
            && $locationDataMap['price_range']->hasContent()
        ) {
            if (empty( $this->userType )) {
                throw new Exception("User type not found");
            }

            $price = false;
            /** @var eZMatrix $priceRangeMatrix */
            $priceRangeMatrix = $locationDataMap['price_range']->content();
            if (isset( $priceRangeMatrix->Matrix['rows'] )) {
                foreach ((array)$priceRangeMatrix->Matrix['rows']['sequential'] as $row) {
                    if ($row['columns'][0] == $this->userType) {
                        $price = floatval($row['columns'][2]);
                    }
                }
            }
            if ($price === false){
                throw new Exception("User type {$this->userType} not allowed");
            }
        }

        if (empty( $this->dates )) {
            throw new Exception("Dates not found");
        }

        foreach ($this->dates as $date) {
            $date->validate();
            $serviceClass->isValidDate($date->getStartDateTime(), $date->getEndDateTime(), $location);
        }
    }

    public function getPayload()
    {
        $serviceClass = new ObjectHandlerServiceControlBookingSalaPubblica();
        $parentObject = eZContentObject::fetch((int)$this->locationId);
        /** @var eZContentObjectAttribute[] $parentObjectDataMap */
        $parentObjectDataMap = $parentObject->attribute('data_map');

        $mainDate = array_shift($this->dates);

        $payload = new PayloadBuilder();
        $payload->setParentNode($parentObject->mainNodeID());
        $payload->setClassIdentifier($serviceClass->prenotazioneClassIdentifier());
        $payload->setLanguages(array(eZINI::instance()->variable('RegionalSettings', 'Locale')));
        $payload->setSectionIdentifier(OpenPABase::initSection('Prenotazioni', 'booking')->attribute('identifier'));
        $payload->setData(null, 'from_time', $mainDate->getStartDateTime()->format(DATE_ISO8601));
        $payload->setData(null, 'to_time', $mainDate->getEndDateTime()->format(DATE_ISO8601));
        $payload->setData(null, 'sala', array((int)$parentObject->attribute('id')));
        if (isset( $parentObjectDataMap['price'] )) {

            /** @var eZPrice $parentPrice */
            $parentPrice = $parentObjectDataMap['price']->content();
            $price = array(
                'value' => number_format((float)$parentPrice->attribute('price'), 2),
                'vat_id' => (int)$parentPrice->attribute('selected_vat_type')->attribute('id'),
                'is_vat_included' => (int)$parentPrice->attribute('is_vat_included')
            );
            $payload->setData(null, 'price', $price);
        }

        $payload->setData(null, 'text', $this->purposeDescription);
        $payload->setData(null, 'range_user', $this->userType);
        $payload->setData(null, 'destinatari', $this->recipientsDescription);
        if ($this->associationId) {
            $payload->setData(null, 'associazione', array($this->associationId));
        }
        $payload->setData(null, 'patrocinio', (bool)$this->patronageRequired);
        $payload->setData(null, 'comunicazione', (bool)$this->communicationServicesRequired);

        if (!empty( $this->dates )) {
            $scheduler = array();
            foreach ($this->dates as $date) {
                $scheduler[] = array(
                    'from' => $date->getStartMilliseconds(),
                    'to' => $date->getEndMilliseconds(),
                    'stuff' => null, //@todo,
                    'content' => $date->getSchedulerContent()
                );
            }
            $payload->setData(null, 'scheduler', json_encode($scheduler));
        }

        return $payload;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

}

class BookingApiBookingRequestDate implements JsonSerializable
{
    private $start;

    private $end;

    public static function fromHash(array $data)
    {
        $request = new BookingApiBookingRequestDate();
        foreach ($data as $key => $value) {
            if (property_exists($request, $key)) {
                $request->{$key} = $value;
            }
        }

        return $request;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    public function getStartDateTime()
    {
        $startDateTime = new DateTime('now', new DateTimeZone('Europe/Rome'));
        $startDateTime->setTimestamp($this->start);

        return $startDateTime;
    }

    public function getStartMilliseconds()
    {
        return $this->start * 1000;
    }

    /**
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    public function getEndDateTime()
    {
        $endDateTime = new DateTime('now', new DateTimeZone('Europe/Rome'));
        $endDateTime->setTimestamp($this->end);

        return $endDateTime;
    }

    public function getEndMilliseconds()
    {
        return $this->end * 1000;
    }

    public function validate()
    {
        if ($this->getStartDateTime() > $this->getEndDateTime()) {
            throw new Exception("The start date is longer than the end date");
        }

        if ($this->getStartDateTime()->format('Y-m-d') != $this->getEndDateTime()->format('Y-m-d')) {
            throw new Exception("Multi-day booking is not available");
        }
    }

    public function getSchedulerContent($stuff = null)
    {
        if (!$stuff) {
            $stuff = 'null';
        }
        $locale = eZLocale::instance();
        $dayString = $locale->formatDate($this->getStart());
        $hourString = 'dalle ' . $locale->formatShortTime($this->getStart()) . ' alle ' . $locale->formatShortTime($this->getEnd());
        $startMilliSeconds = $this->getStartMilliseconds();
        $endMilliSeconds = $this->getEndMilliseconds();
        return "<label>" .
               "<input type=\"checkbox\" data-from=\"{$startMilliSeconds}\" data-to=\"{$endMilliSeconds}\" data-stuff=\"{$stuff}\" checked=\"checked\"> " .
               "<span class=\"booking_date\"><span>{$dayString}</span> <span class=\"booking_hours\">{$hourString}</span></span>" .
               "</label>";
    }

}
