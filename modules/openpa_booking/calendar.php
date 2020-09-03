<?php

/** @var eZModule $module */
$module = $Params['Module'];
$identifier = $Params['HandlerIdentifier'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$service = new ObjectHandlerServiceControlBookingSalaPubblica();

$locations = OpenPABooking::instance()->rootNode()->subTree([
    'ClassFilterType' => 'include',
    'ClassFilterArray' => $service->bookableClassIdentifiers(),
    'SortBy' => [['class_name', true], ['name', true]]
]);

$tpl->setVariable('locations', $locations);
$tpl->setVariable('persistent_variable', array());

$Result = array();
$Result['persistent_variable'] = $tpl->variable('persistent_variable');
$Result['content'] = $tpl->fetch('design:booking/calendar.tpl');
$Result['node_id'] = 0;

$contentInfoArray = array('url_alias' => 'booking/calendar');
$contentInfoArray['persistent_variable'] = [];
if ($tpl->variable('persistent_variable') !== false) {
    $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();