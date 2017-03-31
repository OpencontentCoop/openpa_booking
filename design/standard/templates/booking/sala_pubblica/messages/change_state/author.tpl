{set-block scope=root variable=email_subject}Vostra richiesta di prenotazione n. {$object.id} sala {$object.data_map.sala.content.name|wash()}{/set-block}
{set-block scope=root variable=content_type}text/html{/set-block}
{set-block scope=root variable=email_body}
    Buongiorno {$object.owner.name|wash()},<br/>
    con la presente comunicazione ti informiamo che la tua richiesta di approvazione ha acquisito lo stato <strong>{$state_after.current_translation.name|wash()}</strong>.<br/>
    Puoi controllare l'andamento della pratica visitando l'indirizzo {concat( 'openpa_booking/view/sala_pubblica/', $object.id )|ezurl(no, full)}<br/>
    L'elenco completo di tutte le tue richieste di prenotazione è visitabile all'indirizzo {'openpa_booking/view/sala_pubblica/'|ezurl(no, full)}<br/><br/>

    Cordiali Saluti<br/><br/>
    --<br/>
{"%sitename notification system" |i18n( 'design/standard/notification',,hash( '%sitename', ezini( "SiteSettings", "SiteURL" ) ) )}
{/set-block}
