<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Fix responsabili" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );
try
{
    /** @var eZUser $user */
    $user = eZUser::fetchByName( 'admin' );
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

    $responsabili = array();

    /** @var eZContentObjectTreeNode[] $locations */
    $locations = array_merge(
        eZContentObjectTreeNode::subTreeByNodeID(array(), OpenPABooking::stuffNodeId()),
        eZContentObjectTreeNode::subTreeByNodeID(array(), OpenPABooking::locationsNodeId())
    );
    foreach($locations as $location){
        $dataMap = $location->dataMap();
        if (isset($dataMap['reservation_manager']) && $dataMap['reservation_manager']->hasContent()){
            $responsabili = array_merge(
                $responsabili,
                explode('-', $dataMap['reservation_manager']->toString())
            );
        }
    }

    $responsabili = array_unique($responsabili);

    foreach($responsabili as $responsabile){
        $object = eZContentObject::fetch($responsabile);
        if ($object instanceof eZContentObject){
            OpenPALog::notice($object->attribute('name'));
            eZContentOperationCollection::addAssignment( $object->mainNodeID(), $object->ID, array(OpenPABooking::moderatorGroupNodeId()) );
        }
    }


    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
