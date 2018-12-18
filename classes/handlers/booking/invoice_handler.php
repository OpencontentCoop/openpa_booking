<?php

interface OpenPABookingInvoiceHandler
{
	public function requestInvoice(eZContentObjectAttribute $contentObjectAttribute, eZOrder $order);

    public function downloadInvoice(eZContentObjectAttribute $contentObjectAttribute);
}