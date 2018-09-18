{set-block scope=root variable=email_subject}Vostra richiesta di prenotazione n. {$object.id} {$object.data_map.sala.content.name}{/set-block}
{set-block scope=root variable=content_type}text/html{/set-block}
{set-block scope=root variable=email_body}
    Buongiorno {$object.owner.name|wash()},<br/>

{switch match=$state_after.identifier}

{case match='confermato'}
    con la presente comunicazione ti informiamo che la tua richiesta di prenotazione è <strong>confermata</strong>.<br/>
{/case}

{case match='in_attesa_di_pagamento'}
    con la presente comunicazione confermiamo la disponibilità della sala <strong>in attesa di pagamento</strong>.<br/>
{/case}

{case match='in_attesa_di_verifica_pagamento'}
    con la presente comunicazione ti informiamo che la tua richiesta di prenotazione è <strong>in attesa di verifica pagamento</strong>.<br/>
    La fattura e la modalità di pagamento saranno inviate all'indirizzo mail inserito in fase di registrazione al sito e indicato nella prenotazione. <br/>
{/case}

{case match='rifiutato'}
    con la presente comunicazione ti informiamo che la tua richiesta di prenotazione è stata <strong>rifiutata</strong> per la motivazione indicata nell'iter di richiesta.<br/>
{/case}

{case match='scaduto'}
    con la presente comunicazione ti informiamo che la tua richiesta di prenotazione è stata <strong>cancellata</strong>.<br/>
{/case}

{case match='restituzione_ok'}
    con la presente comunichiamo l'avvenuta restituzione delle chiavi.<br/>
{/case}

{case match='restituzione_ko'}
    con la presente comunichiamo che sono stati segnalati problemi in seguito all'utilizzo della sala.<br/>
{/case}

{case}
    con la presente comunicazione ti informiamo che la tua richiesta di prenotazione ha acquisito lo stato <strong>{$state_after.current_translation.name|wash()}</strong>.<br/>
{/case}

{/switch}

    Puoi controllare l'andamento della pratica visitando l'indirizzo <a href="{concat( 'openpa_booking/view/sala_pubblica/', $object.id )|ezurl(no, full)}">{concat( 'openpa_booking/view/sala_pubblica/', $object.id )|ezurl(no, full)}</a><br/>
    L'elenco completo di tutte le tue richieste di prenotazione è visitabile all'indirizzo  <a href="{'openpa_booking/view/sala_pubblica/'|ezurl(no, full)}">{'openpa_booking/view/sala_pubblica/'|ezurl(no, full)}</a><br/><br/>

    Cordiali Saluti<br/><br/>
    --<br/>
{"%sitename notification system" |i18n( 'design/standard/notification',,hash( '%sitename', ezini( "SiteSettings", "SiteURL" ) ) )}
{/set-block}
