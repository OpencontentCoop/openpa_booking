<?php

/** @var eZModule $module */
$module = $Params['Module'];

$currentUser = eZUser::currentUser();

$tpl = eZTemplate::factory();
$tpl->setVariable('current_user', $currentUser);
$tpl->setVariable('persistent_variable', array());
$tpl->setVariable('booking_home', true);

$Result = array();
$Result['persistent_variable'] = $tpl->variable('persistent_variable');
$Result['content'] = $tpl->fetch('design:booking/home.tpl');
$Result['node_id'] = 0;

$contentInfoArray = array('url_alias' => 'booking/home');
$contentInfoArray['persistent_variable'] = array('booking_home' => true);
if ($tpl->variable('persistent_variable') !== false) {
    $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
    $contentInfoArray['persistent_variable']['booking_home'] = true;
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();
