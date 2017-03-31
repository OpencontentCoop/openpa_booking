<?php

interface OpenPABookingHandlerInterface
{
    function __construct(array $Params = null);

    static function name();

    static function identifier();

    function add();

    function view();

    function workflow($parameters, $process, $event);

    function defer(eZCollaborationItem $item, $parameters = array());

    function approve(eZCollaborationItem $item, $parameters = array());

    function deny(eZCollaborationItem $item, $parameters = array());

    function addComment(eZCollaborationItem $item, $messageText);

    function redirectToItem(eZModule $module, eZCollaborationItem $item, $parameters = array());

    function redirectToSummary(eZModule $module, eZCollaborationItem $item, $parameters = array());

    function handleCustomAction(eZModule $module, eZCollaborationItem $item);

}
