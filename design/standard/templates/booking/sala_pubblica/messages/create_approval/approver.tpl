{set-block scope=root variable=email_subject}Richiesta di prenotazione n. {$object.id} sala {$object.data_map.sala.content.name|wash()}{/set-block}
{set-block scope=root variable=content_type}text/html{/set-block}
{set-block scope=root variable=email_body}
    E' stata inserita una richiesta di prenotazione per la sala pubblica in oggetto.<br/>

    Come responsabile del servizio, sei invitato a consultare l'indirizzo http://{ezini( "SiteSettings", "SiteURL" )}{concat( 'openpa_booking/view/sala_pubblica/', $object.id )|ezurl(no)}<br/>

    {*Se non desideri più ricevere queste segnalazioni, cambia le impostazioni di notifica "Prenotazioni" all'indirizzo:
    http://{ezini( "SiteSettings", "SiteURL" )}{concat( "notification/settings/" )|ezurl( no )}<br/>*}

    Cordiali saluti<br/><br/>

    --<br/>
{"%sitename notification system" |i18n( 'design/standard/notification',,hash( '%sitename', ezini( "SiteSettings", "SiteURL" ) ) )}
{/set-block}
