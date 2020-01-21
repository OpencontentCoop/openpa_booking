{ezscript_require( array(
    'ezjsc::jquery',
    'ezjsc::jqueryio',
    'ezjsc::jqueryUI',
    'leaflet.js',
    'leaflet.markercluster.js',
    'leaflet.makimarkers.js',
    'jquery.opendataTools.js',
    'moment-with-locales.min.js',
    'moment-timezone-with-data.js',
    'jquery.opendataSearchView.js',
    'jquery.timepicker.js',
    'jsrender.js'
))}

{ezcss_require( array(
    'jquery.timepicker.css',
    'leaflet.css',
    'MarkerCluster.css',
    'MarkerCluster.Default.css'
))}

<section class="hgroup">
    <h1>
        {$node.name|wash()}
        {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$node redirect_if_discarded='/openpa_booking/locations' redirect_after_publish=concat('/openpa_booking/locations/',$node.node_id)  }
    </h1>
</section>

<div class="lead">
    {include uri=$openpa.content_main.template}
</div>

{if location_node_id()|eq($node.node_id)}
    {include name=edit uri='design:booking/home.tpl' views=array('list','map')}
{elseif stuff_node_id()|eq($node.node_id)}
    {if booking_stuff_is_enabled()}
        {include name=edit uri='design:booking/home.tpl' views=array('stuff') filters=array()}
    {else}
        <div class="alert alert-warning">
            {"La prenotazione dell'attrezzatura non è attualmente disponibile"|i18n('booking')}
        </div>
    {/if}
{/if}

