<?php


class BookingApiProvider implements ezpRestProviderInterface
{
    public function getRoutes()
    {
        $routes = array(

            //GET /api/booking/v1/sala_pubblica/locations
            'bookingListLocations' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/locations',
                    'BookingApiController',
                    'listLocations',
                    array(),
                    'http-get'
                ), 1
            ),

            //GET /api/booking/v1/sala_pubblica/locations/$Id
            'bookingGetLocation' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/locations/:Id',
                    'BookingApiController',
                    'getLocation',
                    array(),
                    'http-get'
                ), 1
            ),

            //POST /api/booking/v1/sala_pubblica/locations/$Id
            'bookingAddLocation' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/locations',
                    'BookingApiController',
                    'addLocation',
                    array(),
                    'http-post'
                ), 1
            ),

            //GET /api/booking/v1/sala_pubblica/locations/availability/$Day/$From/$To
            'bookingGetLocationsAvailability' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/locations/availability/:Day/:From/:To',
                    'BookingApiController',
                    'getLocationsAvailability',
                    array(),
                    'http-get'
                ), 1
            ),

            //GET /api/booking/v1/sala_pubblica/locations/$Id/availability/$Day/$From/$To
            'bookingGetLocationAvailability' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/locations/:Id/availability/:Day/:From/:To',
                    'BookingApiController',
                    'getLocationAvailability',
                    array(),
                    'http-get'
                ), 1
            ),

            //GET /api/booking/v1/sala_pubblica/locations/$Id/busy/$From/$To
            'bookingGetLocationUnavailability' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/locations/:Id/busy/:From/:To',
                    'BookingApiController',
                    'getLocationUnavailability',
                    array(),
                    'http-get'
                ), 1
            ),

            //POST /api/booking/v1/sala_pubblica/booking
            'bookingAddBooking' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/booking',
                    'BookingApiController',
                    'addBooking',
                    array(),
                    'http-post'
                ), 1
            ),

            //GET /api/booking/v1/sala_pubblica/booking
            'bookingListBooking' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/booking',
                    'BookingApiController',
                    'listBooking',
                    array(),
                    'http-get'
                ), 1
            ),

            //GET /api/booking/v1/sala_pubblica/booking/$Id
            'bookingGetBooking' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/booking/:Id',
                    'BookingApiController',
                    'getBooking',
                    array(),
                    'http-get'
                ), 1
            ),

            //POST /api/booking/v1/sala_pubblica/booking/$Id/comment
            'bookingAddComment' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/booking/:Id/comment',
                    'BookingApiController',
                    'addComment',
                    array(),
                    'http-post'
                ), 1
            ),

            //GET /api/booking/v1/sala_pubblica/booking/$Id/comment
            'bookingListComment' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/booking/:Id/comment',
                    'BookingApiController',
                    'listComment',
                    array(),
                    'http-get'
                ), 1
            ),

            //POST /api/booking/v1/sala_pubblica/booking/$Id/available
            'bookingMarkAvailable' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/booking/:Id/available',
                    'BookingApiController',
                    'markAvailable',
                    array(),
                    'http-post'
                ), 1
            ),

            //POST /api/booking/v1/sala_pubblica/booking/$Id/deny
            'bookingMarkDeny' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/booking/:Id/deny',
                    'BookingApiController',
                    'markDeny',
                    array(),
                    'http-post'
                ), 1
            ),

            //POST /api/booking/v1/sala_pubblica/booking/$Id/approve
            'bookingMarkApprove' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/booking/:Id/approve',
                    'BookingApiController',
                    'markApprove',
                    array(),
                    'http-post'
                ), 1
            ),

            //POST /api/booking/v1/sala_pubblica/booking/$Id/expire
            'bookingMarkExpire' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/booking/:Id/expire',
                    'BookingApiController',
                    'markExpire',
                    array(),
                    'http-post'
                ), 1
            ),

            //POST /api/booking/v1/sala_pubblica/booking/$Id/success
            'bookingMarkSuccess' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/booking/:Id/success',
                    'BookingApiController',
                    'markSuccess',
                    array(),
                    'http-post'
                ), 1
            ),

            //POST /api/booking/v1/sala_pubblica/booking/$Id/fail
            'bookingMarkFail' => new ezpRestVersionedRoute(
                new BookingApiRailsRoute(
                    '/:Handler/booking/:Id/fail',
                    'BookingApiController',
                    'markFail',
                    array(),
                    'http-post'
                ), 1
            ),
        );
        return $routes;
    }

    public function getViewController()
    {
        return new BookingApiViewController();
    }

}
