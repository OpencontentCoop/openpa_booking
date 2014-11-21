<?php

interface OpenPABookingHandlerInterface
{
    public function __construct( array $Params = null );

    public static function name();

    public static function identifier();

    public function add();

    public function view();

    public function workflow( $parameters, $process, $event );

    public function defer( eZCollaborationItem $item );

    public function approve( eZCollaborationItem $item );

    public function deny( eZCollaborationItem $item );
    
    public function redirect( eZModule $module );

}