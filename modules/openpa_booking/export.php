<?php

/** @var eZModule $Module */
$Module = $Params['Module'];
$Http = eZHTTPTool::instance();
$Query = $Params['Query'];

if (empty($Query) && !$Http->hasGetVariable('download')) {

    $tpl = eZTemplate::factory();
    $tpl->setVariable('persistent_variable', array());

    $Result = array();
    $Result['persistent_variable'] = $tpl->variable('persistent_variable');
    $Result['content'] = $tpl->fetch('design:booking/export.tpl');
    $Result['node_id'] = 0;

    $contentInfoArray = array('url_alias' => 'booking/config');
    $contentInfoArray['persistent_variable'] = false;
    if ($tpl->variable('persistent_variable') !== false) {
        $contentInfoArray['persistent_variable'] = $tpl->variable('persistent_variable');
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();

}else{

    try {
        $exporter = new OpenPABookingExporter(1, urldecode($Query));
        $exporter->setModule($Module);
        ob_get_clean();
        $exporter->handleDownload();

    } catch (InvalidArgumentException $e) {
        eZDebug::writeError($e->getMessage(), __FILE__);

        return $Module->handleError(eZError::KERNEL_NOT_FOUND, 'kernel');

    } catch (Exception $e) {
        eZDebug::writeError($e->getMessage(), __FILE__);

        return $Module->handleError(eZError::KERNEL_ACCESS_DENIED, 'kernel');
    }

    eZExecution::cleanExit();
}
