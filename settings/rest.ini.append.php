<?php /* #?ini charset="utf-8"?

[ApiProvider]
ProviderClass[booking]=BookingApiProvider
ProviderClass[bookinguser]=SocialUserApiProvider

[BookingApiController_CacheSettings]
ApplicationCache=disabled

[SocialUserApiController_CacheSettings]
ApplicationCache=disabled

[Authentication]
RequireAuthentication=enabled
AuthenticationStyle=ezpRestBasicAuthStyle
DefaultUserID=

[PreRoutingFilters]
Filters[]=BookingPreRoutingFilter

*/ ?>
