<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(array('description' => ("Refresh booking approvers"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions("[dry-run]",
    "",
    array(
        'dry-run' => ""
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

OpenPALog::setOutputLevel(OpenPALog::ALL);
try {
    /** @var eZUser $user */
    $user = eZUser::fetchByName('admin');
    eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

    $serviceClass = new ObjectHandlerServiceControlBookingSalaPubblica();

    $bookingClass = $serviceClass->prenotazioneClassIdentifier();

    $class = eZContentClass::fetchByIdentifier($bookingClass);
    if ($class instanceof eZContentClass) {
        $objectListCount = $class->objectCount();

        $offset = 0;
        $limit = 100;

        while ($offset < $objectListCount) {
            $objectList = eZContentObject::fetchSameClassList($class->attribute('id'), true, $offset, $limit);
            $offset += count($objectList);

            foreach ($objectList as $object) {
                $result = OpenPABookingCollaborationParticipants::refresh($object, $options['dry-run']);
                if (!empty($result)){
                    $cli->output($result['info']);
                    if ($result['error']){
                        $cli->error(' -> ERROR');
                    }else {
                        if ($options['verbose']) {
                            foreach ($result['users'] as $item) {
                                $cli->output(' -> ' . $item);
                            }
                        }
                        foreach ($result['actions'] as $item) {
                            $cli->warning(' -> ' . $item);
                        }
                    }
                }
            }
            eZContentObject::clearCache();
        }
    }

    $cli->output();
    $memoryMax = memory_get_peak_usage(); // Result is in bytes
    $memoryMax = round( $memoryMax / 1024 / 1024, 2 ); // Convert in Megabytes
    $cli->output( 'Peak memory usage : '.$memoryMax.'M' );

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
