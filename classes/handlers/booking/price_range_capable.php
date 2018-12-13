<?php

interface OpenPABookingPriceRangeCapable
{
	public function getPriceDataByRangeType($bookableAttribute, $identifier);

	public function getRangeList($bookableAttribute, $bookingStartTimestamp = null, $bookingEndTimestamp = null);
}