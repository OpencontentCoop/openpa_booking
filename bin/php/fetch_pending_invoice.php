<?php
require 'autoload.php';

$script = eZScript::instance(array('description' => ("Fetch pending invoices"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();

try {
    $limit = 50;
    /** @var eZPendingActions[] $entries */
    $entries = eZPendingActions::fetchByAction(ObjectHandlerServiceControlBookingSalaPubblica::PENDING_ACTION_REFETCH_INVOICE);

    if (!empty($entries)) {
        foreach ($entries as $entry) {
            $orderId = $entry->attribute('param');
            $cli->output("order #{$orderId}: ", false);
            $result = ObjectHandlerServiceControlBookingSalaPubblica::fetchInvoiceData($orderId);
            if (is_array($result) && isset($result['_status'])){
                $cli->output($result['_status']);
            }else{
                $cli->output('unknown');
            }
        }
    }

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
