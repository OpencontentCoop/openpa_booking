{def $booking_range_list = booking_range_list(
    $attribute.object.data_map.sala.content,
    $attribute.object.data_map.from_time.content.timestamp,
    $attribute.object.data_map.to_time.content.timestamp
)}
{foreach $booking_range_list as $booking_range}
    {if $booking_range.identifier|eq($attribute.content)}
    <div class="block">
        {$booking_range.label|wash( xhtml )}
        {$booking_range.description|wash( xhtml )|nl2br}
        <br />
        <strong>{'Prezzo:'|i18n('booking')}
        {if $booking_range.is_free}
            {'gratuito'|i18n('booking')}
        {elseif $booking_range.vat}                  
            {$booking_range.price|l10n( currency )} ({$booking_range.price_without_vat|l10n( currency )} + IVA {$booking_range.vat_percentage}%)
        {else}
            {$booking_range.price|l10n( currency )}
        {/if}
        </strong>
        {if count($booking_range.valid_hours)|gt(0)}
            <p class="text-muted">
                {'Valido solo per prenotazioni negli orari compresi tra le %start_hours e le %end_hours'|i18n('booking', '', hash('%start_hours', $booking_range.valid_hours[0], '%end_hours', $booking_range.valid_hours[1]))}
            </p>
        {/if}
    </div>
    {break}
    {/if}
{/foreach}
