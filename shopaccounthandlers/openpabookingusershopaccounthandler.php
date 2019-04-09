<?php

class OpenPABookingUserShopAccountHandler extends eZUserShopAccountHandler
{
    private static $accountKeys = array(
        'first_name' => array(
            'is_required' => true,
            'input_name' => 'FirstName'
        ),
        'last_name' => array(
            'is_required' => false,
            'input_name' => 'LastName'
        ),
        'email' => array(
            'is_required' => true,
            'input_name' => 'EMail'
        ),
        'street1' => array(
            'is_required' => false,
            'input_name' => 'Street1'
        ),
        'street2' => array(
            'is_required' => true,
            'input_name' => 'Street2'
        ),
        'zip' => array(
            'is_required' => true,
            'input_name' => 'Zip'
        ),
        'place' => array(
            'is_required' => true,
            'input_name' => 'Place'
        ),
        'country' => array(
            'is_required' => true,
            'input_name' => 'Country'
        ),
        'comment' => array(
            'is_required' => false,
            'input_name' => 'Comment'
        ),
        'state' => array(
            'is_required' => false,
            'input_name' => 'State'
        ),
        'vat_code' => array(
            'is_required' => true,
            'input_name' => 'VatCode'
        ),
    );

    private static $customAccountKeys;

    /**
     * var ObjectHandlerServiceControlBookingSalaPubblica
     */
    private static $bookingService;

    private static function getBookingService()
    {
        if (self::$bookingService === null){
            $basket = eZBasket::currentBasket();
            $productCollectionID = $basket->attribute('productcollection_id');
            $productCollection = eZProductCollection::fetch($productCollectionID);
            $service = ObjectHandlerServiceControlBookingSalaPubblica::instanceFromProductCollection($productCollection);
            if ($service instanceof ObjectHandlerServiceControlBookingSalaPubblica){
                self::$bookingService = $service;
            }
        }

        return self::$bookingService;
    }

    function verifyAccountInformation()
    {
        return false;
    }

    /**
     * @param eZModule $module
     */
    function fetchAccountInformation(&$module)
    {
        $module->redirectTo('/openpa_booking/shop_register/');
    }

    /**
     * @param eZOrder $order
     *
     * @return string|false
     */
    function email($order)
    {
        $email = false;
        $accountInformation = $this->accountInformation($order);
        if ($accountInformation['email']) {
            $email = $accountInformation['email'];
        }

        return $email;
    }

    /**
     * @param eZOrder $order
     *
     * @return string
     */
    function accountName($order)
    {
        $accountName = '';
        $accountInformation = $this->accountInformation($order);
        if ($accountInformation['first_name'] || $accountInformation['last_name']) {
            $accountName = $accountInformation['first_name'] . ' ' . $accountInformation['last_name'];
        }

        return trim($accountName);
    }

    /**
     * @param eZOrder $order
     *
     * @return array
     */
    function accountInformation($order)
    {        
        $xmlString = $order->attribute('data_text_1');
        return self::getAccountInformationFromXml($xmlString);
    }

    private static function getAccountInformationFromXml($xmlString)
    {
        $accountInformation = array_fill_keys(array_keys(self::getAccountDataSettings()), false);
        if ($xmlString != null) {
            $dom = new DOMDocument('1.0', 'utf-8');
            if ($dom->loadXML($xmlString)) {
                foreach (array_keys(self::getAccountDataSettings()) as $key) {
                    $tagName = str_replace('_', '-', $key);
                    $node = $dom->getElementsByTagName($tagName)->item(0);
                    if ($node) {
                        $accountInformation[$key] = $node->textContent;
                    }
                }
            }
        }

        return $accountInformation;
    }

    private static function storeAccountInformationToXml($accountInformation)
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $root = $doc->createElement("shop_account");
        $doc->appendChild($root);

        foreach ($accountInformation as $key => $value) {
            $tagName = str_replace('_', '-', $key);
            $node = $doc->createElement($tagName, $value);
            $root->appendChild($node);
        }

        return $doc->saveXML();
    }

    public static function fetchInput()
    {
        $http = eZHTTPTool::instance();
        $data = array();
        foreach (self::getAccountDataSettings() as $key => $settings) {
            $postValue = trim($http->postVariable($settings['input_name'], ''));
            if ($postValue == "") {
                $postValue = false;
            }
            $data[$key] = $postValue;
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public static function validateData($data)
    {
        foreach (self::getAccountDataSettings() as $key => $settings) {
            if ($settings['is_required'] && $data[$key] == false) {
                throw new Exception("Field $key is required");
            }
        }
    }

    public static function createOrder($accountInformation)
    {
        $basket = eZBasket::currentBasket();
        $db = eZDB::instance();
        $http = eZHTTPTool::instance();

        $db->begin();
        $order = $basket->createOrder();

        $xmlString = self::storeAccountInformationToXml($accountInformation);
        $order->setAttribute('data_text_1', $xmlString);
        $order->setAttribute('account_identifier', "ez");
        $order->setAttribute('ignore_vat', 0);
        $order->store();
        $db->commit();
        if ($accountInformation['country']) {
            eZShopFunctions::setPreferredUserCountry($accountInformation['country']);
        }
        $http->setSessionVariable('MyTemporaryOrderID', $order->attribute('id'));
    }

    public static function getAccountData()
    {
        $accountInformation = array_fill_keys(array_keys(self::getAccountDataSettings()), false);

        $user = eZUser::currentUser();
        if ($user->isRegistered()) {
            /** @var eZOrder[] $orderList */
            $orderList = eZOrder::activeByUserID($user->attribute('contentobject_id'));
            if (count($orderList) > 0) {
                $lastOrder = $orderList[0];
                $accountInformation = $lastOrder->accountInformation();
            } else {
                $accountInformation = self::getAccountInformationFromUser($user);
            }
        }

        return $accountInformation;
    }

    public static function getAccountDataSettings()
    {                
        if (self::getBookingService() instanceof ObjectHandlerServiceControlBookingSalaPubblica){
            return self::getBookingService()->getAccountDataSettings();    
        }
        return self::$accountKeys;
    }

    private static function getAccountInformationFromUser(eZUser $user)
    {
        $accountInformation = array_fill_keys(array_keys(self::getAccountDataSettings()), false);

        $userObject = $user->attribute( 'contentobject' );
        
        $userMap = $userObject->dataMap();
        if (isset($userMap['first_name'])){
            $accountInformation['first_name'] = $userMap['first_name']->content();    
        }
        if (isset($userMap['last_name'])){
            $accountInformation['first_name'] += ' ' . $userMap['last_name']->content();    
        }
                
        $accountInformation['email'] = $user->attribute( 'email' );

        return $accountInformation;
    }

}
