{if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(0)}
    {def $concurrent_requests = $openpa_object.control_booking_sala_pubblica.concurrent_requests}
{else}
    {def $concurrent_requests = $openpa_object.control_booking_sala_pubblica.all_concurrent_requests}
{/if}

{if $concurrent_requests|count()|gt(0)}
    <div class="panel panel-danger">
        <div class="panel-heading">
            {if $collab_item.data_int3|eq(0)}
                <strong>{'Attenzione:'|i18n('booking')}</strong> {'confermando la disponibilità della sala per questa prenotazione, automaticamente verranno rifiutate le seguenti richieste:'|i18n('booking')}
            {else}
                <strong>{'Richieste concorrenti:'|i18n('booking')}</strong>
            {/if}
        </div>
        <table class="table table-striped" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <th>{'ID'|i18n('booking')}</th>
                <th>{'Stato'|i18n('booking')}</th>
                <th>{'Creata'|i18n('booking')} il</th>
                <th>{'Autore'|i18n('booking')}</th>
                <th>{'Periodo'|i18n('booking')}</th>
                <th>{'Luogo'|i18n('booking')}</th>
                <th></th>
            </tr>
            {foreach $concurrent_requests as $prenotazione sequence array( bglight,bgdark ) as $style}
                {include name="row_prenotazione" prenotazione=$prenotazione uri="design:booking/sala_pubblica/prenotazione_row.tpl" style=$style}
            {/foreach}
        </table>
    </div>
{/if}
