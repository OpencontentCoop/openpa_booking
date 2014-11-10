<div class="global-view-full">

  <h1 class="text-center">
    Richiesta di prenotazione "{$sala.name|wash()}" di {$content_object.owner.name|wash()}<br />
    da {$content_object.data_map.from_time.content.timestamp|l10n(shortdatetime)} a {$content_object.data_map.to_time.content.timestamp|l10n(shortdatetime)}
  </h1>

  <h2 class="text-center">
    Lo stato attuale della richiesta &egrave; <pre style="display: inline">{$openpa_object.control_booking_sala_pubblica.current_state.current_translation.name}</pre>
  </h2>

  {if $collab_item.data_int3|eq(0)}
  <p class="text-center">
    <input class="defaultbutton" type="submit" name="CollaborationAction_Defer" value="Approva la richiesta" />
    <input class="defaultbutton" type="submit" name="CollaborationAction_Deny" value="Rifiuta" />
  </p>
  {/if}

  {if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(2)}
  <p class="text-center">
    <a class="defaultbutton" href={concat( "/shop/orderview/", $content_object.data_map.order_id.content)|ezurl}>Dettagli del pagamento</a>
    <input class="defaultbutton" type="submit" name="CollaborationAction_Accept" value="Approva la prenotazione" />
  </p>
  {/if}

  <div class="columns-two">

    <div class="col-1">
      <div class="col-content">
        <h1>Dettagli della richiesta</h1>
        <div class="attributi-base">
          {if is_set($style)}{set $style='col-odd'}{else}{def $style='col-odd'}{/if}
          {foreach $content_object.data_map as $attribute}
            {if $attribute.has_content}
              {if $style|eq( 'col-even' )}{set $style = 'col-odd'}{else}{set $style = 'col-even'}{/if}
              <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                <div class="col-content"><div class="col-content-design">
                  {attribute_view_gui attribute=$attribute}
                </div></div>
              </div>
            {/if}
          {/foreach}
        </div>
      </div>
    </div>

    <div class="col-2">
      <div class="col-content">

        <h1 id="messages">{"Messages"|i18n('design/standard/collaboration/approval')}</h1>
        <div class="last-modified">
        {foreach $participant_list as $item}
          <strong>{$item.name|wash}</strong>
          {foreach $item.items as $partecipant}
            {collaboration_participation_view view=text_linked collaboration_participant=$partecipant}
            {delimiter}, {/delimiter}
          {/foreach}
          {delimiter} - {/delimiter}
        {/foreach}
        </div>

        <p>Puoi utilizzare questo form per comunicare con il richiedente</p>


        <textarea name="Collaboration_OpenpaBookingComment" cols="40" rows="5" class="box"></textarea>
        <input class="defaultbutton" type="submit" name="CollaborationAction_Comment" value="Aggiungi un messaggio" />

        <input type="hidden" name="CollaborationActionCustom" value="custom" />
        <input type="hidden" name="CollaborationTypeIdentifier" value="openpabooking" />
        <input type="hidden" name="CollaborationItemID" value="{$collab_item.id}" />


        {if $message_list}
          <table width="100%" cellspacing="0" cellpadding="4" border="0">
            {foreach $message_list as $item sequence array(bglight,bgdark) as $_style}
              {collaboration_simple_message_view view=element sequence=$_style is_read=$current_participant.last_read|gt($item.modified) item_link=$item collaboration_message=$item.simple_message}
            {/foreach}
          </table>
        {/if}

      </div>
    </div>

  </div>

  <div class="block">

    <h1>Calendario prenotazioni {$sala.name|wash()}</h1>

    {ezscript_require( array( 'fullcalendar/moment.min.js', 'jquery-1.7.1.js', 'fullcalendar/fullcalendar.js', 'fullcalendar/lang/it.js' ) )}
    {ezcss_require( array( 'fullcalendar/fullcalendar.css' ) )}

    {def $min_time = "08:00:00"
    $max_time = "22:00:00"}

    {literal}
    <script>
      $(document).ready(function() {
        $('#calendar').fullCalendar({
          timezone: "local",
          defaultView: "agendaWeek",
          allDaySlot: false,
          minTime: "{/literal}{$min_time}{literal}",
          maxTime: "{/literal}{$max_time}{literal}",
          header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
          },
          events: { url: "{/literal}{concat( 'openpa/data/booking_sala_pubblica'|ezurl(no), '?sala=', $sala.id, '&current=', $content_object.id )}{literal}" }
        });
      });
    </script>
    {/literal}

    <div id='calendar'></div>
  </div>