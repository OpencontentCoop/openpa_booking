<p>
  <a href={concat("collaboration/item/full/",$item.id)|ezurl}>

    <pre style="display: inline">{$openpa_object.control_booking_appuntamento_sindaco.current_state.current_translation.name}</pre>

    {$openpa_object.control_booking_appuntamento_sindaco.sala.name|wash()} -
    da {$openpa_object.control_booking_appuntamento_sindaco.start_timestamp|l10n(shortdatetime)} a {$openpa_object.control_booking_appuntamento_sindaco.end_timestamp|l10n(shortdatetime)}
  </a>
</p>