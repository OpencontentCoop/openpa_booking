<div class="global-view-full">
    <h1>Prenotazioni appuntamenti indaco</h1>

    {def $colors = object_handler($node.object).control_booking_appuntamento_sindaco.state_colors}
    <div class="square-box-soft-gray float-break block">
        <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[3]}"></span> <small>Confermato</small>
        <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[0]}"></span> <small>In attesa di approvazione</small>
        <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[4]}"></span> <small>Rifiutato</small>
        <span style="display: inline-block; width: 10px; height: 10px;background: {$colors['none']}"></span> <small>Non accessibile</small>
    </div>

    {def $items = fetch( content, tree, hash( 'parent_node_id', 1, 'class_filter_type', 'include', 'class_filter_array', array( 'sindaco' ) ) )}

    {foreach $items as $item}

        <h2>
            <a href={$item.url_alias|ezurl}>{$item.name|wash()}</a>
        </h2>

        {def $prenotazioni = fetch( content, list, hash( 'parent_node_id', $item.node_id, 'class_filter_type', 'include', 'class_filter_array', array( 'prenotazione_appuntamento_sindaco' ), 'sort_by', array( 'published', false() ) ) )}
        {if $prenotazioni|count()|gt(0)}
            <table class="list" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <th>ID</th>
                    <th>Richiedente</th>
                    <th>Stato richiesta</th>
                    <th>Periodo di prenotazione</th>
                    <th>Data richiesta</th>
                    <th>Messaggi non letti</th>
                </tr>
                {foreach $prenotazioni as $prenotazione sequence array( bglight,bgdark ) as $style}
                    {include name="row_prenotazione" prenotazione=$prenotazione uri="design:booking/appuntamento_sindaco/prenotazione_row.tpl" style=$style}
                {/foreach}
            </table>
        {else}
            <p><em>Nessuna prenotazione</em></p>
        {/if}
        {undef $prenotazioni}

    {/foreach}

</div>