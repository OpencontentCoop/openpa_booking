{def $openpa_prenotazione = object_handler( $prenotazione )
     $colors = $openpa_prenotazione.control_booking_appuntamento_sindaco.state_colors}

<tr class="{$style}">
    <td>{$prenotazione.object.id}</td>
    <td>{$prenotazione.object.owner.name|wash()}</td>
    <td><pre style="display: inline; background: {$colors[$openpa_prenotazione.control_booking_appuntamento_sindaco.current_state_code]}; color: #fff">{$openpa_prenotazione.control_booking_appuntamento_sindaco.current_state.current_translation.name}</pre></td>
    <td>
        <a href="{concat( "/openpa_booking/view/appuntamento_sindaco/", $prenotazione.contentobject_id )|ezurl(no)}">
            Da {$openpa_prenotazione.control_booking_appuntamento_sindaco.start_timestamp|l10n(shortdatetime)} a {$openpa_prenotazione.control_booking_appuntamento_sindaco.end_timestamp|l10n(shortdatetime)}</td>
    </a>
    <td>{$prenotazione.object.published|l10n(datetime)}</td>
    <td>{$openpa_prenotazione.control_booking_appuntamento_sindaco.collaboration_item.unread_message_count}</td>
</tr>
{undef $openpa_prenotazione}