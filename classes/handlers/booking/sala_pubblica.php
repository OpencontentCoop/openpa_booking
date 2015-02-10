<?php

class BookingHandlerSalaPubblica extends BookingHandlerBase implements OpenPABookingHandlerInterface
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

    public static function name()
    {
        return 'Prenotazioni sale pubbliche';
    }

    public static function identifier()
    {
        return 'sala_pubblica';
    }

    public function serviceClass()
    {
        return new ObjectHandlerServiceControlBookingSalaPubblica();
    }

    public function view()
    {
        if (  $this->currentObject instanceof eZContentObject )
        {
            $openpaObject = OpenPAObjectHandler::instanceFromContentObject( $this->currentObject );
            if ( $openpaObject->hasAttribute( 'control_booking_sala_pubblica' )
                 && $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'is_valid' )
                 && $this->currentObject->attribute( 'can_read' ) )
            {
                $waitCheckout = $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'current_state_code' ) == ObjectHandlerServiceControlBookingSalaPubblica::STATUS_WAITING_FOR_CHECKOUT;

                if ( $waitCheckout && $this->currentObject->attribute( 'owner_id' ) == eZUser::currentUserID() )
                {                                                            
                    if ( $openpaObject->hasAttribute( 'order_id' ) && $openpaObject->attribute( 'order_id' )->attribute( 'has_content' ) )
                    {                        
                        $this->module->redirectTo( "/shop/cutomerorderview/" . eZUser::currentUserID() );
                        return null;
                    }
                    else
                    {                                                
                        $this->updateBasket( $this->currentObject );   
                    }                    
                }
                else
                {                    
                    $collaborationItem = $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'collaboration_item' );
                    if ( $collaborationItem instanceof eZCollaborationItem )
                    {
                        $module = eZModule::exists( "collaboration" );
                        return $module->run( 'item', array( 'full', $collaborationItem->attribute( 'id' ) ) );
                        
                    }
                    else
                    {
                        throw new Exception( "eZCollaborationItem not found for object {$this->currentObject->attribute( 'id' )}" );
                    }
                }
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

    public function workflow( $parameters, $process, $event )
    {
        $trigger = $parameters['trigger_name'];
        eZDebug::writeNotice( "Run workflow", __METHOD__ );
        if ( $trigger == 'post_publish' )
        {
            $currentObject = eZContentObject::fetch( $parameters['object_id'] );
            $openpaObject = OpenPAObjectHandler::instanceFromContentObject( $currentObject );
            if ( $openpaObject->hasAttribute( 'control_booking_sala_pubblica' ) && $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'is_valid' ) )
            {
                if ( $currentObject->attribute( 'current_version' ) == 1 )
                {
                    $id = $openpaObject->getContentObject()->attribute( 'id' );
                    $authorId = $openpaObject->getContentObject()->attribute( 'owner_id' );
                    $approveIdArray = $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'reservation_manager_ids' );
                    
                    foreach( array_merge( array( $authorId ), $approveIdArray ) as $userId )
                    {
                        if ( !eZPersistentObject::fetchObject( eZCollaborationNotificationRule::definition(), null, array( 'user_id' => $userId, 'collab_identifier' => OpenPABookingCollaborationHandler::TYPE_STRING ), null, null, true ) )
                        {
                            $rule = eZCollaborationNotificationRule::create( OpenPABookingCollaborationHandler::TYPE_STRING, $userId );
                            $rule->store();
                            eZDebug::writeNotice( "Create notification rule for user $userId", __METHOD__ );
                        }
                    }
                    
                    OpenPABookingCollaborationHandler::createApproval( $id, self::identifier(), $authorId, $approveIdArray );
                    $openpaObject->attribute( 'control_booking_sala_pubblica' )->notify( 'create_approval' );
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
            $quantity = 1;
            if ( !$openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'has_manual_price' ) )
            {
                $quantity = $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'timeslot_count' );
            }
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

    public function approve( eZCollaborationItem $item, $parameters = array() )
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
            $service->setOrderStatus( eZOrderStatus::DELIVERED );
        }        
    }

    public function defer( eZCollaborationItem $item, $parameters = array() )
    {
        $prenotazione = OpenPABookingCollaborationHandler::contentObject( $item );
        $openpaObject = OpenPAObjectHandler::instanceFromContentObject( $prenotazione );
        /** @var ObjectHandlerServiceControlBookingSalaPubblica $service */
        $service = $openpaObject->service( 'control_booking_sala_pubblica' );
        if ( $openpaObject->hasAttribute( 'control_booking_sala_pubblica' )
            && $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'is_valid' )
            && $prenotazione->attribute( 'can_read' ) )
        {
            $do = true;
            if ( $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'has_manual_price' ) )
            {
                $do = false;
                if ( isset( $parameters['manual_price'] ) )
                {
                    $manualPrice = $parameters['manual_price'];
                    if ( empty( $manualPrice ) || !preg_match( "#^[0-9]+(.){0,1}[0-9]{0,2}$#", $manualPrice ) )
                    {
                        $error = "Prezzo  non valido";
                        throw new Exception( $error );
                    }
                    else
                    {
                        $do = $service->setPrice( $manualPrice );                        
                    }
                }                
            }
            if ( $do )
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
                $concurrentRequests = (array) $openpaObject->attribute( 'control_booking_sala_pubblica' )->attribute( 'concurrent_requests' );
    
                foreach( $concurrentRequests as $concurrentRequest )
                {
                    if ( $concurrentRequest instanceof eZContentObjectTreeNode )
                    {
                        $concurrentRequestOpenpa = OpenPAObjectHandler::instanceFromContentObject(
                            $concurrentRequest->attribute( 'object' )
                        );
                        if ( $concurrentRequestOpenpa->hasAttribute(  'control_booking_sala_pubblica' ) )
                        {
                            $concurrentRequestCollaborationItem = $concurrentRequestOpenpa->attribute(
                                'control_booking_sala_pubblica'
                            )->attribute( 'collaboration_item' );
                            if ( $concurrentRequestCollaborationItem instanceof eZCollaborationItem )
                            {
                                /** @var ObjectHandlerServiceControlBookingSalaPubblica $concurrentRequestOpenpaService */
                                $concurrentRequestOpenpaService = $concurrentRequestOpenpa->service(
                                    'control_booking_sala_pubblica'
                                );
                                $concurrentRequestOpenpaService->changeState(
                                    ObjectHandlerServiceControlBookingSalaPubblica::STATUS_DENIED
                                );
                                OpenPABookingCollaborationHandler::changeApprovalStatus(
                                    $concurrentRequestCollaborationItem,
                                    OpenPABookingCollaborationHandler::STATUS_DENIED
                                );
                            }
                        }
                    }
                }            
            }
        }        
    }

    public function deny( eZCollaborationItem $item, $parameters = array() )
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
            if ( !eZOrderStatus::fetchByStatus( 9999 ) )
            {
                $row = array(
                    'id' => null,
                    'is_active' => true,
                    'status_id' => 9999,
                    'name' => ezpI18n::tr( 'kernel/shop', 'Annullato' ) );
                $newCustom = new eZOrderStatus( $row );
                $newCustom->storeCustom();
            }
            $service->setOrderStatus( 9999 );
        }        
    }

}