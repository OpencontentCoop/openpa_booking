{ezscript_require( array(
    'ezjsc::jquery',
    'ezjsc::jqueryUI',
    'leaflet.js',
    'leaflet.markercluster.js',
    'leaflet.makimarkers.js',
    'jquery.opendataTools.js',
    'moment-with-locales.min.js',
    'moment-timezone-with-data.js',
    'jquery.opendataSearchView.js',
    'jquery.timepicker.js',
    'datepicker-it.js',
    'jsrender.js',
    'openpa_booking_sala_pubblica_search.js'
))}

{ezcss_require( array(
    'jquery.timepicker.css',
    'leaflet.css',
    'MarkerCluster.css',
    'MarkerCluster.Default.css'
))}

{if is_set($views)|not()}
    {def $views = booking_view_list()}
{/if}

{def $default_view = booking_default_view()}
{if and($views|contains($default_view)|not(), $views|count()|gt(0))}
    {set $default_view = $views[0]}
{/if}

{def $view_items = hash(
    'list', hash(
        'id', 'list-panel',
        'name', 'Elenco delle sale disponibili'|i18n('booking'),
        'icon', "fa fa-th"
    ),
    'map', hash(
        'id', 'geo-panel',
        'name', 'Mappa delle sale disponibili'|i18n('booking'),
        'icon', "fa fa-map"
    ),
    'stuff', hash(
        'id', 'stuff-panel',
        'name', 'Elenco delle attrezzature disponibili'|i18n('booking'),
        'icon', "fa fa-th"
    ),
)}

{if is_set($filters)|not()}
    {def $filters = array('destinazione_uso', 'capienza', 'circoscrizione')}
{/if}

<section class="hgroup noborder">

        <form class="form-inline booking-filters">
                    <div class="form-group">
                        <label for="from" class="hide">{'Data'|i18n('booking')}</label>
                        <input type="text" class="form-control date" name="date" placeholder="{'Data'|i18n('booking')}" value="" style="width: 120px" />
                    </div>
                    <div class="form-group">
                        <label for="from_hours" class="hide">{'Dalle ore'|i18n('booking')}</label>
                        <input class="form-control time" type="text" name="from_hours" placeholder="{'Dalle ore'|i18n('booking')}" value="" style="width: 100px" />
                    </div>
                    <div class="form-group">
                        <label for="to_hours" class="hide">{'Alle ore'|i18n('booking')}</label>
                        <input class="form-control time" type="text" name="to_hours" placeholder="{'Alle ore'|i18n('booking')}" value="" style="width: 100px" />
                    </div>
                    {if stuff_sub_workflow_is_enabled()}
                        <div class="form-group">
                            <label for="stuff">{'Attrezzatura richiesta'|i18n('booking')}</label>
                            <select id="stuff" name="stuff" class="form-control" multiple="multiple">
                                <option value="0">{'Nessuna'|i18n('booking')}</option>
                            </select>
                        </div>
                    {/if}
                    {if $filters|contains('numero_posti')}
                        <div class="form-group">
                            <label for="stuff" class="hide">{'Numero di posti'|i18n('booking')}</label>
                            <select id="stuff" name="numero_posti" class="form-control">
                                <option value="">{'Numero di posti'|i18n('booking')}</option>
                                <option value="1">{'Fino a 100'|i18n('booking')}</option>
                                <option value="2">{'Da 100 a 200'|i18n('booking')}</option>
                                <option value="3">{'Da 200 a 400'|i18n('booking')}</option>
                                <option value="4">{'Oltre 400'|i18n('booking')}</option>
                            </select>
                        </div>
                    {/if}
                    {if $filters|contains('capienza')}
                        <div class="form-group" style="display: none">
                            <label for="destinazione_uso" class="hide">{"Capienza"|i18n('booking')}</label>
                            <select id="destinazione_uso" name="capienza" class="form-control" style="max-width: 200px">
                                <option value="">{"Capienza"|i18n('booking')}</option>
                            </select>
                        </div>
                    {/if}
                    {if $filters|contains('destinazione_uso')}
                        <div class="form-group" style="display: none">
                            <label for="destinazione_uso" class="hide">{"Destinazione d'uso"|i18n('booking')}</label>
                            <select id="destinazione_uso" name="destinazione_uso" class="form-control" style="max-width: 200px">
                                <option value="">{"Destinazione d'uso"|i18n('booking')}</option>
                            </select>
                        </div>
                    {/if}
                    {if $filters|contains('circoscrizione')}
                        <div class="form-group" style="display: none">
                            <label for="stuff" class="hide">{'circoscrizione'|i18n('booking')}</label>
                            <select id="stuff" name="circoscrizione" class="form-control" style="max-width: 200px">
                                <option value="">{'Circoscrizione'|i18n('booking')}</option>
                            </select>
                        </div>
                    {/if}
                    <div class="form-group">
                        <button type="submit" class="btn btn-success" name="find_availability">
                            <i class="fa fa-search"></i> Cerca disponibilità
                        </button>
                        <button type="submit" class="btn btn-danger" name="reset" style="display: none">
                            <i class="fa fa-times"></i> Annulla ricerca
                        </button>
                    </div>
        </form>

        {if count($views)|gt(1)}
        <ul class="nav nav-pills booking-pills">

            {if is_set($view_items[$default_view])}
            <li class="active">
                <a data-toggle="tab" href="#{$view_items[$default_view].id}">
                    <i class="{$view_items[$default_view].icon}" aria-hidden="true"></i> <span class=""> {$view_items[$default_view].name}</span>
                </a>
            </li>
            {/if}

            {foreach $views as $view}
                {if $view|eq($default_view)}{skip}{/if}
                <li>
                    <a data-toggle="tab" href="#{$view_items[$view].id}">
                        <i class="{$view_items[$view].icon}" aria-hidden="true"></i> <span class=""> {$view_items[$view].name}</span>
                    </a>
                </li>
            {/foreach}
        </ul>
        {/if}
        <div class="tab-content" style="margin-bottom: 40px;" id="booking_items" data-subtree="{location_node_id()},{stuff_node_id()}">

            {if $views|contains('list')}
                <div id="list-panel" class="tab-pane{if $default_view|eq('list')} active{/if}">
                    <div id="sala_pubblica" data-classes="{location_class_identifiers()|implode(',')}" class="booking-container row"></div>
                </div>
            {/if}

            {if $views|contains('map')}
                <div id="geo-panel" class="tab-pane{if $default_view|eq('map')} active{/if}">
                    <div id="map" style="width: 100%; height: 700px"></div>
                </div>
            {/if}

            {if $views|contains('stuff')}
                <div id="stuff-panel" class="tab-pane{if $default_view|eq('stuff')} active{/if}">
                    <div id="attrezzatura_sala" data-classes="{stuff_class_identifiers()|implode(',')}" class="booking-container row"></div>
                </div>
            {/if}
        </div>
</section>

{include uri='design:booking/parts/tpl-spinner.tpl'}
{include uri='design:booking/parts/tpl-add-booking.tpl'}
{include uri='design:booking/parts/tpl-empty.tpl'}

<script type="text/javascript">
{def $current_language=ezini('RegionalSettings', 'Locale')}
{def $moment_language = $current_language|explode('-')[1]|downcase()}
moment.locale('{$moment_language}');
moment.tz.setDefault("Europe/Rome");
$.opendataTools.settings('endpoint',{ldelim}
    'search': '{'/opendata/api/content/search'|ezurl(no,full)}/',
    'booking': '{'/openpa/data/booking_sala_pubblica/(availability)/search/'|ezurl(no,full)}/'
{rdelim});
$.opendataTools.settings('accessPath', "{''|ezurl(no,full)}");
$.opendataTools.settings('language', "{$current_language}");
$.opendataTools.settings('locale', "{$moment_language}");
$.opendataTools.settings('location_class_identifiers', "{location_class_identifiers()|implode(',')}");
$.opendataTools.settings('stuff_class_identifiers', "{stuff_class_identifiers()|implode(',')}");

$.opendataTools.settings('onError', function(errorCode,errorMessage,jqXHR){ldelim}
    //console.log(errorMessage + ' (error: '+errorCode+')');
    $("#booking_items").html('<div class="alert alert-danger">'+errorMessage+'</div>');
{rdelim});
$.opendataTools.settings('stuff_sub_workflow_is_enabled', {cond(stuff_sub_workflow_is_enabled(), 'true', 'false')});
</script>
