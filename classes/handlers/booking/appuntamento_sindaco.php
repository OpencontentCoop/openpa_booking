<?php

class BookingHandlerAppuntamentoSindaco extends BookingHandlerBase implements OpenPABookingHandlerInterface
{
    public static function name()
    {
        return 'Prenotazioni appuntamenti sindaco';
    }

    public static function identifier()
    {
        return 'appuntamento_sindaco';
    }

    protected function serviceClass()
    {
        return new ObjectHandlerServiceControlBookingAppuntamentoSindaco();
    }
}