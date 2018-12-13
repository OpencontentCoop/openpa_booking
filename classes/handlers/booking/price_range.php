<?php

class OpenPABookingPriceRange
{
	/**
	 * @var eZContentObject
	 */
	protected $bookableObject;

	/**
	 * @var eZContentObjectAttribute[]
	 */
	protected $bookableObjectDataMap;

	protected $hasPriceRangeDefinition;

	protected $isDatatypePriceRangeCapable;

	protected $datatypePriceRangeCapable;

	protected static $vatList;

	public static function instance(eZContentObject $bookableObject)
	{
		$bookableObjectDataMap = $bookableObject->dataMap();
		return new self($bookableObject, $bookableObjectDataMap);
	}

	protected function __construct(eZContentObject $bookableObject, $bookableObjectDataMap)
	{
		$this->bookableObject = $bookableObject;
		$this->bookableObjectDataMap = $bookableObjectDataMap;
		$this->hasPriceRangeDefinition = isset($this->bookableObjectDataMap['price_range']) && $this->bookableObjectDataMap['price_range']->hasContent();
		$this->isDatatypePriceRangeCapable = false;
		if ($this->hasPriceRangeDefinition){			
			if ($this->bookableObjectDataMap['price_range']->dataType() instanceof OpenPABookingPriceRangeCapable){
				$this->isDatatypePriceRangeCapable = true;
				$this->datatypePriceRangeCapable = $this->bookableObjectDataMap['price_range']->dataType();
			}			
		}
	}

	public function hasPriceRangeDefinition()
	{
		return $this->hasPriceRangeDefinition;
	}

	public function getPriceDataByRangeType($identifier)
	{		
		if ($this->isDatatypePriceRangeCapable){			
			return $this->datatypePriceRangeCapable->getPriceDataByRangeType($this->bookableObjectDataMap['price_range'], $identifier);
		}

		$data = array(
			'is_valid' => false,
			'price' => null,
			'vat' => null,
			'vat_included' => null,
		);
		
		foreach ($this->getRangeList() as $item) {
			if ($item['identifier'] == $identifier){
				$data = array(
					'is_valid' => true,
					'price' => $item['raw_price'],
					'vat' => $item['vat'],
					'vat_included' => $item['vat_included'],
				);
				break;
			}
		}

        return $data;
	}

	public function getRangeList($bookingStartTimestamp = null, $bookingEndTimestamp = null)
	{
		if ($this->isDatatypePriceRangeCapable){			
			return $this->datatypePriceRangeCapable->getRangeList($this->bookableObjectDataMap['price_range'], $bookingStartTimestamp, $bookingEndTimestamp);
		}

		$rangeList = array();
		$priceRangeMatrix = isset( $this->bookableObjectDataMap['price_range'] ) ? $this->bookableObjectDataMap['price_range']->content() : new eZMatrix('null');
		if (isset( $priceRangeMatrix->Matrix['rows'] )) {
            foreach ((array)$priceRangeMatrix->Matrix['rows']['sequential'] as $row) {
                $item = array(
                	'identifier' => $row['columns'][0],
                	'label' => $row['columns'][0],
                	'description' => $row['columns'][1],
                	'raw_price' => floatval($row['columns'][2]),
                	'price' => floatval($row['columns'][2]),
                	'price_without_vat' => floatval($row['columns'][2]),
                	'vat_included' => isset($row['columns'][3]) && intval($row['columns'][3] == '1'),
                	'vat' => isset($row['columns'][4]) ? $row['columns'][4] : eZVatType::dynamicVatType()->attribute('id'),
                	'vat_type' => eZVatType::dynamicVatType(),
                	'vat_percentage' => eZVatType::dynamicVatType()->attribute('percentage'),
                	'is_free' => $row['columns'][2] == 0,
                	'is_valid' => true,
                	'valid_hours' => (isset($row['columns'][5]) && !empty($row['columns'][5])) ? explode('-', $row['columns'][5]) : array(),
                );
                if (isset($row['columns'][3]) && isset($row['columns'][4])){
                	foreach (self::getVatList() as $vatType) {
                		if ($vatType->attribute('id') == $row['columns'][4]){
                			$item['vat_type'] = $vatType;
                			$item['vat_percentage'] = $vatType->attribute('percentage');                			
                			if ($item['vat_included']){
                				$item['price_without_vat'] = $item['price'] / ( $item['vat_percentage'] + 100 ) * 100;
                			}else{
                				$item['price'] = $item['price'] * ( $item['vat_percentage'] + 100 ) / 100;
                			}
                		}
                	}
                }
                if (count($item['valid_hours']) > 0 && $bookingStartTimestamp && $bookingEndTimestamp){
                	$item['is_valid'] = self::isBookingInRange($bookingStartTimestamp, $bookingEndTimestamp, $row['columns'][5]);                	
                }

                $rangeList[] = $item;
            }
        }

        return $rangeList;
	}

	public static function getVatList()
	{
		if (self::$vatList == null){
			self::$vatList = eZVatType::fetchList( true, true );
		}

		return self::$vatList;
	}

	public static function refreshVatList()
	{
		self::$vatList = null;
	}

	public static function isBookingInRange($bookingStartTimestamp, $bookingEndTimestamp, $rangeHoursString)
	{
		$startDateTime = new DateTime('now', new DateTimeZone('Europe/Rome'));
        $startDateTime->setTimestamp((int)$bookingStartTimestamp);
        $endDateTime = new DateTime('now', new DateTimeZone('Europe/Rome'));
        $endDateTime->setTimestamp((int)$bookingEndTimestamp);

        @list($checkStart, $checkEnd) = explode('-', $rangeHoursString);
        @list($checkStartHours, $checkStartMinutes) = explode(':', $checkStart);
        $checkStartDateTime = clone $startDateTime;
        $checkStartDateTime->setTime($checkStartHours, $checkStartMinutes);
        
        @list($checkEndHours, $checkEndMinutes) = explode(':', $checkEnd);
        $checkEndDateTime = clone $endDateTime;
        $checkEndDateTime->setTime($checkEndHours, $checkEndMinutes);

        return $startDateTime >= $checkStartDateTime && $endDateTime <= $checkEndDateTime;
	}
}