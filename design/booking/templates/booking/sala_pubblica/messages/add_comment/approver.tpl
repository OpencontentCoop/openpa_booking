{set-block scope=root variable=email_subject}Richiesta di prenotazione n. {$object.id} {$object.data_map.sala.content.name}{/set-block}
{set-block scope=root variable=content_type}text/html{/set-block}
{set-block scope=root variable=email_body}
    Si notifica che è stato aggiunto un commento alla richiesta di prenotazione in oggetto: http://{ezini( "SiteSettings", "SiteURL" )}{concat( 'openpa_booking/view/sala_pubblica/', $object.id )|ezurl(no)}<br/>

    Cordiali saluti<br/><br/>

    --<br/>
{"%sitename notification system" |i18n( 'design/standard/notification',,hash( '%sitename', ezini( "SiteSettings", "SiteURL" ) ) )}
{/set-block}
