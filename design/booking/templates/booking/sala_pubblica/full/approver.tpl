{def $sala = $content_object.data_map.sala.content}
{include uri='design:booking/sala_pubblica/full/header.tpl'}

{if and($collab_item.data_int3|ne(2), $openpa_object.control_booking_sala_pubblica.current_state_code|eq(3))}
<form method="post" class="form-inline text-center" action="{"collaboration/action/"|ezurl(no)}">
    <input type="hidden" name="Collaboration_OpenpaBookingActionParameters[]" value="" />
    <input class="btn btn-danger btn-lg" type="submit" name="CollaborationAction_Expire" value="Cancella prenotazione" />
    <input type="hidden" name="CollaborationActionCustom" value="custom"/>
    <input type="hidden" name="CollaborationTypeIdentifier" value="openpabooking"/>
    <input type="hidden" name="CollaborationItemID" value="{$collab_item.id}"/>
</form>
<br />
{/if}

<form method="post" action={"collaboration/action/"|ezurl} xmlns="http://www.w3.org/1999/html">
    <input type="hidden" name="Collaboration_OpenpaBookingActionParameters[]" value="" />

    {if $collab_item.data_int3|eq(0)}

        {if $openpa_object.control_booking_sala_pubblica.is_stuff_not_pending}

            {if $openpa_object.control_booking_sala_pubblica.has_manual_price}
            <div class="input-group input-group-lg" style="max-width: 500px;margin: 0 auto 20px;">
                <span class="input-group-addon">€</span>
                <input type="text" class="form-control" name="Collaboration_OpenpaBookingActionParameters[manual_price]" value="" placeholder="{'Inserisci il costo'|i18n('booking')}">
                <span class="input-group-addon">.00</span>
            </div>
            {/if}

            <p class="text-center">
                {def $text = 'Conferma la disponibilità della sala'|i18n('booking')
                     $level = 'success'}
                {if $openpa_object.control_booking_sala_pubblica.is_stuff_approved|not()}
                    {set $text = "L'attrezzatura non è disponibile: conferma comuque la disponibilità della sala"|i18n('booking')
                         $level = 'info'}
                {/if}
                <input class="btn btn-{$level} btn-lg" type="submit" name="CollaborationAction_Defer" value="{$text|wash()}" />
                {undef $text $level}

                <input class="btn btn-danger btn-lg" type="submit" name="CollaborationAction_Deny" value="{'Rifiuta la richiesta'|i18n('booking')}" />
            </p>

        {else}

            <p class="lead text-center" style="max-width: 600px;margin: 0 auto 20px;">
                <b>Per poter confermare la disponibilità della sala occorre che siano approvate tutte le richieste di attrezzatura</b>
            </p>

            <p class="text-center">
                <input class="btn btn-danger btn-lg" type="submit" name="CollaborationAction_Deny" value="{'Rifiuta la richiesta'|i18n('booking')}" />
            </p>

        {/if}
    {/if}

    {if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(1)}
        <p class="text-center">
            <input class="btn btn-danger btn-lg" type="submit" name="CollaborationAction_Deny" value="{'Rifiuta la richiesta'|i18n('booking')}" />
        </p>
    {/if}

    {if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(2)}
        <p class="text-center">
            <a class="btn btn-info btn-lg" href={concat( "/shop/orderview/", $content_object.data_map.order_id.content)|ezurl}>{'Vedi i dettagli del pagamento'|i18n('booking')}</a>
            <input class="btn btn-success btn-lg" type="submit" name="CollaborationAction_Accept" value="{'Approva la prenotazione'|i18n('booking')}" />
            <input class="btn btn-danger btn-lg" type="submit" name="CollaborationAction_Deny" value="{'Rifiuta la richiesta'|i18n('booking')}" />
        </p>
    {/if}

    {if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(3)}
        <p class="text-center">
            <input class="btn btn-success btn-lg" type="submit" name="CollaborationAction_ReturnOk" value="{$openpa_object.control_booking_sala_pubblica.states['booking.restituzione_ok'].current_translation.name|wash()}" />
            <input class="btn btn-danger btn-lg" type="submit" name="CollaborationAction_ReturnKo" value="{$openpa_object.control_booking_sala_pubblica.states['booking.restituzione_ko'].current_translation.name|wash()}" />
        </p>
    {/if}

    <input type="hidden" name="CollaborationActionCustom" value="custom"/>
    <input type="hidden" name="CollaborationTypeIdentifier" value="openpabooking"/>
    <input type="hidden" name="CollaborationItemID" value="{$collab_item.id}"/>

</form>


{include uri='design:booking/sala_pubblica/full/info.tpl'}

{include uri='design:booking/sala_pubblica/full/concurrents.tpl'}

{include uri='design:booking/sala_pubblica/full/detail_and_messages.tpl'}
