<script id="tpl-empty" type="text/x-jsrender">
<div class="alert alert-danger">Luogo o attrezzatura non disponibile per la data selezionata</div>
</script>

{literal}
<script id="tpl-schedule" type="text/x-jsrender">
<div class="checkbox" id="{{:currentRequest.from_moment}}">
    <label>
        <input type="checkbox"
               data-from="{{:currentRequest.from_moment}}"
               data-to="{{:currentRequest.to_moment}}"
               data-stuff="{{if currentRequest.has_stuff && stuff_available}}{{:currentRequest.stuff_id_list}}{{/if}}"
               {{if !(location_busy_level == 0 || (currentRequest.has_stuff && !stuff_available))}}checked="checked"{{/if}}
        />
        <span class="booking_date">
        {{if location_busy_level == 0}}<i class="fa fa-warning"></i> Metti in coda di prenotazione per {{/if}}
        <span>{{:currentRequest.date_formatted}}</span> <span class="booking_hours">dalle {{:currentRequest.from_hours_formatted}} alle {{:currentRequest.to_hours_formatted}}</span>
        {{if currentRequest.has_stuff && stuff_available}}
        {{else currentRequest.has_stuff && !stuff_available}}
        <i class="fa fa-warning"></i> <b>senza attrezzatura</b>
        {{/if}}
    </label>
    </div>
</script>
{/literal}
