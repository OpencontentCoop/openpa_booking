<?php
require 'autoload.php';

$script = eZScript::instance(array(
    'description' => ( "OpenPA Import Class Extra Parameters from file \n\n" ),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true
));

$script->startup();

$options = $script->getOptions(
    '[file:]',
    '',
    array( 'file'  => 'Nome del file')
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();

OpenPALog::setOutputLevel(OpenPALog::ALL);

try {
    $user = eZUser::fetchByName('admin');
    eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

    $name = $options['file'] ? $options['file'] : 'sala_pubblica.json';
    $path = eZSys::rootDir() . '/extension/openpa_booking/data/' . $name;
    $fileContents = file_get_contents($path);

    if (!$fileContents){
        throw new Exception("File $path non trovato o vuoto");
    }
    $data = json_decode($fileContents, 1);

    foreach($data as $item){
        $parameter = new OCClassExtraParameters($item);
        $parameter->store();
    }

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
