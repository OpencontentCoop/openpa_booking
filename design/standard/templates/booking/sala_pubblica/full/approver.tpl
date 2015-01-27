<div class="global-view-full">

  <div class="width-layout block" id="virtual-path" style="margin-top: -10px">
    <h2 class="hide">Ti trovi in:</h2>
    <p>
      <a href={'openpa_booking/view/sala_pubblica'|ezurl}>Prenotazioni sale pubbliche</a>
      <span class="path-separator">»</span>
      <span class="path-text"> Prenotazione n. {$content_object.id} </span>	
    </p>
  </div>
  
  
  
  <h1 class="text-center">
    Richiesta di prenotazione "{$sala.name|wash()}" di {$content_object.owner.name|wash()}<br />
    da {$content_object.data_map.from_time.content.timestamp|l10n(shortdatetime)} a {$content_object.data_map.to_time.content.timestamp|l10n(shortdatetime)}
  </h1>

  <div class="col">
    <div class="square-box-soft-gray block col-content-design">

      <h2 class="text-center">
        Lo stato attuale della richiesta &egrave; <pre style="display: inline">{$openpa_object.control_booking_sala_pubblica.current_state.current_translation.name}</pre>
      </h2>
      
      {if ezhttp( 'error', 'get', true() )}
        <div class="alert warning message-warning">
          {ezhttp( 'error', 'get' )|urldecode()}
        </div>
      {/if}
      
      {if $collab_item.data_int3|eq(0)}
        <p class="text-center">          
          {if $openpa_object.control_booking_sala_pubblica.has_manual_price}
            Costo euro <input name="Collaboration_OpenpaBookingActionParameters[manual_price]" value="" />
          {/if}
          <input class="defaultbutton" type="submit" name="CollaborationAction_Defer" value="Conferma la disponibilit&agrave; della sala" />
          <input class="defaultbutton" type="submit" name="CollaborationAction_Deny" value="Rifiuta la richiesta" />
        </p>
      {/if}
      
      {if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(1)}
        <p class="text-center">
          <input class="defaultbutton" type="submit" name="CollaborationAction_Deny" value="Rifiuta la richiesta" />
        </p>
      {/if}
      
      {if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(2)}
      <p class="text-center">
        <a class="button" href={concat( "/shop/orderview/", $content_object.data_map.order_id.content)|ezurl}>Vedi i dettagli del pagamento</a>
        <input class="defaultbutton" type="submit" name="CollaborationAction_Accept" value="Approva la prenotazione" />
        <input class="defaultbutton" type="submit" name="CollaborationAction_Deny" value="Rifiuta la richiesta" />
      </p>
      {/if}
      

      {if $openpa_object.control_booking_sala_pubblica.current_state_code|ne(4)}
        {def $concurrent_requests = $openpa_object.control_booking_sala_pubblica.concurrent_requests}
        {if $concurrent_requests|count()|gt(0)}
          <p>
          {if $collab_item.data_int3|eq(0)}        
            <strong>Attenzione:</strong> confermando la disponibilità della sala per questa prenotazione, automaticamente verranno rifiutate le seguenti richieste:        
          {else}
            <strong>Attenzione:</strong> la richiesta &egrave; in conlitto con le seguenti richieste:
          {/if}
          </p>
          <table class="list" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
              <th>ID</th>
              <th>Richiedente</th>
              <th>Stato richiesta</th>
              <th>Periodo di prenotazione</th>
              <th>Data richiesta</th>
              <th>Messaggi non letti</th>
              <th>Dettaglio pagamento</th>
            </tr>
            {foreach $concurrent_requests as $prenotazione sequence array( bglight,bgdark ) as $style}
              {include name="row_prenotazione" prenotazione=$prenotazione uri="design:booking/sala_pubblica/prenotazione_row.tpl" style=$style}
            {/foreach}
          </table>
        {/if}             
      {/if}             

    </div>
  </div>

  <div class="columns-two">

    <div class="col-1">
      <div class="col-content">
        <h1>Dettagli della richiesta</h1>
        <div class="attributi-base">
          {if is_set($style)}{set $style='col-odd'}{else}{def $style='col-odd'}{/if}

          <div class="{$style} col float-break">
            <div class="col-title"><span class="label">Data della richiesta</span></div>
            <div class="col-content"><div class="col-content-design">
              {$content_object.published|l10n(datetime)}
            </div></div>
          </div>

          {if $style|eq( 'col-even' )}{set $style = 'col-odd'}{else}{set $style = 'col-even'}{/if}
          <div class="{$style} col float-break">
            <div class="col-title"><span class="label">Richiedente</span></div>
            <div class="col-content"><div class="col-content-design">
                {$content_object.owner.name|wash()}
              </div></div>
          </div>

          {foreach $content_object.data_map as $attribute}            
            {if $style|eq( 'col-even' )}{set $style = 'col-odd'}{else}{set $style = 'col-even'}{/if}
            <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
              <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
              <div class="col-content"><div class="col-content-design">
                {attribute_view_gui attribute=$attribute}
              </div></div>
            </div>            
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

  <div class="separator"></div>

  <div class="block">

    <h1>Calendario prenotazioni {$sala.name|wash()}</h1>
    
    {def $colors = $openpa_object.control_booking_sala_pubblica.state_colors}
    <div class="square-box-soft-gray float-break block">
      <small>Legenda:</small>
      <span style="display: inline-block; width: 10px; height: 10px;background: {$colors['current']}"></span> <small>Prenotazione corrente</small>
      <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[3]}"></span> <small>Confermato</small>      
      <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[0]}"></span> <small>In attesa di approvazione</small>
      <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[1]}"></span> <small>In attesa di pagamento</small>
      <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[2]}"></span> <small>In attesa di verifica pagamento</small>
      <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[4]}"></span> <small>Rifiutato</small>
      <span style="display: inline-block; width: 10px; height: 10px;background: {$colors['none']}"></span> <small>Non accessibile</small>
    </div>

    {ezscript_require( array( 'fullcalendar/moment.min.js', 'jquery-1.7.1.js', 'fullcalendar/fullcalendar.js', 'fullcalendar/lang/it.js' ) )}
    {ezcss_require( array( 'fullcalendar/fullcalendar.css' ) )}

    {def $min_time = "00:00:00"
         $max_time = "24:00:00"}

    {literal}
    <script>
      $(document).ready(function() {
        $('#calendar').fullCalendar({
          defaultDate: "{/literal}{$openpa_object.control_booking_sala_pubblica.start_moment}{literal}",
          timezone: "local",
          defaultView: "agendaWeek",
          height: 550,
          slotDuration: '00:60:00',
          allDaySlot: false,          
          minTime: "{/literal}{$min_time}{literal}",
          maxTime: "{/literal}{$max_time}{literal}",
          header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
          },
          eventClick: function(calEvent, jsEvent, view) { window.location.href = "{/literal}{"openpa_booking/view/sala_pubblica"|ezurl(no)}{literal}/"+calEvent.id; },
          events: { url: "{/literal}{concat( 'openpa/data/booking_sala_pubblica'|ezurl(no), '?states=all&sala=', $sala.id, '&current=', $content_object.id )}{literal}" }
        });
      });
    </script>
    {/literal}

    <div id='calendar'></div>
  </div>