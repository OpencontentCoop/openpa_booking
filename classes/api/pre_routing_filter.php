<?php

class BookingPreRoutingFilter implements ezpRestPreRoutingFilterInterface
{
    public function __construct(ezcMvcRequest $request)
    {
        if (strpos($request->requestId, 'api/booking') !== false) {
            eZDebug::setHandleType(eZDebug::HANDLE_FROM_PHP);
            $currentSiteaccess = eZSiteAccess::current();
            $bookingSiteAccessName = OpenPABase::getCustomSiteaccessName('booking');;
            if ($currentSiteaccess['name'] !== $bookingSiteAccessName) {
                eZINI::resetAllInstances();
                eZExtension::activateExtensions('default');
                eZSiteAccess::change(array('name' => $bookingSiteAccessName));
                eZExtension::activateExtensions('access');
            }
        }
    }

    public function filter()
    {
    }

}
