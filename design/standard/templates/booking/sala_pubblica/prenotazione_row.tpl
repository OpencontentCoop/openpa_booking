{def $openpa_prenotazione = object_handler( $prenotazione )
     $colors = $openpa_prenotazione.control_booking_sala_pubblica.state_colors}

<tr class="{$style}">
  <td>{$prenotazione.object.id}</td>
  <td>{$prenotazione.object.owner.name|wash()}</td>
  <td><pre style="display: inline; background: {$colors[$openpa_prenotazione.control_booking_sala_pubblica.current_state_code]}; color: #fff">{$openpa_prenotazione.control_booking_sala_pubblica.current_state.current_translation.name}</pre></td>
  <td>
    <a href="{concat( "/openpa_booking/view/sala_pubblica/", $prenotazione.contentobject_id )|ezurl(no)}">
      Da {$openpa_prenotazione.control_booking_sala_pubblica.start_timestamp|l10n(shortdatetime)} a {$openpa_prenotazione.control_booking_sala_pubblica.end_timestamp|l10n(shortdatetime)}</td>
  </a>
  <td>{$prenotazione.object.published|l10n(datetime)}</td>
  <td>{$openpa_prenotazione.control_booking_sala_pubblica.collaboration_item.unread_message_count}</td>
  <td>
    {if $prenotazione.data_map.order_id.content|gt(0)}
      <a href={concat( "/shop/orderview/", $prenotazione.data_map.order_id.content)|ezurl}>Ordine {$prenotazione.data_map.order_id.content}</a>
    {/if}
  </td>
</tr>
{undef $openpa_prenotazione}