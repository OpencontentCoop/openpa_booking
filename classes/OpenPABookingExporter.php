<?php

use Opencontent\Opendata\Api\AttributeConverterLoader;
use Opencontent\Opendata\Api\ContentSearch;

class OpenPABookingExporter extends SearchQueryCSVExporter
{
    public static $circoscrizioni = [];

    public function __construct($parentNodeId, $queryString)
    {        
        $pivotCircoscrizioniQuery = "subtree [" . OpenPABooking::instance()->rootNode()->attribute('node_id') . "] and classes [sala_pubblica] facets [circoscrizione|alpha|100] pivot [facet=>[attr_circoscrizione_s,meta_id_si],mincount=>1]";
        $contentSearch = new ContentSearch();
        $contentSearch->setEnvironment(new DefaultEnvironmentSettings());
        $pivotCircoscrizioniSearch = (array)$contentSearch->search($pivotCircoscrizioniQuery);
        if (isset($pivotCircoscrizioniSearch['pivot']["attr_circoscrizione_s,meta_id_si"])){
            foreach ($pivotCircoscrizioniSearch['pivot']["attr_circoscrizione_s,meta_id_si"] as $item) {
                $circoscrizione = $item['value'];
                $idList = [];
                foreach ($item['pivot'] as $subItem) {
                    $idList[] = $subItem['value'];
                }
                if (!empty($circoscrizione)){
                    self::$circoscrizioni[] = [
                        'name' => $circoscrizione,
                        'id_list' => $idList
                    ];
                }
            }
        }

        $this->CSVheaders = [
            'ID' => ['metadata' => 'id'],
            'Inserimento' => ['metadata' => 'published'],
            'Sala' => ['data' => 'prenotazione_sala/sala/ezobjectrelation'],
            'Circoscrizione' => ['custom' => function($item){
                $circoscrizioni = OpenPABookingExporter::$circoscrizioni;
                $language = eZLocale::currentLocaleCode();
                $data = $item['data'][$language]; 
                if (isset($data['sala']['content'])){
                    $valueList = [];
                    foreach ($data['sala']['content'] as $sala) {
                        foreach ($circoscrizioni as $circoscrizione) {
                            if (in_array($sala['id'], $circoscrizione['id_list'])){
                                $valueList[] = $circoscrizione['name'];
                            }
                        }
                    }
                    return implode(', ', $valueList);
                }else{
                    return '';
                }
            }],
            'Inizio' => ['data' => 'prenotazione_sala/from_time/ezdatetime'],
            'Termine' => ['data' => 'prenotazione_sala/to_time/ezdatetime'],
            'Richiedente' => ['metadata' => 'ownerName'],            
            'Associazione' => ['data' => 'prenotazione_sala/associazione/ezobjectrelationlist'],
            'Scopo' => ['data' => 'prenotazione_sala/text/ezxmltext'],
            'Tipologia' => ['data' => 'prenotazione_sala/range_user/ezstring'],
            'Costo' => ['data' => 'prenotazione_sala/price/ezprice'],
            'Ordine' => ['data' => 'prenotazione_sala/order_id/ezinteger'],
            'Materiale' => ['data' => 'prenotazione_sala/materiale_informativo/ezbinaryfile'],
            'Sottorichiesta' => ['custom' => function($item){
                $language = eZLocale::currentLocaleCode();
                $data = $item['data'][$language]; 
                return $data['subrequest']['content'] ? 'X' : '';
            }],
        ];
        
        parent::__construct($parentNodeId, $queryString);
    }

    protected function getPaginateTemplate($variables)
    {
        $tpl = eZTemplate::factory();
        foreach($variables as $key => $value){
            $tpl->setVariable($key, $value);
        }
        return $tpl->fetch('design:booking/download_paginate.tpl');
    }

    protected function csvHeaders($item)
    {
        return array_keys($this->CSVheaders);
    }

    function transformItem($item)
    {        
        $data = $item['data'][$this->language];

        $stringData = array();

        foreach ($this->CSVheaders as $key => $headerHandler) {
            if (isset($headerHandler['metadata'])){

                $value = $item['metadata'][$headerHandler['metadata']];
                if ($headerHandler['metadata'] == 'published'){
                    $value = date('d/m/Y H:i', strtotime($value));
                }elseif (is_array($value)){
                    $value = $value[$this->language];
                }

            }elseif (isset($headerHandler['data'])){

                list( $classIdentifier, $identifier, $datatype ) = explode('/', $headerHandler['data']);
                $converter = AttributeConverterLoader::load(
                    $classIdentifier,
                    $identifier,
                    $datatype
                );
                switch ($datatype) {
                    case 'ezobjectrelation':
                    case 'ezobjectrelationlist': {
                        $value = $converter->toCSVString($data[$identifier]['content'], $this->language);
                    } break;

                    case 'ezdatetime': {
                        $value = date('d/m/Y H:i', strtotime($data[$identifier]['content']));
                    } break;                    

                    default: {
                        $value = $converter->toCSVString($data[$identifier]['content']);
                    } break;
                }

            }elseif (isset($headerHandler['custom']) && is_callable($headerHandler['custom'])){
                $value = $headerHandler['custom']($item);
            }

            $stringData[$key] = $value;
        }

        return $stringData;
    }
}