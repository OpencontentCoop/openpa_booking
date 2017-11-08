<?php

class OpenPABookingSalaPubblicaAvailabilityRequest implements JsonSerializable
{
    /**
     * @var bool
     */
    private $showUnavailable;

    /**
     * @var DateTime
     */
    private $from;

    /**
     * @var DateTime
     */
    private $to;

    /**
     * @var int[] stuff id list
     */
    private $stuff = array();

    /**
     * @var int 1, 2, 3, or 4
     */
    private $places;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var int location id
     */
    private $location;

    public static function getTimeZone()
    {
        return new \DateTimeZone('Europe/Rome'); 
    }

    public static function fromString($request)
    {
        $availabilityRequest = new OpenPABookingSalaPubblicaAvailabilityRequest();

        parse_str($request, $requestData);

        $cleanRequest = array_map(function ($value) {
            return str_replace('*', ' ', $value);
        }, $requestData);

        if (isset( $cleanRequest['show_unavailable'] ) && (int)$cleanRequest['show_unavailable'] == 1) {
            $availabilityRequest->setShowUnavailable(true);
        }

        if (isset( $cleanRequest['from'] ) && isset( $cleanRequest['to'] )) {
            $availabilityRequest->setFrom($cleanRequest['from']);
            $availabilityRequest->setTo($cleanRequest['to']);
        }

        if (isset( $cleanRequest['stuff'] )) {
            if (!empty( $cleanRequest['stuff'] )) {
                $availabilityRequest->setStuff(explode('-', trim($cleanRequest['stuff'])));
            }
        }

        if (isset( $cleanRequest['numero_posti'] )) {
            $availabilityRequest->setPlaces($cleanRequest['numero_posti']);
        }

        if (isset( $cleanRequest['destinazione_uso'] ) && !empty( $cleanRequest['destinazione_uso'] )) {
            $availabilityRequest->setDestination($cleanRequest['destinazione_uso']);
        }

        if (isset( $cleanRequest['location'] )) {
            $availabilityRequest->setLocation($cleanRequest['location']);
        }

        return $availabilityRequest;
    }


    /**
     * @return mixed
     */
    public function isShowUnavailable()
    {
        return $this->showUnavailable;
    }

    /**
     * @param mixed $showUnavailable
     */
    public function setShowUnavailable($showUnavailable)
    {
        $this->showUnavailable = $showUnavailable;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param $from
     *
     * @throws Exception
     */
    public function setFrom($from)
    {
        if ($from instanceof DateTime) {
            $this->from = $from;
        }else{
            $this->from = new \DateTime($from, self::getTimeZone());
            if (!$this->from instanceof \DateTime) {
                throw new Exception("Problem with date {$from}");
            }
        }
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param $to
     *
     * @throws Exception
     */
    public function setTo($to)
    {
        if ($to instanceof DateTime) {
            $this->to = $to;
        }else {
            $this->to = new \DateTime($to, self::getTimeZone());
            if (!$this->to instanceof \DateTime) {
                throw new Exception("Problem with date {$to}");
            }
        }
    }

    /**
     * @return mixed
     */
    public function getStuff()
    {
        return $this->stuff;
    }

    /**
     * @param mixed $stuff
     */
    public function setStuff($stuff)
    {
        $this->stuff = $stuff;
    }

    /**
     * @return mixed
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * @param mixed $places
     */
    public function setPlaces($places)
    {
        switch ($places) {
            case '1':
                $this->places = '[*,100]';
                break;
            case '2':
                $this->places = '[100,200]';
                break;
            case '3':
                $this->places = '[200,400]';
                break;
            case '4':
                $this->places = '[400,*]';
                break;
        }
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param mixed $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = (int)$location;
    }

    public function hasCalendarFilter()
    {
        return $this->getCalendarFilter() != '';
    }

    public function getCalendarFilter()
    {        
        if ($this->from && $this->to) {
            $from = $this->from->add(new DateInterval('PT1S'))->format('Y-m-d H:i');
            $to = $this->to->sub(new DateInterval('PT1S'))->format('Y-m-d H:i');

            return "calendar[] = [{$from},{$to}]";
        }

        return '';
    }

    public function getQueryFilters()
    {
        $filters = array();
        if ($this->getPlaces()){
            $filters[] = 'raw[attr_numero_posti_si] range ' . $this->getPlaces();
        }

        if ($this->getDestination()){
            $filters[] = "destinazione_uso = '" . $this->getDestination() . "'";
        }

        if ($this->getLocation()){
            if ($this->getLocation() > 0){
                $filters[] = 'id = ' . $this->getLocation();
            }
        }

        return implode(' and ', $filters);
    }

    function jsonSerialize()
    {
        return array(
            'from' => $this->getFrom(),
            'to' => $this->getTo(),
            'show_unavailable' => $this->isShowUnavailable(),
            'places' => $this->getPlaces(),
            'destination' => $this->getDestination(),
            'location' => $this->getLocation(),
        );
    }

}
