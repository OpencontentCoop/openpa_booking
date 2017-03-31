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

{if array(location_node_id(), stuff_node_id())|contains($node.node_id)}

<section class="hgroup noborder">
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-12" style="margin-bottom: 20px">
                    <ul class="nav nav-pills" style="display: none">
                        <li class="active">
                            <a data-toggle="tab" href="#list">
                                <i class="fa fa-th" aria-hidden="true"></i> <span class=""> {'Elenco'|i18n('booking')}</span>
                            </a>
                        </li>

                        <li>
                            <a data-toggle="tab" href="#geo">
                                <i class="fa fa-map" aria-hidden="true"></i> <span class="">{'Sulla mappa'|i18n('booking')}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="tab-content" style="margin-bottom: 40px;">
                <div id="list" class="tab-pane active"><div id="locations" class="row"></div></div>
                <div id="geo" class="tab-pane"><div id="map" style="width: 100%; height: 700px"></div></div>
            </div>
        </div>
    </div>
</section>

{include uri='design:booking/parts/tpl-spinner.tpl'}
{include uri='design:booking/parts/tpl-location.tpl'}
{include uri='design:booking/parts/tpl-stuff.tpl'}
{include uri='design:booking/parts/tpl-empty.tpl'}
{include uri='design:booking/parts/tpl-load-other.tpl'}

<script type="text/javascript">

    {def $current_language=ezini('RegionalSettings', 'Locale')}
    {def $moment_language = $current_language|explode('-')[1]|downcase()}

    moment.locale('{$moment_language}');
    moment.tz.setDefault("Europe/Rome");
    $.opendataTools.settings('endpoint',{ldelim}
        'geo': '{'/opendata/api/geo/search'|ezurl(no,full)}/',
        'search': '{'/opendata/api/content/search'|ezurl(no,full)}/'
    {rdelim});
    $.opendataTools.settings('accessPath', "{''|ezurl(no,full)}");
    $.opendataTools.settings('language', "{$current_language}");
    $.opendataTools.settings('locale', "{$moment_language}");

    {if location_node_id()eq($node.node_id)}

    $.opendataTools.settings('query', "{concat( "classes ['",location_class_identifiers()|implode("','"), "'] limit 12")}");
    $.opendataTools.settings('template', "#tpl-location");
    $.opendataTools.settings('use_map', true);

    {else}

    $.opendataTools.settings('query', "{concat( "classes ['",stuff_class_identifiers()|implode("','"), "'] limit 12")}");
    $.opendataTools.settings('template', "#tpl-stuff");
    $.opendataTools.settings('use_map', false);

    {/if}

    {literal}
    $(document).ready(function () {
        var spinner = $($.templates("#tpl-spinner").render({}));

        var initMap = function () {
            if (document.getElementById('map')) {
                var map = $.opendataTools.initMap(
                        'map',
                        function (response) {
                            if (response.features.length > 0) $('.nav-pills').show();
                            return L.geoJson(response, {
                                pointToLayer: function (feature, latlng) {
                                    var customIcon = L.MakiMarkers.icon({icon: "circle", size: "l"});
                                    return L.marker(latlng, {icon: customIcon});
                                },
                                onEachFeature: function (feature, layer) {
                                    var popupDefault = '<p class="text-center"><i class="fa fa-circle-o-notch fa-spin"></i></p>';
                                    var popup = new L.Popup();
                                    popup.setContent(popupDefault);
                                    layer.on('click', function (e) {

                                        $.opendataTools.findOne('id = ' + e.target.feature.properties.id, function (data) {
                                            var template = $.templates("#tpl-location");
                                            $.views.helpers($.opendataTools.helpers);
                                            var htmlOutput = template.render([data]).replace('col-md-4', '');
                                            popup.setContent(htmlOutput);
                                            popup.update();
                                        })
                                    });
                                    layer.bindPopup(popup);
                                }
                            });
                        }
                );
                map.scrollWheelZoom.disable();

                $("body").on("shown.bs.tab", function () {
                    $.opendataTools.refreshMap();
                });
            }
        };

        var loadMapResults = function (response, query, appendResults, view) {
            if (response.totalCount > 0) {
                $.opendataTools.loadMarkersInMap(query);
            }
        };

        var loadListResults = function (response, query, appendResults, view) {
            spinner.remove();
            if (response.totalCount > 0) {
                var template = $.templates($.opendataTools.settings('template'));
                $.views.helpers($.opendataTools.helpers);

                var htmlOutput = template.render(response.searchHits);
                if (appendResults) view.container.append(htmlOutput);
                else view.container.html(htmlOutput);

                if (response.nextPageQuery) {
                    var loadMore = $($.templates("#tpl-load-other").render({}));
                    loadMore.find('a').bind('click', function (e) {
                        view.appendSearch(response.nextPageQuery);
                        loadMore.remove();
                        view.container.append(spinner);
                        e.preventDefault();
                    });
                    view.container.append(loadMore)
                }
            } else {
                view.container.html(empty);
            }
        };

        $('#locations').opendataSearchView({
            query: $.opendataTools.settings('query'),
            onInit: function (view) {
                if ($.opendataTools.settings('use_map')) initMap();
            },
            onBeforeSearch: function (query, view) {
                view.container.html(spinner);
            },
            onLoadResults: function (response, query, appendResults, view) {
                loadListResults(response, query, appendResults, view);
                if ($.opendataTools.settings('use_map')) loadMapResults(response, query, appendResults, view);
            },
            onLoadErrors: function (errorCode, errorMessage, jqXHR, view) {
                view.container.html('<div class="alert alert-danger">' + errorMessage + '</div>')
            }
        }).data('opendataSearchView').init().doSearch();
    });
    {/literal}
</script>
{/if}
