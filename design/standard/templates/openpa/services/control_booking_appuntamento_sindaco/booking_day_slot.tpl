<table class="list table">
    <tr class="{$style}">
        <td style="vertical-align: middle">
            <small>{$slot.start|l10n( 'date' )}</small>
        </td>
        {foreach $slot.events as $item}
            <td align="center" style="vertical-align: middle">
                {def $busy = $item.busy}
                {if $busy}
                    {if $busy.can_read}
                        <a href="{concat( "openpa_booking/view/appuntamento_sindaco/", $busy.id)|ezurl(no)}">
                            <strong><small>Prenotato <br />{$item.from||l10n( 'shorttime' )} - {$item.to||l10n( 'shorttime' )}</small></strong>
                        </a>
                    {else}
                        <span style="text-decoration: line-through">
                            <small>{$item.from||l10n( 'shorttime' )} - {$item.to||l10n( 'shorttime' )}</small>
                        </span>
                    {/if}
                {else}
                    <a href="{concat( "openpa_booking/add/appuntamento_sindaco/", $item.referrer_contentobject_id, '?start=', $item.from, '&end=', $item.to)|ezurl(no)}">
                        <small>{$item.from||l10n( 'shorttime' )} - {$item.to||l10n( 'shorttime' )}</small>
                    </a>
                {/if}
            </td>
        {/foreach}
    </tr>
</table>
