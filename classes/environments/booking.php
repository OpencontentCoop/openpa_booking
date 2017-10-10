<?php

use Opencontent\Opendata\Api\Values\Content;
use Opencontent\Opendata\Api\Values\ContentData;
use Opencontent\Opendata\Api\EnvironmentLoader;

class BookingEnvironmentSettings extends DefaultEnvironmentSettings
{
    private $serviceClass;

    public function __construct(array $properties)
    {
        parent::__construct($properties);
        $this->serviceClass = new ObjectHandlerServiceControlBookingSalaPubblica();
    }

    protected function flatData(Content $content)
    {
        $flatContent = parent::flatData($content);

        $data = $flatContent->data->jsonSerialize();
        $language = eZLocale::currentLocaleCode();
        $object = $content->getContentObject($language);
        $node = $object->mainNode();
        if ($node->childrenCount() > 0){
            $requests = array();
            foreach($node->children() as $item){
                $requests[] = (int)$item->attribute('contentobject_id');
            }
            $data[$language]['subRequests'] = $requests;
        }elseif($data[$language]['isSubRequest'] == 1){
            $data[$language]['parentRequests'] = (int)$node->fetchParent()->attribute('contentobject_id');
        }

        $flatContent->data = new ContentData($data);
        return $flatContent;
    }

    protected function filterMetaData(Content $content)
    {
        $metadata = array(
            'id' => $content->metadata->id,
            'remoteId' => $content->metadata->remoteId,
            'mainNodeId' => (int)$content->metadata->mainNodeId,
            'ownerId' => $content->metadata->ownerId,
            'ownerName' => $content->metadata->ownerName,
            'classIdentifier' => $content->metadata->classIdentifier,
            'published' => $content->metadata->published,
            'modified' => $content->metadata->modified,
            'languages' => $content->metadata->languages,
            'name' => $content->metadata->name,
        );

        if ($this->serviceClass->prenotazioneClassIdentifier() == $content->metadata->classIdentifier) {
            foreach ($content->metadata->stateIdentifiers as $stateIdentifier) {
                if (strpos($stateIdentifier, 'booking.') === 0) {
                    $metadata['bookingState'] = $stateIdentifier;
                    break;
                }
            }
        }

        $content->metadata = new ContentData($metadata);

        return $content;
    }

    protected function overrideIdentifier(Content $content)
    {
        $originalOverrideIdentifierSettings = $overrideIdentifierSettings = (array)EnvironmentLoader::ini()->variable('ContentSettings',
            'OverrideFieldIdentifierList');

        $overrideIdentifierSettings[] = 'text;purposeDescription';
        $overrideIdentifierSettings[] = 'range_user;userType';
        $overrideIdentifierSettings[] = 'associazione;association';
        $overrideIdentifierSettings[] = 'from_time;start';
        $overrideIdentifierSettings[] = 'to_time;end';
        $overrideIdentifierSettings[] = 'destinatari;recipientsDescription';
        $overrideIdentifierSettings[] = 'patrocinio;patronageRequired';
        $overrideIdentifierSettings[] = 'comunicazione;communicationServicesRequired';
        $overrideIdentifierSettings[] = 'sala;location';
        $overrideIdentifierSettings[] = 'subrequest;isSubRequest';
        EnvironmentLoader::ini()->setVariable('ContentSettings', 'OverrideFieldIdentifierList',
            $overrideIdentifierSettings);

        $content = parent::overrideIdentifier($content);
        EnvironmentLoader::ini()->setVariable('ContentSettings', 'OverrideFieldIdentifierList',
            $originalOverrideIdentifierSettings);

        return $content;
    }

    protected function removeBlackListedAttributes(Content $content)
    {
        $originalIdentifierBlackList = $identifierBlackList = (array) EnvironmentLoader::ini()->variable( 'ContentSettings', 'IdentifierBlackListForExternal' );
        $identifierBlackList[] = 'scheduler';
        $identifierBlackList[] = 'stuff';
        $identifierBlackList[] = 'order_id';
//        $identifierBlackList[] = 'price';

        EnvironmentLoader::ini()->setVariable('ContentSettings', 'IdentifierBlackListForExternal',
            $identifierBlackList);

        $content = parent::removeBlackListedAttributes($content);
        EnvironmentLoader::ini()->setVariable('ContentSettings', 'IdentifierBlackListForExternal',
            $originalIdentifierBlackList);

        return $content;
    }


}
