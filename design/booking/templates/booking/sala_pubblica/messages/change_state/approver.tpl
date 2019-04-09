{if $state_after.identifier|eq( 'in_attesa_di_verifica_pagamento' )}
{set-block scope=root variable=email_subject}Richiesta di prenotazione n. {$object.id} {$object.data_map.sala.content.name}{/set-block}
{set-block scope=root variable=content_type}text/html{/set-block}
{set-block scope=root variable=email_body}
    L'utente ha eseguito il pagamento.<br/>
    Come responsabile del servizio, sei invitato a verificare il pagamento all'indirizzo http://{ezini( "SiteSettings", "SiteURL" )}{concat( 'openpa_booking/view/sala_pubblica/', $object.id )|ezurl(no)}<br/>

    Cordiali saluti<br/><br/>

    --<br/>
{"%sitename notification system" |i18n( 'design/standard/notification',,hash( '%sitename', ezini( "SiteSettings", "SiteURL" ) ) )}
{/set-block}
{/if}
