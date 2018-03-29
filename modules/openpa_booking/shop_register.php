<?php

/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();

$tpl = eZTemplate::factory();

if ($module->isCurrentAction('Cancel')) {
    $module->redirectTo('/shop/basket/');

    return;
}

$data = OpenPABookingUserShopAccountHandler::getAccountData();

$tpl->setVariable("input_error", false);

if ($module->isCurrentAction('Store')) {


    $data = OpenPABookingUserShopAccountHandler::fetchInput();

    try {
        OpenPABookingUserShopAccountHandler::validateData($data);
        OpenPABookingUserShopAccountHandler::createOrder($data);

        $module->redirectTo('/shop/confirmorder/');

        return;

    } catch (Exception $e) {
        $tpl->setVariable("input_error", true);
    }
}

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
