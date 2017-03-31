<?php

$Module = $Params['Module'];
$identifier = $Params['Page'];


$tpl = eZTemplate::factory();
if (OpenPABooking::instance()->rootHasAttribute($identifier)) {
    $currentUser = eZUser::currentUser();

    $tpl->setVariable('current_user', $currentUser);
    $tpl->setVariable('persistent_variable', array());
    $tpl->setVariable('identifier', $identifier);
    $tpl->setVariable('page', OpenPABooking::instance()->getAttribute($identifier));

    $Result = array();

    $Result['persistent_variable'] = $tpl->variable('persistent_variable');
    $Result['content'] = $tpl->fetch('design:booking/info.tpl');
    $Result['node_id'] = 0;

    $contentInfoArray = array('url_alias' => 'booking/info');
    $contentInfoArray['persistent_variable'] = false;
    if ($tpl->variable('persistent_variable') !== false) {
        $contentInfoArray['persistent_variable'] = $tpl->variable(
            'persistent_variable'
        );
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();

} else {
    $Module->handleError(
        eZError::KERNEL_NOT_AVAILABLE,
        'kernel'
    );
}
