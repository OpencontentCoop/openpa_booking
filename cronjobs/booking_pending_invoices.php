<?php

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
} catch (Exception $e) {
    $cli->error($e->getMessage());
}
