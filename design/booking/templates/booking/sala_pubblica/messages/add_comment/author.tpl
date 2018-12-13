{set-block scope=root variable=email_subject}Vostra richiesta di prenotazione n. {$object.id} {$object.data_map.sala.content.name}{/set-block}
{set-block scope=root variable=content_type}text/html{/set-block}
{set-block scope=root variable=email_body}
    Buongiorno {$object.owner.name|wash()},<br/>

    con la presente comunicazione ti informiamo che è stato aggiunto un commento alla tua richiesta di prenotazione.<br/>

    Puoi controllare l'andamento della pratica visitando l'indirizzo <a href="{concat( 'openpa_booking/view/sala_pubblica/', $object.id )|ezurl(no, full)}">{concat( 'openpa_booking/view/sala_pubblica/', $object.id )|ezurl(no, full)}</a><br/>
    L'elenco completo di tutte le tue richieste di prenotazione è visitabile all'indirizzo  <a href="{'openpa_booking/view/sala_pubblica/'|ezurl(no, full)}">{'openpa_booking/view/sala_pubblica/'|ezurl(no, full)}</a><br/><br/>

    Cordiali Saluti<br/><br/>
    --<br/>
{"%sitename notification system" |i18n( 'design/standard/notification',,hash( '%sitename', ezini( "SiteSettings", "SiteURL" ) ) )}
{/set-block}
