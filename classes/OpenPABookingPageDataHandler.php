<?php

class OpenPABookingPageDataHandler implements OCPageDataHandlerInterface
{

    public function booking()
    {
        return OpenPABooking::instance();
    }

    public function siteTitle()
    {
        return strip_tags( $this->logoTitle() );
    }

    public function siteUrl()
    {
        return $this->booking()->siteUrl();
    }

    public function assetUrl()
    {
        $siteUrl = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );
        $parts = explode( '/', $siteUrl );
        if ( count( $parts ) >= 2 )
        {
            array_pop( $parts );
            $siteUrl = implode( '/', $parts );
        }
        return rtrim( $siteUrl, '/' );
    }

    public function logoPath()
    {
        return $this->booking()->imagePath('logo');
    }

    public function logoTitle()
    {
        return $this->booking()->getAttributeString( 'logo_title' );
    }

    public function logoSubtitle()
    {
        return $this->booking()->getAttributeString( 'logo_subtitle' );
    }

    public function headImages()
    {
        return array(
            "apple-touch-icon-114x114-precomposed" => null,
            "apple-touch-icon-72x72-precomposed" => null,
            "apple-touch-icon-57x57-precomposed" => null,
            "favicon" => null
        );
    }

    public function needLogin()
    {
        // TODO: Implement needLogin() method.
    }

    public function attributeContacts()
    {
        return $this->booking()->getAttribute('contacts');
    }

    public function attributeFooter()
    {
        return $this->booking()->getAttribute('footer');
    }

    public function textCredits()
    {
        return ezpI18n::tr( 'booking', 'OpenPA Booking - realizzato da OpenContent con OpenPA' );
    }

    public function googleAnalyticsId()
    {
        return OpenPAINI::variable( 'Seo', 'GoogleAnalyticsAccountID', false );
    }

    public function cookieLawUrl()
    {
        $href = 'openpa_booking/info/cookie';
        eZURI::transformURI( $href, false, 'full' );
        return $href;
    }

    public function menu()
    {
        $infoChildren = array(
            array(
                'name' => ezpI18n::tr( 'booking/menu', 'Faq' ),
                'url' => 'openpa_booking/info/faq',
                'has_children' => false,
            ),
            array(
                'name' => ezpI18n::tr( 'booking/menu', 'Privacy' ),
                'url' => 'openpa_booking/info/privacy',
                'has_children' => false,
            ),
            array(
                'name' => ezpI18n::tr( 'booking/menu', 'Termini di utilizzo' ),
                'url' => 'openpa_booking/info/terms',
                'has_children' => false,
            )
        );

        $menu = array();

        $menu[] = array(
            'name' => ezpI18n::tr( 'booking/menu', 'Spazi pubblici' ),
            'url' => 'openpa_booking/locations',
            'highlight' => false,
            'has_children' => false
        );

        $menu[] = array(
            'name' => ezpI18n::tr( 'booking/menu', 'Attrezzatura' ),
            'url' => 'openpa_booking/stuff',
            'highlight' => false,
            'has_children' => false
        );

        if (eZUser::currentUser()->isRegistered()){
            $menu[] = array(
                'name' => ezpI18n::tr( 'booking/menu', 'Le mie prenotazioni' ),
                'url' => 'openpa_booking/view/sala_pubblica/',
                'highlight' => false,
                'has_children' => false
            );
        }

        if ($this->booking()->isCollaborationModeEnabled()) {

        }

        $menu[] = array(
            'name' => ezpI18n::tr( 'booking/menu', 'Informazioni' ),
            'url' => 'openpa_booking/info',
            'highlight' => false,
            'has_children' => true,
            'children' => $infoChildren
        );

        return $menu;
    }

    public function userMenu()
    {
        $userMenu = array(
            array(
                'name' => ezpI18n::tr( 'booking/menu', 'Profilo' ),
                'url' => 'user/edit',
                'highlight' => false,
                'has_children' => false
            ),
//            array(
//                'name' => ezpI18n::tr( 'booking/menu', 'Notifiche' ),
//                'url' => 'notification/settings',
//                'highlight' => false,
//                'has_children' => false
//            )
        );

        $hasAccess = eZUser::currentUser()->hasAccessTo( 'booking', 'config' );
        if ( $hasAccess['accessWord'] == 'yes' ) {
            $userMenu[] = array(
                'name' => ezpI18n::tr('booking/menu', 'Settings'),
                'url' => 'openpa_booking/config',
                'highlight' => false,
                'has_children' => false
            );
        }
        $userMenu[] = array(
            'name' => ezpI18n::tr( 'booking/menu', 'Esci' ),
            'url' => 'user/logout',
            'highlight' => false,
            'has_children' => false
        );
        return $userMenu;
    }

    public function bannerPath()
    {
        return $this->booking()->imagePath('banner');
    }

    public function bannerTitle()
    {
        return $this->booking()->getAttributeString( 'banner_title' );
    }

    public function bannerSubtitle()
    {
        return $this->booking()->getAttributeString( 'banner_subtitle' );
    }
}
