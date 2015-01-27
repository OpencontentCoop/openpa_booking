<?php

interface OpenPABookingHandlerInterface
{
    public function __construct( array $Params = null );

    public static function name();

    public static function identifier();

    public function add();

    public function view();

    public function workflow( $parameters, $process, $event );

    public function defer( eZCollaborationItem $item, $parameters = array() );

    public function approve( eZCollaborationItem $item, $parameters = array() );

    public function deny( eZCollaborationItem $item, $parameters = array() );
    
    public function redirectToItem( eZModule $module, eZCollaborationItem $item, $parameters = array() );
    
    public function redirectToSummary( eZModule $module, eZCollaborationItem $item, $parameters = array() );

}