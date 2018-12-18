<?php

class OpenPABookingConfirmOrderHandler extends eZDefaultConfirmOrderHandler
{
    function execute( $params = array() )
    {
        $ini = eZINI::instance();
        $sendOrderEmail = $ini->variable( 'ShopSettings', 'SendOrderEmail' );
        if ( $sendOrderEmail == 'enabled' )
        {
            $this->sendOrderEmail( $params );
        }
        if (isset($params['order']) and isset($params['email'])) {	        
	        $order = $params['order'];
	        $email = $params['email'];
	        $productCollectionID = $order->attribute('productcollection_id');
	        $productCollection = eZProductCollection::fetch($productCollectionID);	        
	        $service = ObjectHandlerServiceControlBookingSalaPubblica::instanceFromProductCollection($productCollection);
	        if ($service instanceof ObjectHandlerServiceControlBookingSalaPubblica){
	            $service->handleConfirmOrder($order, $email);
	        }
	    }
    }

    function sendOrderEmail($params)
    {
        $ini = eZINI::instance();
        if (isset($params['order']) and isset($params['email'])) {
            $order = $params['order'];
            $email = $params['email'];

            $tpl = eZTemplate::factory();
            $tpl->setVariable('order', $order);
            $templateResult = $tpl->fetch('design:shop/orderemail.tpl');

            $subject = $tpl->variable('subject');

            $mail = new eZMail();

            $emailSender = $ini->variable('MailSettings', 'EmailSender');
            if (!$emailSender) {
                $emailSender = $ini->variable("MailSettings", "AdminEmail");
            }

            if ($tpl->hasVariable('content_type')) {
                $mail->setContentType($tpl->variable('content_type'));
            }

            $mail->setReceiver($email);
            $mail->setSender($emailSender, $ini->variable("SiteSettings", "SiteName"));
            $mail->setSubject($subject);
            $mail->setBody($templateResult);
            $mailResult = eZMailTransport::send($mail);

            $email = $ini->variable('MailSettings', 'AdminEmail');

            $mail = new eZMail();

            if ($tpl->hasVariable('content_type')) {
                $mail->setContentType($tpl->variable('content_type'));
            }

            $mail->setReceiver($email);
            $mail->setSender($emailSender, $ini->variable("SiteSettings", "SiteName"));
            $mail->setSubject($subject);
            $mail->setBody($templateResult);
            $mailResult = eZMailTransport::send($mail);
        }
    }
}
