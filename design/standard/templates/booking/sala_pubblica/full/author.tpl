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
        Lo stato attuale della richiesta &egrave; <pre style="display: inline">{$openpa_object.control_booking_sala_pubblica.current_state.current_translation.name|wash()}</pre>
      </h2>

      {if and( $openpa_object.control_booking_sala_pubblica.current_state.current_translation.description|ne(''), $openpa_object.control_booking_sala_pubblica.current_state.current_translation.description|ne($openpa_object.control_booking_sala_pubblica.current_state.current_translation.name) )}
        <p>{$openpa_object.control_booking_sala_pubblica.current_state.current_translation.description}</p>
      {/if}

      {if $openpa_object.control_booking_sala_pubblica.current_state_code|eq(1)}
      <p class="text-center">
        <a class="defaultbutton" href={concat( "/openpa_booking/view/sala_pubblica/", $content_object.id)|ezurl}>Procedi con il pagamento</a>
      </p>
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
            {if and( $attribute.has_content, $attribute.content|gt(0) )}
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
              {$partecipant.participant.contentobject.name|wash()}
              {delimiter}, {/delimiter}
            {/foreach}
            {delimiter} - {/delimiter}
          {/foreach}
        </div>

        <p>Puoi utilizzare questo form per comunicare con l'amministrazione</p>

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