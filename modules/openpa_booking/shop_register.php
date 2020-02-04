<?php

/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();

$tpl = eZTemplate::factory();

if ($module->isCurrentAction('Cancel')) {
    $module->redirectTo('/shop/basket/');

    return;
}

$settings = OpenPABookingUserShopAccountHandler::getAccountDataSettings();
$data = OpenPABookingUserShopAccountHandler::getAccountData();
foreach ($settings as $key => $value) {
    if (isset($data[$key]) && $data[$key] != 'null'){
        $settings[$key]['value'] = $data[$key];
    }
}

$tpl->setVariable("input_error", false);

if ($module->isCurrentAction('Store')) {

    $data = OpenPABookingUserShopAccountHandler::fetchInput();
    foreach ($settings as $key => $value) {
        if (isset($data[$key])){
            $settings[$key]['value'] = $data[$key];
        }
    }

    try {
        OpenPABookingUserShopAccountHandler::validateData($data);
        OpenPABookingUserShopAccountHandler::createOrder($data);

        $module->redirectTo('/shop/confirmorder/');
        return;

    } catch (Exception $e) {        
        
        foreach ($settings as $key => $value) {
            if ($value['value'] == 'null'){                
                $settings[$key]['value'] = '';
            }
        }
        $tpl->setVariable("input_error", true);
    }
}

$tpl->setVariable('settings', $settings);
$type = 'persona_fisica';
if (!empty($settings['type']['value'])){
    $type = $settings['type']['value'];
}
$tpl->setVariable('current_type', $type);
foreach ($data as $name => $value) {
    $tpl->setVariable($name, $value);
}

$Result = array();
$Result['content'] = $tpl->fetch("design:shop/shop_register.tpl");
$Result['path'] = array(
    array(
        'url' => false,
        'text' => ezpI18n::tr('kernel/shop', 'Enter account information')
    )
);
