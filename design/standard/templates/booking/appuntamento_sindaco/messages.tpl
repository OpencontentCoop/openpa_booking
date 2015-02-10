{* Messages for change_status *}
{if $action|eq( 'change_state' )}




{if $scope|eq( 'author' )} {* Message change_state for author *}

{set-block scope=root variable=email_subject}Vostra richiesta di appuntamento n. {$object.id}{/set-block}
{set-block scope=root variable=content_type}text/html{/set-block}
{set-block scope=root variable=email_body}
Buongiorno {$object.owner.name|wash()},<br/>
con la presente comunicazione ti informiamo che la tua richiesta di approvazione ha acquisito lo stato <strong>{$state_after.current_translation.name|wash()}</strong>.<br/>
Puoi controllare l'andamento della pratica visitando l'indirizzo {concat( 'openpa_booking/view/appuntamento_sindaco/', $object.id )|ezurl(no, full)}<br/>
L'elenco completo di tutte le tue richieste di appuntamento è visitabile all'indirizzo {'openpa_booking/view/appuntamento_sindaco/'|ezurl(no, full)}<br/><br/>

Cordiali Saluti<br/><br/>
--<br/>
{"%sitename notification system" |i18n( 'design/standard/notification',,hash( '%sitename', ezini( "SiteSettings", "SiteURL" ) ) )}
{/set-block}

{elseif $scope|eq( 'messages' )} {* Messages for change_state *}
{set-block scope=root variable=$comment}La richiesta è stata cambiata di stato: da "{$state_before.current_translation.name|wash()}" a "{$state_after.current_translation.name|wash()}"{/set-block}
{/if}


{* Messages for create_approval *}
{elseif $action|eq( 'create_approval' )}


{if $scope|eq( 'author' )} {* Message create_approval for author *}

{set-block scope=root variable=email_subject}Vostra richiesta di appuntamento n. {$object.id}{/set-block}
{set-block scope=root variable=content_type}text/html{/set-block}
{set-block scope=root variable=email_body}
La presente per comunicarti che la richiesta di appuntamento è stata inoltrata al responsabile del servizio.<br/>

Puoi controllare l'andamento della pratica visitando l'indirizzo http://{ezini( "SiteSettings", "SiteURL" )}{concat( 'openpa_booking/view/appuntamento_sindaco/', $object.id )|ezurl(no)}<br/>

{*Se non desideri più ricevere queste segnalazioni, cambia le impostazioni di notifica "Prenotazioni" all'indirizzo:
http://{ezini( "SiteSettings", "SiteURL" )}{concat( "notification/settings/" )|ezurl( no )}<br/>*}

Cordiali saluti<br/><br/>

--<br/>
{"%sitename notification system" |i18n( 'design/standard/notification',,hash( '%sitename', ezini( "SiteSettings", "SiteURL" ) ) )}
{/set-block}


{elseif $scope|eq( 'approver' )} {* Message create_approval for approver *}

{set-block scope=root variable=email_subject}Richiesta di appuntamento n. {$object.id}{/set-block}
{set-block scope=root variable=content_type}text/html{/set-block}
{set-block scope=root variable=email_body}
E' stata inserita una richiesta di appuntamento in oggetto.<br/>

Come responsabile del servizio, sei invitato a consultare l'indirizzo http://{ezini( "SiteSettings", "SiteURL" )}{concat( 'openpa_booking/view/appuntamento_sindaco/', $object.id )|ezurl(no)}<br/>

{*Se non desideri più ricevere queste segnalazioni, cambia le impostazioni di notifica "Prenotazioni" all'indirizzo:
http://{ezini( "SiteSettings", "SiteURL" )}{concat( "notification/settings/" )|ezurl( no )}<br/>*}

Cordiali saluti<br/><br/>

--<br/>
{"%sitename notification system" |i18n( 'design/standard/notification',,hash( '%sitename', ezini( "SiteSettings", "SiteURL" ) ) )}
{/set-block}


{elseif $scope|eq( 'messages' )} {* Messages for create_approval *}
{set-block scope=root variable=$comment}Inserita richiesta{/set-block}
{/if}

{/if}