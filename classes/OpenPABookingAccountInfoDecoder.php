<?php

class OpenPABookingAccountInfoDecoder implements OcAccountInfoDecoderInterface
{
  public function getAccountInfo(eZOrder $order)
  {
    $accountInfo = $order->attribute('account_information');

    $myPayAccountInfo = new OcMyPayAccountInfo();

    if (isset($accountInfo['type']) && $accountInfo['type'] == 'persona_giuridica'){
      $myPayAccountInfo->setTipo('G');
    }

    return $myPayAccountInfo->setCodiceFiscale($accountInfo['vat_code'])
      ->setNomeCognome($accountInfo['first_name'] . ' ' . $accountInfo['last_name'])
      ->setIndirizzo($accountInfo['street2'])
      ->setCap($accountInfo['zip'])
      ->setLocalita($accountInfo['place'])
      ->setProvincia($accountInfo['state'])
      ->setNazione(!empty($accountInfo['country']) ? $accountInfo['country'] : 'IT')
      ->setEmail($accountInfo['email']);
  }
}
