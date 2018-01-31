<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Fix booking assignments" ),
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

    $serviceClass = new ObjectHandlerServiceControlBookingSalaPubblica();
    $class = $serviceClass->prenotazioneClassIdentifier();

    /** @var eZContentObjectTreeNode[] $items */
    $items = eZContentObjectTreeNode::subTreeByNodeID(array(
        'ClassFilterType' => 'include',
        'ClassFilterArray' => array($class),
    ), 1);

    foreach ($items as $item) {
        /** @var eZContentObjectTreeNode $parent */
        $parent = $item->attribute('parent');
        $locationParent = $serviceClass->getBookingLocationNode($parent->object());
        if ($parent->attribute('node_id') != $locationParent->attribute('node_id')){
            eZContentObjectTreeNodeOperations::move($item->attribute('node_id'), $locationParent->attribute('node_id'));
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
