<?php

/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();

$orderId = $Params['OrderID'];
$order = eZOrder::fetch((int) $orderId);
if ($order instanceof eZOrder){
	$productCollectionID = $order->attribute('productcollection_id');
    $productCollection = eZProductCollection::fetch($productCollectionID);	        
    $service = ObjectHandlerServiceControlBookingSalaPubblica::instanceFromProductCollection($productCollection);
    if ($service instanceof ObjectHandlerServiceControlBookingSalaPubblica){
        try {
            if (!$service->downloadInvoice()){
                return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
            }
        }catch (Exception $e){
            return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
        }
    }
}



