{def $sala = $content_object.data_map.sala.content}
{include uri='design:booking/sala_pubblica/full/header.tpl'}

{if $collab_item.data_int3|eq(0)}
    <form method="post" class="form-inline text-center" action="{"collaboration/action/"|ezurl(no)}">
        <input type="hidden" name="Collaboration_OpenpaBookingActionParameters[]" value="" />
        <input class="btn btn-danger btn-lg" type="submit" name="CollaborationAction_Expire" value="Cancella prenotazione" />
        <input type="hidden" name="CollaborationActionCustom" value="custom"/>
        <input type="hidden" name="CollaborationTypeIdentifier" value="openpabooking"/>
        <input type="hidden" name="CollaborationItemID" value="{$collab_item.id}"/>
    </form>
    <br />
{/if}

{if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(1)}
    <div class="text-center lead">    
        <form method="post" class="form-inline text-center" action="{"collaboration/action/"|ezurl(no)}">
            <input type="hidden" name="Collaboration_OpenpaBookingActionParameters[]" value="" />
            <button class="btn btn-success btn-lg" type="submit" name="CollaborationAction_GoToCheckout">
                Procedi con il pagamento di {attribute_view_gui attribute=$content_object.data_map.price}
            </button>
            <input type="hidden" name="CollaborationActionCustom" value="custom"/>
            <input type="hidden" name="CollaborationTypeIdentifier" value="openpabooking"/>
            <input type="hidden" name="CollaborationItemID" value="{$collab_item.id}"/>
        </form>
    </div>
{/if}

{if array(0,1,2,3)|contains($openpa_object.control_booking_sala_pubblica.current_state_code)}
    {def $agenda_link = openpa_agenda_link()}
    {if or($agenda_link, booking_stuff_is_enabled())}
    <div class="row">
        {if booking_stuff_is_enabled()}
        <div class="col-md-6">
            <div class="service_teaser vertical">
                <div class="service_details clearfix" style="min-height: auto">
                    <h1 class="section_header skincolored">Ti serve <b>attrezzatura</b>?</h1>
                    <a class="btn btn-primary btn-lg pull-right" href="{'openpa_booking/stuff'|ezurl(no)}">Guarda l'attrezzatura disponibile</a>
                </div>
            </div>
        </div>
        {/if}
        {if $agenda_link}
        <div class="col-md-6">
            <div class="service_teaser vertical">
                <div class="service_details clearfix" style="min-height: auto">
                    <h1 class="section_header skincolored">Vuoi pubblicizzare il <b>tuo evento</b>?</h1>
                    <a class="btn btn-primary btn-lg pull-right" href="{$agenda_link}">Aggiungi evento in agenda</a>
                </div>
            </div>
        </div>
        {/if}
    </div>
    {/if}
{/if}

{include uri='design:booking/sala_pubblica/full/info.tpl'}

{include uri='design:booking/sala_pubblica/full/detail_and_messages.tpl'}
