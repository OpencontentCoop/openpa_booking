<?php

class BookingHandlerSalaPubblica implements OpenPABookingHandlerInterface
{
    /**
     * @var eZTemplate
     */
    protected $tpl;

    /**
     * @var eZModule
     */
    protected $module;

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @param eZContentObject
     */
    protected $currentObject;

    /**
     * @var bool
     */
    protected $hasRedirect;

    /**
     *
     * In base al primo parametero che deve essere un ObjectId esegue una funzione
     *
     * @param array $Params
     * @throws Exception
     */
    public function __construct( array $Params = null )
    {
        $this->tpl = $tpl = eZTemplate::factory();
        if ( is_array( $Params ) )
        {
            $this->parameters = isset( $Params['Parameters'] ) ? $Params['Parameters'] : array();
            $this->currentObject = isset( $this->parameters[1] ) ? eZContentObject::fetch( $this->parameters[1] ) : false;

            $this->module = isset( $Params['Module'] ) ? $Params['Module'] : false;

            if ( $this->currentObject instanceof eZContentObject )
            {
                if ( !$this->currentObject->attribute( 'can_read' ) )
                {
                    throw new Exception( "User can not read object {$this->currentObject->attribute( 'id' )}" );
                }
            }
        }
    }

    public function add()
    {
        if ( $this->currentObject instanceof eZContentObject )
        {
            $http = eZHTTPTool::instance();

            $start = $http->getVariable( 'start', false );
            $end = $http->getVariable( 'end', false );

            try
            {
                ObjectHandlerServiceControlBookingSalaPubblica::isValidDate( $start, $end, $this->currentObject );
            }
            catch ( Exception $e )
            {
                $this->module->redirectTo( 'content/view/full/' . $this->currentObject->attribute( 'main_node_id' ) . '/(error)/' . urlencode( $e->getMessage() ) . '#error' );
                return null;
            }

            $object = ObjectHandlerServiceControlBookingSalaPubblica::createObject( $this->currentObject, $start, $end );

            if ( $object instanceof eZContentObject )
            {
                if ( !$this->module instanceof eZModule )
                {
                    throw new Exception( "eZModule non trovato" );
                }
                $this->module->redirectTo( 'content/edit/' . $object->attribute( 'id' ) . '/' . $object->attribute( 'current_version' )  );
                return null;
            }
            else
            {
                throw new Exception( "Non è possibile creare l'oggetto prenotazione" );
            }
        }
        else
        {
            $Result = array();
            $Result['content'] = $this->tpl->fetch( 'design:booking/sala_pubblica/list.tpl' );
            $Result['path'] = array( array( 'text' => 'Prenotazione' , 'url' => false ) );
            return $Result;
        }
    }

    public function view()
    {
        $openpaObject = OpenPAObjectHandler::instanceFromContentObject( $this->currentObject );
        if ( $openpaObject->hasAttribute( 'control_booking_sala_pubblica' )
             && $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'is_valid' )
             && $this->currentObject->attribute( 'can_read' ) )
        {
            $waitCheckout = $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'current_state_code' ) == ObjectHandlerServiceControlBookingSalaPubblica::STATUS_WAITING_FOR_CHECKOUT;

            if ( $waitCheckout && $this->currentObject->attribute( 'owner_id' ) == eZUser::currentUserID() )
            {
                $this->updateBasket( $this->currentObject );
            }
            else
            {
                $collaborationItem = $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'collaboration_item' );
                if ( $collaborationItem instanceof eZCollaborationItem )
                {
                    $this->module->redirectTo( 'collaboration/item/full/' . $collaborationItem->attribute( 'id' )  );
                    return null;
                }
                else
                {
                    throw new Exception( "eZCollaborationItem not found for object {$this->currentObject->attribute( 'id' )}" );
                }
            }
        }
        else
        {
            throw new Exception( "Prenotazione non accessibile" );
        }
    }

    public function workflow( $parameters, $process, $event )
    {
        $trigger = $parameters['trigger_name'];
        eZDebug::writeNotice( "Run workflow", __METHOD__ );
        if ( $trigger == 'post_publish' )
        {
            $this->currentObject = eZContentObject::fetch( $parameters['object_id'] );
            $openpaObject = OpenPAObjectHandler::instanceFromContentObject( $this->currentObject );
            if ( $openpaObject->hasAttribute( 'control_booking_sala_pubblica' ) && $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'is_valid' ) )
            {
                if ( $this->currentObject->attribute( 'current_version' ) == 1 )
                {
                    $id = $openpaObject->getContentObject()->attribute( 'id' );
                    $authorId = $openpaObject->getContentObject()->attribute( 'owner_id' );
                    $approveIdArray = $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'reservation_manager_ids' );
                    OpenPABookingCollaborationHandler::createApproval( $id, __CLASS__, $authorId, $approveIdArray );
                    eZDebug::writeNotice( "Create collaboration item", __METHOD__ );
                }
            }
        }
        if ( $trigger == 'post_checkout' )
        {
            /** @var eZOrder $order */
            $order = eZOrder::fetch( $parameters['order_id'] );
            if ( $order instanceof eZOrder )
            {
                $products = $order->attribute( 'product_items' );
                foreach( $products as $product )
                {
                    /** @var eZContentObject $prenotazione */
                    $prenotazione = $product['item_object']->attribute( 'contentobject' );
                    $openpaObject = OpenPAObjectHandler::instanceFromContentObject( $prenotazione );
                    /** @var ObjectHandlerServiceControlBookingSalaPubblica $service */
                    $service = $openpaObject->service( 'control_booking_sala_pubblica' );
                    if ( $openpaObject->hasAttribute( 'control_booking_sala_pubblica' )
                        && $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'is_valid' )
                        && $prenotazione->attribute( 'can_read' ) )
                    {
                        OpenPABase::sudo( function() use( $service, $order ){
                            $service->addOrder( $order );
                        });
                    }
                }
            }
        }
    }

    protected function updateBasket( eZContentObject $prenotazione )
    {
        $openpaObject = OpenPAObjectHandler::instanceFromContentObject( $prenotazione );
        if ( $openpaObject->hasAttribute( 'control_booking_sala_pubblica' )
            && $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'is_valid' )
            && $prenotazione->attribute( 'can_read' ) )
        {
            $quantity = $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'timeslot_count' );
            if ( !$this->module instanceof eZModule )
            {
                throw new Exception( "eZModule non trovato" );
            }
            $this->module->redirectTo( "/shop/add/" . $prenotazione->attribute( 'id' ) . "/" . $quantity );
            return null;
        }
        else
        {
            throw new Exception( "Prenotazione non accessibile" );
        }
    }

    public function approve( eZCollaborationItem $item )
    {
        $prenotazione = OpenPABookingCollaborationHandler::contentObject( $item );
        $openpaObject = OpenPAObjectHandler::instanceFromContentObject( $prenotazione );
        /** @var ObjectHandlerServiceControlBookingSalaPubblica $service */
        $service = $openpaObject->service( 'control_booking_sala_pubblica' );
        if ( $openpaObject->hasAttribute( 'control_booking_sala_pubblica' )
            && $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'is_valid' )
            && $prenotazione->attribute( 'can_read' ) )
        {
            $service->changeState( ObjectHandlerServiceControlBookingSalaPubblica::STATUS_APPROVED );
        }
    }

    public function defer( eZCollaborationItem $item )
    {
        $prenotazione = OpenPABookingCollaborationHandler::contentObject( $item );
        $openpaObject = OpenPAObjectHandler::instanceFromContentObject( $prenotazione );
        /** @var ObjectHandlerServiceControlBookingSalaPubblica $service */
        $service = $openpaObject->service( 'control_booking_sala_pubblica' );
        if ( $openpaObject->hasAttribute( 'control_booking_sala_pubblica' )
            && $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'is_valid' )
            && $prenotazione->attribute( 'can_read' ) )
        {
            $sala = $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'sala' );
            if ( $sala instanceof eZContentObject )
            {
                $productType = eZShopFunctions::productTypeByObject( $sala );
                if ( $productType )
                {
                    $service->changeState( ObjectHandlerServiceControlBookingSalaPubblica::STATUS_WAITING_FOR_CHECKOUT );
                }
                else
                {
                    $service->changeState( ObjectHandlerServiceControlBookingSalaPubblica::STATUS_APPROVED );
                    OpenPABookingCollaborationHandler::changeApprovalStatus( $item, OpenPABookingCollaborationHandler::STATUS_ACCEPTED );
                }
            }
        }
    }

    public function deny( eZCollaborationItem $item )
    {
        $prenotazione = OpenPABookingCollaborationHandler::contentObject( $item );
        $openpaObject = OpenPAObjectHandler::instanceFromContentObject( $prenotazione );
        /** @var ObjectHandlerServiceControlBookingSalaPubblica $service */
        $service = $openpaObject->service( 'control_booking_sala_pubblica' );
        if ( $openpaObject->hasAttribute( 'control_booking_sala_pubblica' )
            && $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'is_valid' )
            && $prenotazione->attribute( 'can_read' ) )
        {
            $service->changeState( ObjectHandlerServiceControlBookingSalaPubblica::STATUS_DENIED );
        }
    }

}