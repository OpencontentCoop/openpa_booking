{if ezhttp( 'error', 'get', true() )}
    <div class="alert warning message-warning">
        {ezhttp( 'error', 'get' )|urldecode()|wash()}
    </div>
{/if}

{def $colors = $openpa_object.control_booking_sala_pubblica.state_colors}

<section class="hgroup">
    <h1>
        <span class="label label-default">{$content_object.id}</span> {'Richiesta di prenotazione'|i18n('booking')} "{$sala.name|wash()}"
    </h1>

    {def $current_state_code = $openpa_object.control_booking_sala_pubblica.current_state_code
         $current_state = $openpa_object.control_booking_sala_pubblica.current_state}
    <ul class="breadcrumb pull-right" id="current-post-breadcrumb">
        <li>{$current_state.current_translation.name|wash()}</li>
    </ul>
</section>

{if and($current_state.current_translation.description|ne(''),$current_state.current_translation.name|ne($current_state.current_translation.description))}
    <div class="lead">
        <p>{$current_state.current_translation.description|wash()}</p>
    </div>
{/if}
