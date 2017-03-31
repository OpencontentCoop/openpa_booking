{def $sala = $content_object.data_map.sala.content}
{include uri='design:booking/sala_pubblica/full/header.tpl'}

<form method="post" action={"collaboration/action/"|ezurl} xmlns="http://www.w3.org/1999/html">
    <input type="hidden" name="Collaboration_OpenpaBookingActionParameters[]" value="" />

    {if $collab_item.data_int3|eq(0)}
        <p class="text-center">
            {if $openpa_object.control_booking_sala_pubblica.has_manual_price}
                Costo euro <input type="text" name="Collaboration_OpenpaBookingActionParameters[manual_price]" value="" />
            {/if}
            <input class="btn btn-success btn-lg" type="submit" name="CollaborationAction_Defer" value="Conferma la disponibilit&agrave; della sala" />
            <input class="btn btn-danger btn-lg" type="submit" name="CollaborationAction_Deny" value="Rifiuta la richiesta" />
        </p>
    {/if}

    {if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(1)}
        <p class="text-center">
            <input class="btn btn-danger btn-lg" type="submit" name="CollaborationAction_Deny" value="Rifiuta la richiesta" />
        </p>
    {/if}

    {if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(2)}
        <p class="text-center">
            <a class="btn btn-info btn-lg" href={concat( "/shop/orderview/", $content_object.data_map.order_id.content)|ezurl}>Vedi i dettagli del pagamento</a>
            <input class="btn btn-success btn-lg" type="submit" name="CollaborationAction_Accept" value="Approva la prenotazione" />
            <input class="btn btn-danger btn-lg" type="submit" name="CollaborationAction_Deny" value="Rifiuta la richiesta" />
        </p>
    {/if}

    <input type="hidden" name="CollaborationActionCustom" value="custom"/>
    <input type="hidden" name="CollaborationTypeIdentifier" value="openpabooking"/>
    <input type="hidden" name="CollaborationItemID" value="{$collab_item.id}"/>

</form>

{if $openpa_object.control_booking_sala_pubblica.current_state_code|ne(4)}
    {def $concurrent_requests = $openpa_object.control_booking_sala_pubblica.concurrent_requests}
    {if $concurrent_requests|count()|gt(0)}
        <p>
            {if $collab_item.data_int3|eq(0)}
                <strong>Attenzione:</strong> confermando la disponibilità della sala per questa prenotazione, automaticamente verranno rifiutate le seguenti richieste:
            {else}
                <strong>Attenzione:</strong> la richiesta &egrave; in conlitto con le seguenti richieste:
            {/if}
        </p>
        <table class="list" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <th>ID</th>
                <th>Richiedente</th>
                <th>Stato richiesta</th>
                <th>Periodo di prenotazione</th>
                <th>Data richiesta</th>
                <th>Messaggi non letti</th>
                <th>Dettaglio pagamento</th>
            </tr>
            {foreach $concurrent_requests as $prenotazione sequence array( bglight,bgdark ) as $style}
                {include name="row_prenotazione" prenotazione=$prenotazione uri="design:booking/sala_pubblica/prenotazione_row.tpl" style=$style}
            {/foreach}
        </table>
    {/if}
{/if}


{include uri='design:booking/sala_pubblica/full/info.tpl'}
