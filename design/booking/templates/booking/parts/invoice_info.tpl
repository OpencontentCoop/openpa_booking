{if $invoice_info._status|ne('ready')}
    {if is_set($invoice_info._lastError)}
        <p class="text-danger">
            {"Ci sono stati dei problemi nell'elaborazione della fattura"|i18n('booking')} ({$invoice_info._lastError|wash()})<br />
            {if $invoice_info._status|ne('fatal_error')}
                {'Il sistema proverà a rielaborare la fattura in seguito.'|i18n('booking')}
            {/if}
        </p>
    {else}
        <p class="text-success">
            <em>{'La fattura è in elaborazione'|i18n('booking')} {if is_set($invoice_info._remoteStatus)}({$invoice_info._remoteStatus|wash()}){/if} {'e sarà disponibile a breve'|i18n('booking')}</em>
        </p>
    {/if}
{elseif $invoice_info._status|eq('ready')}
    <a class="btn btn-xl btn-info" href="{concat('openpa_booking/invoice/',$order.id)|ezurl(no)}">{'Scarica il file pdf della fattura'|i18n('booking')}</a>
{/if}