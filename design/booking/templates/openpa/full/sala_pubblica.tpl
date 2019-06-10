{ezscript_require( array(
    'ezjsc::jquery',
    'ezjsc::jqueryUI',
    'ezjsc::jqueryio',
    'leaflet/leaflet.0.7.2.js',
    'leaflet/leaflet.markercluster.js',
    'leaflet/Leaflet.MakiMarkers.js',
    'leaflet/Control.Geocoder.js'
))}

{ezcss_require( array(
    'leaflet/leaflet.0.7.2.css',
    'leaflet/map.css',
    'leaflet/MarkerCluster.css',
    'leaflet/MarkerCluster.Default.css'
))}

<section class="hgroup">
    <h1>
        {$node.name|wash()}
        {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$node redirect_if_discarded='/openpa_booking/locations' redirect_after_publish=concat('/openpa_booking/locations/',$node.node_id)  }
    </h1>
</section>

<div class="row booking-location">

    <div class="col-md-4">

        {if $openpa.content_contacts.has_content}
            <div class="panel panel-info">
                {if $openpa.content_contacts.show_label}
                    <div class="panel-heading">
                        <h4 class="panel-title"><strong>{$openpa.content_contacts.label}</strong></h4>
                    </div>
                {/if}
                <div class="panel-body">
                    {foreach $openpa.content_contacts.attributes as $openpa_attribute}
                        <div class="row">
                            {if and( $openpa_attribute.full.show_label, $openpa_attribute.full.collapse_label|not() )}
                                <div class="col-md-3"><strong>{$openpa_attribute.label}: </strong></div>
                            {/if}
                            <div class="col-md-{if and( $openpa_attribute.full.show_label, $openpa_attribute.full.collapse_label|not() )}9{else}12{/if}">
                                {if and( $openpa_attribute.full.show_label, $openpa_attribute.full.collapse_label )}
                                    <strong>{$openpa_attribute.label}</strong>
                                {/if}
                                {attribute_view_gui attribute=$openpa_attribute.contentobject_attribute href=cond($openpa_attribute.full.show_link|not, 'no-link', '')}
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        {/if}

        {if or($node|has_attribute('opening_hours'), and( $node|has_attribute('closing_days'), is_set($node|attribute('closing_days').content.cells[0]), $node|attribute('closing_days').content.cells[0]|ne('')))}
            <div class="panel panel-info">
                {if $node|has_attribute('opening_hours')}
                    <div class="panel-heading"><b>{$openpa.opening_hours.label|wash()}</b></div>
                    {def $matrix=$node|attribute('opening_hours').content}
                    <table class="table table-striped" cellspacing="0">

                            {foreach $matrix.columns.sequential as $column}
                            <tr>
                                <th>{$column.name}</th>
                                <td>
                                    {foreach $column.rows as $row}
                                        {if $row}
                                            <div>{'Dalle ore'|i18n('booking')} {$row|explode('-')|implode(' alle ore '|i18n('booking'))}</div>
                                        {/if}
                                    {/foreach}
                                </td>
                            </tr>
                            {/foreach}
                    </table>
                    {undef $matrix}
                {/if}

                {if and( $node|has_attribute('closing_days'), is_set($node|attribute('closing_days').content.cells[0]), $node|attribute('closing_days').content.cells[0]|ne(''))}
                    <div class="panel-heading"><b>{$openpa.closing_days.label|wash()}</b></div>
                    {def $matrix=$node|attribute('closing_days').content}
                    <table class="table table-condensed" cellspacing="0">
                        {section var=Rows loop=$matrix.rows.sequential sequence=array( bglight, bgdark )}
                            <tr class="{$Rows.sequence}">
                                {section var=Columns loop=$Rows.item.columns}
                                    <td>{$Columns.item|wash( xhtml )}</td>
                                {/section}
                            </tr>
                        {/section}
                    </table>
                    {undef $matrix}
                {/if}
            </div>
        {/if}

        <div class="panel panel-info">
            <div class="panel-heading"><b>Info</b></div>
            <div class="panel-body">
                {foreach $openpa.content_detail.attributes as $openpa_attribute}
                    <div class="row">
                        {if and( $openpa_attribute.full.show_label, $openpa_attribute.full.collapse_label|not() )}
                            <div class="col-md-3">
                                <strong>{$openpa_attribute.label}</strong>
                            </div>
                        {/if}
                        <div class="col-md-{if and( $openpa_attribute.full.show_label, $openpa_attribute.full.collapse_label|not() )}9{else}12{/if}">
                            {if and( $openpa_attribute.full.show_label, $openpa_attribute.full.collapse_label )}
                                <strong>{$openpa_attribute.label}</strong>
                            {/if}
                            {attribute_view_gui attribute=$openpa_attribute.contentobject_attribute href='no-link'}
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="lead">
            {if is_set( $openpa.content_main.parts.abstract )}
                {attribute_view_gui attribute=$openpa.content_main.parts.abstract.contentobject_attribute}
            {/if}
        </div>
        {if is_set( $openpa.content_main.parts.image )}
            <div class="text-center">
                {include uri='design:atoms/image.tpl' item=$node alignment='center' image_class=imagefull css_classes="main_image" image_css_class="media-object tr_all_long_hover"}
            </div>
        {/if}

        <section class="hgroup">
            {include uri=$openpa.control_booking_sala_pubblica.template}
        </section>


        {if is_set( $openpa.content_main.parts.full_text )}
            {attribute_view_gui attribute=$openpa.content_main.parts.full_text.contentobject_attribute}
        {/if}
    </div>

</div>

{* https://github.com/blueimp/Gallery vedi atom/gallery.tpl *}
<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls">
    <div class="slides"></div>
    <h3 class="title"><span class="sr-only">gallery</span></h3>
    <a class="prev">‹</a>
    <a class="next">›</a>
    <a class="close">×</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
</div>







