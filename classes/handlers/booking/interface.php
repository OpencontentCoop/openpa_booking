<?php

interface OpenPABookingHandlerInterface
{
    public function __construct( array $Params = null );

    public function add();

    public function view();

    public function workflow( $parameters, $process, $event );

    public function approve( eZCollaborationItem $item );

    public function deny( eZCollaborationItem $item );

}