{set-block scope=root variable=subject}Richiesta di appuntamento con il Sindaco{/set-block}

E' stata inserita una richiesta di appuntamento.

Come responsabile del servizio, sei invitato a consultare l'indirizzo http://{ezini( "SiteSettings", "SiteURL" )}{concat( 'openpa_booking/view/appuntamento_sindaco/', $object.id )|ezurl(no)}

Se non desideri più ricevere queste segnalazioni, cambia le impostazioni di notifica "Prenotazioni" all'indirizzo:
http://{ezini( "SiteSettings", "SiteURL" )}{concat( "notification/settings/" )|ezurl( no )}

Cordiali saluti

--
{"%sitename notification system" |i18n( 'design/standard/notification',,hash( '%sitename', ezini( "SiteSettings", "SiteURL" ) ) )}