<?php

class OpenPABookingConfirmOrderHandler extends eZDefaultConfirmOrderHandler
{
    function sendOrderEmail($params)
    {
        $ini = eZINI::instance();
        if (isset($params['order']) and
            isset($params['email'])) {
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
