<?php
require 'autoload.php';

$script = eZScript::instance(array(
    'description' => ( "OpenPA Booking Sala Pubblica Installer \n\n" ),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true
));

$script->startup();

$options = $script->getOptions(
    '[class:][file:]',
    '',
    array(
        'class'  => 'Identificatore di classe',
        'file'  => 'Nome del file'
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();

OpenPALog::setOutputLevel(OpenPALog::ALL);

try {
    $user = eZUser::fetchByName('admin');
    eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

    $parameters = OCClassExtraParameters::fetchObjectList(OCClassExtraParameters::definition(), null,
        array('class_identifier' => $options['class']));

    $data = array();
    foreach($parameters as $parameter){
        $keys = $parameter->attributes();
        $item = array();
        foreach($keys as $key){
            $item[$key] = $parameter->attribute($key);
        }
        $data[] = $item;
    }

    if (empty($data)){
        throw new Exception("Nessun dato trovato");
    }

    $data = json_encode($data);
    if (!eZFile::create($options['file'], eZSys::rootDir(), $data)){
        throw new Exception("File non creato");
    }



    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
