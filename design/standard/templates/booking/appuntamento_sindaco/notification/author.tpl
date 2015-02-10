{set-block scope=root variable=subject}Vostra richiesta di appuntamento con il Sindaco {$object.data_map.sala.content.name|wash()}{/set-block}

La presente per comunicarti che la richiesta di di appuntamento con il Sindaco è stata inoltrata al responsabile del servizio.

Puoi controllare l'andamento della pratica visitando l'indirizzo http://{ezini( "SiteSettings", "SiteURL" )}{concat( 'openpa_booking/view/appuntamento_sindaco/', $object.id )|ezurl(no)}

Se non desideri più ricevere queste segnalazioni, cambia le impostazioni di notifica "Prenotazioni" all'indirizzo:
http://{ezini( "SiteSettings", "SiteURL" )}{concat( "notification/settings/" )|ezurl( no )}

Cordiali saluti

--
{"%sitename notification system" |i18n( 'design/standard/notification',,hash( '%sitename', ezini( "SiteSettings", "SiteURL" ) ) )}