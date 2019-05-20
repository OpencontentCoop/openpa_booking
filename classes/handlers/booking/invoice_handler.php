<?php

interface OpenPABookingInvoiceHandler
{
	public function requestInvoice(eZContentObjectAttribute $contentObjectAttribute, eZOrder $order, $forceRebuild = false);

    public function downloadInvoice(eZContentObjectAttribute $contentObjectAttribute);
}