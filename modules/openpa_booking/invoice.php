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
        $service->downloadInvoice();
        eZDisplayDebug();
        eZExecution::cleanExit();
    }
}



