<?php

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
                    throw new Exception("Attrezzatura $stuffId non trovata");
                }
            }

            $this->dataFunction = 'getCalendarData';

        }
    }

    public function getData()
    {
        return $this->{$this->dataFunction}();
    }

    private function getAvailability()
    {
        $requestString = urldecode(eZHTTPTool::instance()->getVariable('q', false));
        $finder = new OpenPABookingSalaPubblicaAvailabilityFinder();
        return $finder->request(OpenPABookingSalaPubblicaAvailabilityRequest::fromString($requestString));
    }

    private function getCalendarData()
    {
        $start = eZHTTPTool::instance()->getVariable('start', false);
        $end = eZHTTPTool::instance()->getVariable('end', false);
        $current = eZHTTPTool::instance()->getVariable('current', array());
        $calendarFinder = new OpenPABookingSalaPubblicaCalendar(
            $start,
            $end,
            $this->currentSalaObject,
            $this->currentStuffObject,
            $current,
            function(eZContentObject $object){
                $url = 'openpa_booking/view/sala_pubblica/' . $object->attribute('id');
                eZURI::transformURI($url);

                return $url;
            }
        );

        return $calendarFinder->getData();
    }
}
