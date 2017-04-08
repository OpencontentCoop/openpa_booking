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

<section class="hgroup noborder">
<div class="row">
    <div class="col-sm-3">
        <aside class="widget" data-filter="q">
            <h4>Cerca disponibilità</h4>
            <div class="form-group">
                <label for="from" class="">{'Data'|i18n('booking')}</label>
                <input type="text" class="form-control date" name="date" placeholder="{'Data'|i18n('booking')}" value="" />
            </div>
            <div class="form-group">
                <label for="from_hours" class="">{'Dalle ore'|i18n('booking')}</label>
                <input class="form-control time" type="text" name="from_hours" placeholder="{'Dalle ore'|i18n('booking')}" value="" />
            </div>
            <div class="form-group">
                <label for="to_hours" class="">{'Alle ore'|i18n('booking')}</label>
                <input class="form-control time" type="text" name="to_hours" placeholder="{'Alle ore'|i18n('booking')}" value="" />
            </div>
            <div class="form-group">
                <label for="stuff">{'Attrezzatura richiesta'|i18n('booking')}</label>
                <select id="stuff" name="stuff" class="form-control" multiple="multiple">
                    <option value="0">{'Nessuna'|i18n('booking')}</option>
                </select>
            </div>
            <div class="form-group">
                <label for="stuff">{'Numero di posti'|i18n('booking')}</label>
                <select id="stuff" name="numero_posti" class="form-control">
                    <option value="0">{'Qualsiasi'|i18n('booking')}</option>
                    <option value="1">{'Fino a 100'|i18n('booking')}</option>
                    <option value="2">{'Da 100 a 200'|i18n('booking')}</option>
                    <option value="3">{'Da 200 a 400'|i18n('booking')}</option>
                    <option value="4">{'Oltre 400'|i18n('booking')}</option>
                </select>
            </div>
        </aside>

    </div>
    <div class="col-sm-9">
        <div class="row">
            <div class="col-sm-12" style="margin-bottom: 20px">
                <ul class="nav nav-pills">
                    <li class="active">
                        <a data-toggle="tab" href="#list">
                            <i class="fa fa-th" aria-hidden="true"></i> <span class=""> {'Elenco delle sale disponibili'|i18n('booking')}</span>
                        </a>
                    </li>

                    <li>
                        <a data-toggle="tab" href="#geo">
                            <i class="fa fa-map" aria-hidden="true"></i> <span class="">{'Sale disponibili sulla mappa'|i18n('booking')}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="tab-content" style="margin-bottom: 40px;">
            <div id="list" class="tab-pane active"><div id="booking_items" class="row"></div></div>
            <div id="geo" class="tab-pane"><div id="map" style="width: 100%; height: 700px"></div></div>
        </div>
    </div>
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

$.opendataTools.settings('onError', function(errorCode,errorMessage,jqXHR){ldelim}
    //console.log(errorMessage + ' (error: '+errorCode+')');
    $("#booking_items").html('<div class="alert alert-danger">'+errorMessage+'</div>');
{rdelim});

{literal}
$(document).ready(function () {
    $( ".date" ).datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd-mm-yy",
        numberOfMonths: 1,
        minDate: '+1'
    });
    $('.time').timepicker({
        'timeFormat': 'H:i',
        step: 60,
        disableTimeRanges: [['00:00','07:00']]
    });

    $('[name="from_hours"]').on('changeTime', function() {
        var currentDate = $(this).timepicker('getTime');
        currentDate.setHours(currentDate.getHours()+1);
        $('[name="to_hours"]').timepicker('setTime', currentDate);
    });

    var setInitialCurrentIntervalRequest = function(){
        var tomorrow = moment().add(1,'days').hours(16);
        $('[name="date"]').val(tomorrow.format("DD-MM-YYYY"));
        $('[name="from_hours"]').val(tomorrow.format("HH")+':00');
        tomorrow.add(2,'hours');
        $('[name="to_hours"]').val(tomorrow.format("HH")+':00');
    };
    setInitialCurrentIntervalRequest();

    var spinner = $($.templates("#tpl-spinner").render({}));

    $.opendataTools.findAll('classes [attrezzatura_sala]', function(Attrezzature){
        $.each(Attrezzature, function(){
            $('[name="stuff"]').append('<option value="'+this.metadata.id+'">'+this.metadata.name['ita-IT']+'</option>')
        });

        var getCurrentRequest = function(){
            var currentMoment = moment($('[name="date"]').val(), "DD-MM-YYYY");
            var fromHours = $('[name="from_hours"]').timepicker('getTime').getHours();
            var toHours = $('[name="to_hours"]').timepicker('getTime').getHours();
            var fromMoment = currentMoment.clone().set('hour', fromHours);
            var from = fromMoment.format('X');
            var toMoment = currentMoment.clone().set('hour', toHours);
            var to = toMoment.format('X');
            var currentRequest = {
                date: currentMoment.format("DD-MM-YYYY"),
                from_moment: fromMoment,
                to_moment: toMoment,
                date_formatted: currentMoment.format("dddd D MMMM YYYY"),
                from_hours_formatted: fromHours,
                to_hours_formatted: toHours,
                from: parseInt(from),
                to: parseInt(to),
                has_stuff: false,
                stuff: [],
                stuff_id_list: null,
                numero_posti:  $('[name="numero_posti"]').val()
            };
            var attrezzatureRichieste = $('[name="stuff"]').val();
            var stuffIdList = [];
            $.each(Attrezzature, function () {
                if ($.inArray(this.metadata.id.toString(), attrezzatureRichieste) > -1) {
                    currentRequest.stuff.push(this);
                    stuffIdList.push(this.metadata.id);
                    currentRequest.has_stuff = true;
                }
            });
            currentRequest.stuff_id_list = stuffIdList.join('-');
            return currentRequest;
        };

        var map = L.map('map').setView([0, 0], 1);
        L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'}).addTo(map);
        var markers = L.markerClusterGroup();
        map.addLayer(markers);
        map.scrollWheelZoom.disable();

        var markerBuilder = function (response) {
            return L.geoJson(response, {
                pointToLayer: function (feature, latlng) {
                    var customIcon = L.MakiMarkers.icon({icon: "circle", size: "l"});
                    return L.marker(latlng, {icon: customIcon});
                },
                onEachFeature: function (feature, layer) {
                    var popupDefault = '<p class="text-center"><i class="fa fa-circle-o-notch fa-spin"></i>'+feature.properties.name+'</p>';
                    var popup = new L.Popup();
                    popup.setContent(popupDefault);
                    layer.on('click', function (e) {
                        var template = $.templates("#tpl-prenotazione");
                        $.views.helpers($.opendataTools.helpers);
                        var location = e.target.feature.properties.content;
                        location.currentRequest = getCurrentRequest();
                        var htmlOutput = template.render(location);
                        popup.setContent(htmlOutput);
                        popup.update();
                    });
                    layer.bindPopup(popup);
                }
            });
        };

        $("body").on("shown.bs.tab", function () {
            map.invalidateSize(false);
            map.fitBounds(markers.getBounds());
        });

        var loadMarkersInMap =  function(data){
            markers.clearLayers();
            if (data.features.length > 0) {
                var geoJsonLayer = markerBuilder(data);
                markers.addLayer(geoJsonLayer);
                map.fitBounds(markers.getBounds());
            }
        };

        $('#booking_items').opendataSearchView({
            query: '',
            onInit: function (view) {
                $.opendataTools.settings('endpoint',{
                    'search': $.opendataTools.settings('endpoint').booking
                });
                $('[name="stuff"], [name="date"], [name="numero_posti"]').on('change', function(e){
                    view.doSearch();
                    e.preventDefault();
                });
                $('.time').on('changeTime', function(e) {
                    view.doSearch();
                    e.preventDefault();
                });
            },
            onBuildQuery: function(queryParts){
                var request = getCurrentRequest();
                var from = request.from_moment.format('DD-MM-YYYY*HH:mm');
                var to = request.to_moment.subtract(1,'seconds').format('DD-MM-YYYY*HH:mm');
                return "from="+from+"&to="+to+"&stuff="+request.stuff_id_list+"&numero_posti="+request.numero_posti+"&";
            },
            onBeforeSearch: function (query, view) {
                view.container.html(spinner);
            },
            onLoadResults: function (response, query, appendResults, view) {
                view.container.html('');
                var currentRequest = getCurrentRequest();
                var template = $.templates("#tpl-prenotazione");
                $.views.helpers($.opendataTools.helpers);
                if (response.features.length > 0) {
                    var htmlOutput = [];
                    $.each(response.features, function () {
                        var location = this.properties.content;
                        location.currentRequest = currentRequest;
                        htmlOutput.push(template.render(location));
                    });
                    var itemsCount = htmlOutput.length;
                    var itemPerColumn = Math.ceil(htmlOutput.length / 2);
                    var column;
                    for (i = 0; i < itemsCount; i++) {
                        if (i == 0 || i == itemPerColumn) {
                            column = $('<div class="col-sm-6"></div>');
                            view.container.append(column);
                        }
                        column.append(htmlOutput[i]);
                    }
                    loadMarkersInMap(response);
                }else{
                    view.container.append($.templates("#tpl-empty"));
                }
            },
            onLoadErrors: function (errorCode, errorMessage, jqXHR, view) {
                view.container.html('<div class="alert alert-danger">' + errorMessage + '</div>')
            }
        }).data('opendataSearchView').init().doSearch();
    });
});
{/literal}
</script>
