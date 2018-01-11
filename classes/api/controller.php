<?php

use Opencontent\Opendata\Api\Exception\NotAllowedException;
use Opencontent\Opendata\Api\Exception\NotFoundException;
use Opencontent\Opendata\Api\EnvironmentLoader;
use Opencontent\Opendata\Api\ContentRepository;
use Opencontent\Opendata\Api\ContentSearch;
use Opencontent\Opendata\Api\ClassRepository;
use Opencontent\Opendata\Api\Exception\BaseException;

class BookingApiController extends ezpRestMvcController
{

    /**
     * @var ContentRepository;
     */
    protected $contentRepository;

    /**
     * @var ContentSearch
     */
    protected $contentSearch;

    /**
     * @var ClassRepository
     */
    protected $classRepository;

    /**
     * @var ezpRestRequest
     */
    protected $request;

    /**
     * @var \Opencontent\Opendata\Api\EnvironmentSettings
     */
    protected $currentEnvironment;

    protected $currentUserId;

    public function __construct($action, ezcMvcRequest $request)
    {
        parent::__construct($action, $request);
        $this->contentRepository = new ContentRepository();
        $this->contentSearch = new ContentSearch();
        $this->classRepository = new ClassRepository();
        $this->currentEnvironment = EnvironmentLoader::loadPreset('booking');
        $this->currentEnvironment->__set('requestBaseUri', $this->getBaseUri());
        $this->currentEnvironment->__set('request', $this->request);
        $this->contentRepository->setEnvironment($this->currentEnvironment);
        $this->contentSearch->setEnvironment($this->currentEnvironment);
    }

    /**
     * Intercetta il custom header X-Booking-User e impersona l'utente se l'utente corrente è un api super user
     *
     * @throws Exception
     */
    private function setCurrentUser()
    {
        if (isset($this->request->raw['HTTP_X_BOOKING_USER'])){
            $user = eZUser::currentUser();
            $hasAccess = $user->hasAccessTo('social_user', 'api_super_user');
            if ($hasAccess['accessWord'] != 'yes') {
                throw new Exception('Current user is not an api super user');
            }

            $userId = (int)$this->request->raw['HTTP_X_BOOKING_USER'];
            $user = eZUser::fetch($userId);
            if (!$user instanceof eZUser){
                throw new Exception("Booking-User $userId not found");
            }

            if ( $userId != eZUser::currentUserID() ) {
                eZUser::setCurrentlyLoggedInUser( $user, $userId );
            }
        }
        $this->currentUserId = eZUser::currentUserID();
    }

    public function doListLocations()
    {
        try {
            $this->setCurrentUser();

            $service = $this->getHandler()->serviceClass();
            $classes = implode(',', $service->bookableClassIdentifiers());
            $result = new ezpRestMvcResult();
            $locationSubtree = OpenPABooking::locationsNodeId();
            $stuffSubtree = OpenPABooking::stuffNodeId();

            $search = $this->contentSearch->search("classes [$classes] subtree [{$locationSubtree},{$stuffSubtree}]");
            $result->variables = (array)$search->searchHits;
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doGetLocation()
    {
        try {
            $this->setCurrentUser();

            $id = $this->request->variables['Id'];
            $result = new ezpRestMvcResult();
            $service = $this->getHandler()->serviceClass();
            $classes = $service->bookableClassIdentifiers();
            $content = $this->contentRepository->read($id);
            if (!in_array($content['metadata']['classIdentifier'], $classes)) {
                throw new NotFoundException($id, 'Location');
            }
            $result->variables = $content;
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doAddLocation()
    {
        try {
            $this->setCurrentUser();

            if (SocialUser::current()->hasBlockMode()) {
                throw new NotAllowedException('location', 'add');
            }

            $service = $this->getHandler()->serviceClass();
            $classes = $service->bookableClassIdentifiers();

            $payload = $this->getPayload();
            if (!isset($payload['metadata']['classIdentifier'])){
                throw new InvalidArgumentException("Missing parameter classIdentifier");
            }
            if (!in_array($payload['metadata']['classIdentifier'], $classes)){
                throw new InvalidArgumentException("Content '" . $payload['metadata']['classIdentifier'] . "' not allowed");
            }

            if ($payload['metadata']['classIdentifier'] == 'sala_pubblica') {
                $payload['metadata']['parentNodes'] = array(OpenPABooking::locationsNodeId());
            }elseif ($payload['metadata']['classIdentifier'] == 'attrezzatura_sala') {
                $payload['metadata']['parentNodes'] = array(OpenPABooking::stuffNodeId());
            }

            $result = new ezpRestMvcResult();
            $result->variables['result'] = (array)$this->contentRepository->createUpdate($payload);
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doGetLocationsAvailability()
    {
        try {
            $this->setCurrentUser();

            $day = $this->request->variables['Day'];
            $from = $this->request->variables['From'];
            $to = $this->request->variables['To'];

            $finder = new OpenPABookingSalaPubblicaAvailabilityFinder();

            $dayDate = DateTime::createFromFormat('Y-n-j', $day, OpenPABookingSalaPubblicaAvailabilityRequest::getTimeZone());
            if (!$dayDate instanceof DateTime) {
                throw new Exception("Date $day not valid");
            }

            list( $hour, $minute ) = explode(':', $from);
            $from = clone $dayDate;
            $from->setTime($hour, $minute);

            list( $hour, $minute ) = explode(':', $to);
            $to = clone $dayDate;
            $to->setTime($hour, $minute);

            $request = new OpenPABookingSalaPubblicaAvailabilityRequest();
            $request->setFrom($from);
            $request->setTo($to);
            if (isset($this->request->get['LocationId'])){
                $request->setLocation((int)$this->request->get['LocationId']);
            }
            if (isset($this->request->get['Destination'])){
                $request->setDestination($this->request->get['Destination']);
            }
            if (isset($this->request->get['NumberOfPlaces'])){
                $request->setPlaces($this->request->get['NumberOfPlaces']);
            }

            $result = new ezpRestMvcResult();
            $result->variables['result'] = $finder->request($request);
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doGetLocationAvailability()
    {
        try {
            $this->setCurrentUser();

            $day = $this->request->variables['Day'];
            $from = $this->request->variables['From'];
            $to = $this->request->variables['To'];
            $location = $this->request->variables['Id'];

            $finder = new OpenPABookingSalaPubblicaAvailabilityFinder();

            $dayDate = DateTime::createFromFormat('Y-n-j', $day, OpenPABookingSalaPubblicaAvailabilityRequest::getTimeZone());
            if (!$dayDate instanceof DateTime) {
                throw new Exception("Date $day not valid");
            }

            list( $hour, $minute ) = explode(':', $from);
            $from = clone $dayDate;
            $from->setTime($hour, $minute);

            list( $hour, $minute ) = explode(':', $to);
            $to = clone $dayDate;
            $to->setTime($hour, $minute);

            $request = new OpenPABookingSalaPubblicaAvailabilityRequest();
            $request->setFrom($from);
            $request->setTo($to);
            $request->setLocation($location);

            $result = new ezpRestMvcResult();
            $result->variables['result'] = $finder->request($request);
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doGetLocationUnavailability()
    {
        try {
            $this->setCurrentUser();

            $from = $this->request->variables['From'];
            $to = $this->request->variables['To'];
            $location = $this->request->variables['Id'];

            $locationObject = eZContentObject::fetch($location);
            if (!$locationObject instanceof eZContentObject) {
                throw new Exception("Location $location not found");
            }

            $fromDateTime = DateTime::createFromFormat('Y-n-j', $from);
            if (!$fromDateTime instanceof DateTime) {
                throw new Exception("Date $from not valid");
            }

            $toDateTime = DateTime::createFromFormat('Y-n-j', $to);
            if (!$toDateTime instanceof DateTime) {
                throw new Exception("Date $to not valid");
            }

            $urlTransformer = function (eZContentObject $object) {
                return '/api/booking/v1/sala_pubblica/booking/' . $object->attribute('id');
            };

            $calendarFinder = new OpenPABookingSalaPubblicaCalendar(
                $from,
                $to,
                $locationObject,
                null,
                array(),
                $urlTransformer,
                isset( $this->request->get['showAvailableSlots'] ) && $this->request->get['showAvailableSlots'] == 'true'
            );
            $result = new ezpRestMvcResult();
            $result->variables['result'] = $calendarFinder->getData();
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doAddBooking()
    {
        try {
            $this->setCurrentUser();

            if (SocialUser::current()->hasBlockMode()) {
                throw new NotAllowedException('booking', 'add');
            }

            $request = BookingApiBookingRequest::fromHash($this->getPayload());
            $request->validate();

            $result = new ezpRestMvcResult();
            //$result->variables['result'] = (array)$request->jsonSerialize();
            $result->variables['result'] = (array)$this->contentRepository->create($request->getPayload());

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doListBooking()
    {
        try {
            $this->setCurrentUser();

            $service = $this->getHandler()->serviceClass();
            $classes = $service->prenotazioneClassIdentifier();
            $result = new ezpRestMvcResult();
            $query = "classes [$classes] and subrequest = 0 and raw[extra_booking_users_lk] in [{$this->currentUserId}] sort [published=>desc]";
            if (isset( $this->request->get['status'] )) {
                $query .= " and state in [{$this->request->get['status']}]";
            }
            $search = $this->contentSearch->search($query);
            $result->variables = (array)$search;
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doGetBooking()
    {
        try {
            $this->setCurrentUser();

            $result = new ezpRestMvcResult();
            $result->variables = $this->getBooking($this->request->variables['Id']);
        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doAddComment()
    {
        try {
            $this->setCurrentUser();

            $id = (int)$this->request->variables['Id'];
            $this->getBooking($id);

            $payload = $this->getPayload();
            $message = isset( $payload['message'] ) ? $payload['message'] : null;

            if (empty( $message )) {
                throw new Exception("Empty message");
            }

            $this->getHandler()->addComment(
                $this->getOpenpaServiceControlBooking($id)->getCollaborationItem(),
                $message
            );
            $result = new ezpRestMvcResult();

            $result->variables = array(
                'message' => 'success',
                'method' => 'create',
                'content' => $message
            );

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doListComment()
    {
        try {
            $this->setCurrentUser();

            $id = (int)$this->request->variables['Id'];
            $this->getBooking($id);

            $offset = isset( $this->request->get['offset'] ) ? (int)$this->request->get['offset'] : 0;
            $limit = isset( $this->request->get['limit'] ) ? (int)$this->request->get['limit'] : 10;
            $itemParameters = array(
                'item_id' => $this->getOpenpaServiceControlBooking($id)->getCollaborationItem()->attribute('id'),
                'offset' => $offset,
                'limit' => $limit
            );
            /** @var eZCollaborationItemMessageLink[] $children */
            $children = eZCollaborationItemMessageLink::fetchItemList($itemParameters);

            $messages = array();
            foreach ($children as $item) {
                $authorObject = eZContentObject::fetch($item->participant()->attribute('participant_id'));

                $messages[] = array(
                    'author' => $authorObject ? $authorObject->attribute('name') : '?',
                    'created' => date('c', $item->attribute('created')),
                    'message' => $item->simpleMessage()->attribute('data_text1')
                );
            }

            $result = new ezpRestMvcResult();
            $result->variables = array(
                'result' => $messages
            );

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doMarkAvailable()
    {
        try {
            $this->setCurrentUser();

            $id = (int)$this->request->variables['Id'];
            $this->getBooking($id);

            $payload = $this->getPayload();
            $price = isset( $payload['price'] ) ? $payload['price'] : null;

            $this->getHandler()->defer(
                $this->getOpenpaServiceControlBooking($id)->getCollaborationItem(),
                array('manual_price' => $price)
            );

            $result = new ezpRestMvcResult();
            $result->variables = array(
                'message' => 'success',
                'method' => 'update'
            );

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doMarkApprove()
    {
        try {
            $this->setCurrentUser();

            $id = (int)$this->request->variables['Id'];
            $this->getBooking($id);

            $this->getHandler()->approve(
                $this->getOpenpaServiceControlBooking($id)->getCollaborationItem()
            );

            $result = new ezpRestMvcResult();
            $result->variables = array(
                'message' => 'success',
                'method' => 'update'
            );

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doMarkDeny()
    {
        try {
            $this->setCurrentUser();

            $id = (int)$this->request->variables['Id'];
            $this->getBooking($id);

            $this->getHandler()->deny(
                $this->getOpenpaServiceControlBooking($id)->getCollaborationItem()
            );

            $result = new ezpRestMvcResult();
            $result->variables = array(
                'message' => 'success',
                'method' => 'update'
            );

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doMarkExpire()
    {
        try {
            $this->setCurrentUser();

            $id = (int)$this->request->variables['Id'];
            $this->getBooking($id);

            $this->getHandler()->expire(
                $this->getOpenpaServiceControlBooking($id)->getCollaborationItem()
            );

            $result = new ezpRestMvcResult();
            $result->variables = array(
                'message' => 'success',
                'method' => 'update'
            );

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doMarkSuccess()
    {
        try {
            $this->setCurrentUser();

            $id = (int)$this->request->variables['Id'];
            $this->getBooking($id);

            $this->getHandler()->returnOK(
                $this->getOpenpaServiceControlBooking($id)->getCollaborationItem()
            );

            $result = new ezpRestMvcResult();
            $result->variables = array(
                'message' => 'success',
                'method' => 'update'
            );

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    public function doMarkFail()
    {
        try {
            $this->setCurrentUser();

            $id = (int)$this->request->variables['Id'];
            $this->getBooking($id);

            $this->getHandler()->returnKO(
                $this->getOpenpaServiceControlBooking($id)->getCollaborationItem()
            );

            $result = new ezpRestMvcResult();
            $result->variables = array(
                'message' => 'success',
                'method' => 'update'
            );

        } catch (Exception $e) {
            $result = $this->doExceptionResult($e);
        }

        return $result;
    }

    /**
     * @return BookingHandlerSalaPubblica
     * @throws BaseException
     * @throws Exception
     */
    protected function getHandler()
    {
        $handlerIdentifier = $this->request->variables['Handler'];
        $handler = OpenPABookingHandler::handler($handlerIdentifier, null);
        if ($handler instanceof BookingHandlerSalaPubblica) {
            return $handler;
        }

        throw new BaseException("Booking handler" . $this->request->variables['Handler'] . " non found");
    }

    /**
     * @param $contentId
     *
     * @return ObjectHandlerServiceControlBooking
     * @throws Exception
     */
    protected function getOpenpaServiceControlBooking($contentId)
    {
        $object = eZContentObject::fetch($contentId);
        if ($object instanceof eZContentObject) {
            $openpaObject = OpenPAObjectHandler::instanceFromObject($object);

            /** @var ObjectHandlerServiceControlBooking $openpaService */
            return $openpaObject->attribute('control_booking_sala_pubblica');
        }

        throw new Exception("Content $contentId not found");
    }

    protected function doExceptionResult(Exception $exception)
    {
        $result = new ezcMvcResult;
        $result->variables['message'] = $exception->getMessage();

        $serverErrorCode = ezpHttpResponseCodes::SERVER_ERROR;
        $errorType = BaseException::cleanErrorCode(get_class($exception));
        if ($exception instanceof BaseException) {
            $serverErrorCode = $exception->getServerErrorCode();
            $errorType = $exception->getErrorType();
        }

        $result->status = new OcOpenDataErrorResponse(
            $serverErrorCode,
            $exception->getMessage(),
            $errorType
        );

        return $result;
    }

    protected function getBaseUri()
    {
        $hostUri = $this->request->getHostURI();
        $apiName = ezpRestPrefixFilterInterface::getApiProviderName();
        $apiPrefix = eZINI::instance('rest.ini')->variable('System', 'ApiPrefix');
        $uri = $hostUri . $apiPrefix . '/' . $apiName . '/v1/';
        if ($this->currentEnvironment instanceof \Opencontent\Opendata\Api\EnvironmentSettings) {
            $uri .= $this->currentEnvironment->__get('identifier') . '/';
        }

        return $uri;
    }

    protected function getPayload()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        return $data;
    }

    private function getBooking($id)
    {
        $service = $this->getHandler()->serviceClass();
        $content = $this->contentRepository->read($id);
        if ($content['metadata']['classIdentifier'] != $service->prenotazioneClassIdentifier()) {
            throw new NotFoundException($id, 'Booking');
        }

        return $content;
    }
}
