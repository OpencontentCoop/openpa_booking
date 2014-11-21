<div class="global-view-full">
  <h1>Prenotazioni sale pubbliche</h1>
  {def $sale_pubbliche = fetch( content, tree, hash( 'parent_node_id', 1, 'class_filter_type', 'include', 'class_filter_array', array( 'sala_pubblica' ) ) )}

  {foreach $sale_pubbliche as $sala}

    <h2>
      <a href={$sala.url_alias|ezurl}>{$sala.name|wash()}</a>
      {*<a class="defaultbutton" href={concat( "openpa_booking/add/sala_pubblica/", $sala.contentobject_id )|ezurl}>Prenota questa sala</a>*}
    </h2>

    {def $prenotazioni = fetch( content, list, hash( 'parent_node_id', $sala.node_id, 'class_filter_type', 'include', 'class_filter_array', array( 'prenotazione_sala' ), 'sort_by', array( 'published', false() ) ) )}
    {if $prenotazioni|count()|gt(0)}
    <table class="list" width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <th>Richiedente</th>
        <th>Stato richiesta</th>
        <th>Periodo di prenotazione</th>
        <th>Data richiesta</th>
        <th>Dettaglio pagamento</th>
      </tr>
      {foreach $prenotazioni as $prenotazione sequence array( bglight,bgdark ) as $style}
        {include name="row_prenotazione" prenotazione=$prenotazione uri="design:booking/sala_pubblica/prenotazione_row.tpl" style=$style}
      {/foreach}
      </table>
    {else}
      <p><em>Nessuna prenotazione</em></p>
    {/if}
    {undef $prenotazioni}

  {/foreach}

</div>