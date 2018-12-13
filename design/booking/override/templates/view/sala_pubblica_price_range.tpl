{def $booking_range_list = booking_range_list($attribute.object)}
{foreach $booking_range_list as $booking_range}
    <p>
            <strong>
            {$booking_range.identifier|wash}:
            
            {if $booking_range.is_free}
                gratuito
            {elseif $booking_range.vat}                  
                {$booking_range.price|l10n( currency )} ({$booking_range.price_without_vat|l10n( currency )} + IVA {$booking_range.vat_percentage}%)
            {else}
                {$booking_range.price|l10n( currency )}
            {/if}
            </strong>
            {if count($booking_range.valid_hours)|gt(0)}
                <br /><small class="text-muted">Valido solo per prenotazioni negli orari compresi tra le {$booking_range.valid_hours|implode(' e le ')}</small>
            {/if}
            <br />
            <small>{$booking_range.description|wash( xhtml )|nl2br}</small>
    </p>
{/foreach}
{undef $booking_range_list}
