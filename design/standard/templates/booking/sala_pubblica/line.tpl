<p>
  <a href={concat("collaboration/item/full/",$item.id)|ezurl}>

    <pre style="display: inline">{$openpa_object.control_booking_sala_pubblica.current_state.current_translation.name}</pre>

    {$openpa_object.control_booking_sala_pubblica.sala.name|wash()} -
    da {$openpa_object.control_booking_sala_pubblica.start_timestamp|l10n(shortdatetime)} a {$openpa_object.control_booking_sala_pubblica.end_timestamp|l10n(shortdatetime)}
  </a>
</p>