{if ezhttp( 'error', 'get', true() )}
    <div class="alert warning message-warning">
        {ezhttp( 'error', 'get' )|urldecode()}
    </div>
{/if}

{def $colors = $openpa_object.control_booking_sala_pubblica.state_colors}

<section class="hgroup">
    <h1>
        <span class="label label-primary">{$content_object.id}</span> Richiesta di prenotazione "{$sala.name|wash()}"
    </h1>

    {def $current_state_code = $openpa_object.control_booking_sala_pubblica.current_state_code
         $current_state = $openpa_object.control_booking_sala_pubblica.current_state}
    <ul class="breadcrumb pull-right" id="current-post-breadcrumb">
        <li>
            <span class="label" style="background: {$colors[$current_state_code]}">{$current_state.current_translation.name|wash()}</span>
        </li>
    </ul>
</section>

{if $openpa_object.control_booking_sala_pubblica.current_state.current_translation.description|ne('')}
    <div class="lead">
        <p>{$openpa_object.control_booking_sala_pubblica.current_state.current_translation.description}</p>
    </div>
{/if}
