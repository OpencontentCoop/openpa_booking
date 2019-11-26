<?php


class OpenPABooking
{
    const SECTION_IDENTIFIER = "booking";
    const SECTION_NAME = "Booking";

    public static $stateGroupIdentifier = 'moderation';
    public static $stateIdentifiers = array(
        'skipped' => "Non necessita di moderazione",
        'draft' => "In lavorazione",
        'waiting' => "In attesa di moderazione",
        'accepted' => "Accettato",
        'refused' => "Rifiutato",
    );

    public static $programStateGroupIdentifier = 'programma_eventi';
    public static $programStateIdentifiers = array(
        'public' => "Pubblico",
        'private' => "Privato"
    );

    public static $privacyStateGroupIdentifier = 'privacy';
    public static $privacyStateIdentifiers = array(
        'public' => "Pubblico",
        'private' => "Privato"
    );

    /**
     * @var eZContentObject
     */
    protected $root;

    /**
     * @var eZContentObjectAttribute[]
     */
    protected $rootDataMap = array();

    private static $_instance;

    public static function instance()
    {
        if (self::$_instance === null) {
            self::$_instance = new OpenPABooking();
        }

        return self::$_instance;
    }

    protected function __construct()
    {
        $this->root = eZContentObject::fetchByRemoteID(self::rootRemoteId());
        if ($this->root instanceof eZContentObject) {
            $this->rootDataMap = $this->root->attribute('data_map');
        }
    }

    public function checkAccess($nodeId)
    {
        //@todo
        return true;
    }

    public function rootObject()
    {
        return $this->root;
    }

    /**
     * @return eZContentObjectTreeNode
     */
    public function rootNode()
    {
        return $this->root->attribute('main_node');
    }

    public function needModeration(eZContentObject $contentObject)
    {
        //@todo
        return $contentObject->attribute('current_version') == 1;
    }

    public function rootHasAttribute($identifier)
    {
        return isset($this->rootDataMap[$identifier]);
    }

    public function getBookingCacheDir()
    {
        $cacheFilePath = eZDir::path(
            array( eZSys::cacheDirectory(), 'openpa_booking' )
        );
        return $cacheFilePath;
    }

    /**
     * Remote id di rootNode
     *
     * @return string
     */
    public static function rootRemoteId()
    {
        return OpenPABase::getCurrentSiteaccessIdentifier() . '_openpa_booking';
    }

    private static function getNodeIdFromRemoteId($remote, $createIfNotExists = true, $params = array())
    {
        $db = eZDB::instance();
        $results = $db->arrayQuery("SELECT ezcotn.main_node_id, ezco.remote_id FROM ezcontentobject_tree as ezcotn, ezcontentobject as ezco WHERE ezco.id = ezcotn.contentobject_id AND ezco.remote_id = '{$remote}'");
        foreach($results as $result){
            return $result['main_node_id'];
        }
        if ($createIfNotExists){
            $newObject = eZContentFunctions::createAndPublishObject(
                array_merge(array(
                    'parent_node_id' => self::instance()->rootNode()->attribute('node_id'),
                    'remote_id' => $remote,
                    'class_identifier' => 'folder',
                    'attributes' => array( 'name' => $remote )
                ), $params )
            );
            if ($newObject instanceof eZContentObject){
                return $newObject->attribute('main_node_id');
            }
        }
        return OpenPAAppSectionHelper::instance()->rootNode()->attribute('node_id');
    }

    public static function locationsRemoteId()
    {
        return OpenPABooking::rootRemoteId() . '_locations_container';
    }

    public static function locationsNodeId()
    {
        return self::getNodeIdFromRemoteId(self::locationsRemoteId());
    }

    public static function stuffRemoteId()
    {
        return OpenPABooking::rootRemoteId() . '_stuff_container';
    }

    public static function stuffNodeId()
    {
        return self::getNodeIdFromRemoteId(self::stuffRemoteId());
    }

    public static function moderatorGroupRemoteId()
    {
        return OpenPABooking::rootRemoteId() . '_moderators';
    }

    public static function externalUsersGroupRemoteId()
    {
        return OpenPABooking::rootRemoteId() . '_external_users';
    }

    public static function moderatorGroupNodeId($createIfNotExists = false, $params = array())
    {
        return self::getNodeIdFromRemoteId(self::moderatorGroupRemoteId(), $createIfNotExists, $params);
    }

    public static function externalUsersGroupNodeId()
    {
        return self::getNodeIdFromRemoteId(self::externalUsersGroupRemoteId());
    }

    public static function classIdentifiers()
    {        
        return array('booking_root');
    }

    /**
     * @param string $identifier
     * @param bool $replaceBracket
     *
     * @return string
     */
    public function getAttributeString($identifier, $replaceBracket = true)
    {
        $data = '';
        if (isset( $this->rootDataMap[$identifier] )) {
            if ($this->rootDataMap[$identifier] instanceof eZContentObjectAttribute) {
                if ($replaceBracket)
                    $data = self::replaceBracket($this->rootDataMap[$identifier]->toString());
                else
                    $data = $this->rootDataMap[$identifier]->toString();
            }
        }

        return $data;
    }

    /**
     * @param string $identifier
     *
     * @return eZContentObjectAttribute
     */
    public function getAttribute($identifier)
    {
        $data = new eZContentObjectAttribute(array());
        if (isset( $this->rootDataMap[$identifier] )) {
            $data = $this->rootDataMap[$identifier];
        }

        return $data;
    }

    public function siteUrl()
    {
        $currentSiteaccess = eZSiteAccess::current();
        $sitaccessIdentifier = $currentSiteaccess['name'];
        if ( !self::isBookingSiteAccessName( $sitaccessIdentifier ) )
        {
            $sitaccessIdentifier = self::getBookingSiteAccessName();
        }
        $path = "settings/siteaccess/{$sitaccessIdentifier}/";
        $ini = new eZINI( 'site.ini.append', $path, null, null, null, true, true );
        return rtrim( $ini->variable( 'SiteSettings', 'SiteURL' ), '/' );
    }

    public static function isBookingSiteAccessName( $currentSiteAccessName )
    {
        return OpenPABase::getCustomSiteaccessName( 'booking' ) == $currentSiteAccessName;
    }

    public static function getBookingSiteAccessName()
    {
        return OpenPABase::getCustomSiteaccessName( 'booking' );
    }

    public function imagePath($identifier)
    {
        $data = false;
        if (isset( $this->rootDataMap[$identifier] )) {
            if ( $this->rootDataMap[$identifier] instanceof eZContentObjectAttribute
                 && $this->rootDataMap[$identifier]->hasContent() )
            {
                /** @var eZImageAliasHandler $content */
                $content = $this->rootDataMap[$identifier]->content();
                $original = $content->attribute( 'original' );
                $data = $original['full_path'];
            }
            else
            {
                $data = '/extension/openpa_booking/design/standard/images/logo_default.png';
            }
        }
        return $data;
    }


    /**
     * Replace [ ] with strong html tag
     *
     * @param string $string
     *
     * @return string
     */
    private static function replaceBracket($string)
    {
        $string = str_replace('[', '<strong>', $string);
        $string = str_replace(']', '</strong>', $string);

        return $string;
    }

    public function isCollaborationModeEnabled()
    {
        return (bool)$this->getAttributeString('collaboration_mode', false) == true;
    }

    public function isCommentEnabled()
    {
        return (bool)$this->getAttributeString('enable_comment', false) == true;
    }

    public function isHeaderOnlyLogoEnabled()
    {
        return (bool)$this->getAttributeString('enable_header_only_logo', false) == true;
    }

    public function isStuffSubWorkflowEnabled()
    {
        return false;
    }

    public function isStuffBookingEnabled()
    {
        return !(bool)$this->getAttributeString('disable_booking_stuff', false) == true;
    }

    public function getViewList()
    {
        $views = array('list','map');
        if ($this->isStuffBookingEnabled()){
            $views[] = 'stuff';
        }

        return $views;
    }

    public function getDefaultView()
    {
        $default = 'list';

        $viewString = $this->getAttributeString('default_view', false);
        switch ($viewString){
            case 'Mappa sale';
                $view = 'map';
                break;

            case 'Elenco sale';
                $view = 'list';
                break;

            case 'Elenco attrezzatura';
                $view = 'stuff';
                break;

            default:
                $view = false;
        }
        if (!in_array($view, $this->getViewList())){
            $view = $default;
        }

        return $view;
    }

    public function freeBookingNeedsCheckout()
    {
        return (bool)$this->getAttributeString('go_to_checkout_in_free_booking', false) == true;
    }

    public function isSubrequestDisabled()
    {
        return (bool)$this->getAttributeString('disable_subrequest', false) == true;
    }
}
