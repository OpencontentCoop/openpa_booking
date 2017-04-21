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
    'jsrender.js'
))}

{ezcss_require( array(
    'jquery.timepicker.css',
    'leaflet.css',
    'MarkerCluster.css',
    'MarkerCluster.Default.css'
))}

<section class="hgroup noborder">

        <form class="form-inline booking-filters">
                <div class="form-group">
                    <label for="from" class="hide">{'Data'|i18n('booking')}</label>
                    <input type="text" class="form-control date" name="date" placeholder="{'Data'|i18n('booking')}" value="" />
                </div>
                <div class="form-group">
                    <label for="from_hours" class="hide">{'Dalle ore'|i18n('booking')}</label>
                    <input class="form-control time" type="text" name="from_hours" placeholder="{'Dalle ore'|i18n('booking')}" value="" />
                </div>
                <div class="form-group">
                    <label for="to_hours" class="hide">{'Alle ore'|i18n('booking')}</label>
                    <input class="form-control time" type="text" name="to_hours" placeholder="{'Alle ore'|i18n('booking')}" value="" />
                </div>
                {if stuff_sub_workflow_is_enabled()}
                    <div class="form-group">
                        <label for="stuff">{'Attrezzatura richiesta'|i18n('booking')}</label>
                        <select id="stuff" name="stuff" class="form-control" multiple="multiple">
                            <option value="0">{'Nessuna'|i18n('booking')}</option>
                        </select>
                    </div>
                {/if}
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
                <div class="form-group" style="display: none">
                    <label for="destinazione_uso" class="hide">{"Destinazione d'uso"|i18n('booking')}</label>
                    <select id="destinazione_uso" name="destinazione_uso" class="form-control">
                        <option value="">{"Destinazione d'uso"|i18n('booking')}</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success" name="find_availability">
                        <i class="fa fa-search"></i> Cerca disponibilità
                    </button>
                    <button type="submit" class="btn btn-danger" name="reset" style="display: none">
                        <i class="fa fa-times"></i> Annulla ricerca
                    </button>
                </div>
        </form>
        <ul class="nav nav-pills booking-pills">
            <li class="active">
                <a data-toggle="tab" href="#list-panel">
                    <i class="fa fa-th" aria-hidden="true"></i> <span class=""> {'Elenco delle sale disponibili'|i18n('booking')}</span>
                </a>
            </li>

            <li>
                <a data-toggle="tab" href="#geo-panel">
                    <i class="fa fa-map" aria-hidden="true"></i> <span class="">{'Mappa delle sale disponibili'|i18n('booking')}</span>
                </a>
            </li>

            <li>
                <a data-toggle="tab" href="#stuff-panel">
                    <i class="fa fa-th" aria-hidden="true"></i> <span class=""> {'Elenco delle attrezzature disponibili'|i18n('booking')}</span>
                </a>
            </li>
        </ul>
        <div class="tab-content" style="margin-bottom: 40px;" id="booking_items">
            <div id="list-panel" class="tab-pane active">
                <div id="sala_pubblica" class="booking-container row"></div>
            </div>

            <div id="geo-panel" class="tab-pane"><div id="map" style="width: 100%; height: 700px"></div></div>

            <div id="stuff-panel" class="tab-pane">
                <div id="attrezzatura_sala" class="booking-container row"></div>
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

    var spinner = $($.templates("#tpl-spinner").render({}));
    var dateInput = $('[name="date"]');
    var fromHoursInput = $('[name="from_hours"]');
    var toHoursInput = $('[name="to_hours"]');
    var availableAttrezzature = [];

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

    fromHoursInput.on('changeTime', function() {
        var currentDate = $(this).timepicker('getTime');
        currentDate.setHours(currentDate.getHours()+1);
        toHoursInput.timepicker('setTime', currentDate);
    });

    var setInitialCurrentIntervalRequest = function(){
        var tomorrow = moment().add(1,'days').hours(16);
        dateInput.val(tomorrow.format("DD-MM-YYYY"));
        fromHoursInput.val(tomorrow.format("HH")+':00');
        tomorrow.add(2,'hours');
        toHoursInput.val(tomorrow.format("HH")+':00');
    };
    //setInitialCurrentIntervalRequest();


    var getCurrentRequest = function () {

        var currentRequest = {
            date: null,
            from_moment: null,
            to_moment: null,
            date_formatted: null,
            from_hours_formatted: null,
            to_hours_formatted: null,
            from: null,
            to: null,
            has_stuff: false,
            stuff: [],
            stuff_id_list: null,
            numero_posti: $('[name="numero_posti"]').val(),
            destinazione_uso: $('[name="destinazione_uso"]').val()
        };

        if (dateInput.val() != '') {
            var currentMoment = moment(dateInput.val(), "DD-MM-YYYY");
            currentRequest.date = currentMoment;
            currentRequest.date_formatted = currentMoment.format("dddd D MMMM YYYY");

            if (fromHoursInput.timepicker('getTime')) {
                var fromHours = fromHoursInput.timepicker('getTime').getHours();
                var fromMoment = currentMoment.clone().set('hour', fromHours);
                var from = fromMoment.format('X');
                currentRequest.from_moment = fromMoment;
                currentRequest.from_hours_formatted = fromHours;
                currentRequest.from = parseInt(from);
            }

            if (toHoursInput.timepicker('getTime')) {
                var toHours = toHoursInput.timepicker('getTime').getHours();
                var toMoment = currentMoment.clone().set('hour', toHours);
                var to = toMoment.format('X');
                currentRequest.to_moment = toMoment;
                currentRequest.to_hours_formatted = toHours;
                currentRequest.to = parseInt(to);
            }
        }

        {/literal}{if stuff_sub_workflow_is_enabled()}{literal}
        var attrezzatureRichieste = $('[name="stuff"]').val();
        var stuffIdList = [];
        $.each(availableAttrezzature, function () {
            if ($.inArray(this.metadata.id.toString(), attrezzatureRichieste) > -1) {
                currentRequest.stuff.push(this);
                stuffIdList.push(this.metadata.id);
                currentRequest.has_stuff = true;
            }
        });
        currentRequest.stuff_id_list = stuffIdList.join('-');
        {/literal}{/if}{literal}
        return currentRequest;
    };


    var initBookingGui = function() {
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
                    var popupDefault = '<p class="text-center"><i class="fa fa-circle-o-notch fa-spin"></i>' + feature.properties.name + '</p>';
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

        var loadMarkersInMap = function (data) {
            markers.clearLayers();
            if (data.features.length > 0) {
                var geoJsonLayer = markerBuilder(data);
                markers.addLayer(geoJsonLayer);
                map.fitBounds(markers.getBounds());
            }
        };

        var fixupRequestAndDoSearch = function(view) {
            if (dateInput.val() == ''){
                var tomorrow = moment().add(1,'days').hours(16);
                dateInput.val(tomorrow.format("DD-MM-YYYY"));
            }
            var date = moment(dateInput.val(), "DD-MM-YYYY");
            if (fromHoursInput.val() == ''){
                if (toHoursInput.val() == '') {
                    date.hours(16);
                }else{
                    var hours = toHoursInput.timepicker('getTime').getHours() - 2;
                    date.hours(hours);
                }
                fromHoursInput.val(date.format("HH") + ':00');
            }
            date.add(2,'hours');
            if (toHoursInput.val() == '') {
                toHoursInput.val(date.format("HH") + ':00');
            }
            view.doSearch();
        };

        var resetContainers = function(){
            $('.booking-container').html('');
        };

        var appendToContainers = function(locations){
            var template = $.templates("#tpl-prenotazione");
            $.views.helpers($.opendataTools.helpers);
            $('.booking-container').each(function(){
                var container = $(this);
                var classIdentifier = container.attr('id');
                var htmlOutput = [];
                $.each(locations, function(){
                    var location = this;
                    if (location.metadata.classIdentifier == classIdentifier){
                        htmlOutput.push(template.render(location));
                    }
                });
                var itemsCount = htmlOutput.length;
                if(itemsCount > 0) {
                    var itemPerColumn = Math.ceil(htmlOutput.length / 2);
                    var column;
                    for (i = 0; i < itemsCount; i++) {
                        if (i == 0 || i == itemPerColumn) {
                            column = $('<div class="col-sm-6"></div>');
                            container.append(column);
                        }
                        column.append(htmlOutput[i]);
                    }
                }else{
                    container.html($.templates("#tpl-empty"));
                }
            });
        };

        var showSpinnerInContainers = function(){
            $('.booking-container').html(spinner);
        };

        var showEmptyInContainers = function(){
            $('.booking-container').append($.templates("#tpl-empty"));
        };


        $('#booking_items').opendataSearchView({
            query: '',
            onInit: function (view) {
                var destinazioni = $.opendataTools.find('classes sala_pubblica facets [destinazione_uso] limit 1', function (data) {
                    if (data.facets.length > 0) {
                        $.each(data.facets[0].data, function (index, value) {
                            $('[name="destinazione_uso"]').append('<option value="' + index + '">' + index + '</option>');
                        });
                        $('[name="destinazione_uso"]').parents('.form-group').show();
                    }
                });
                $.opendataTools.settings('endpoint', {
                    'search': $.opendataTools.settings('endpoint').booking
                });
                $('[name="destinazione_uso"], [name="numero_posti"]').on('change', function (e) {
                    view.doSearch();
                    $('[name="reset"]').show();
                    $('[name="find_availability"]').hide();
                    e.preventDefault();
                });
                $('[name="date"]').on('change', function (e) {
                    fixupRequestAndDoSearch(view);
                    $('[name="reset"]').show();
                    $('[name="find_availability"]').hide();
                    e.preventDefault();
                });
                $('.time').on('changeTime', function (e) {
                    fixupRequestAndDoSearch(view);
                    $('[name="reset"]').show();
                    $('[name="find_availability"]').hide();
                    e.preventDefault();
                });

                $('[name="find_availability"]').on('click', function (e) {
                    fixupRequestAndDoSearch(view);
                    $('[name="reset"]').show();
                    $('[name="find_availability"]').hide();
                    e.preventDefault();
                });

                $('[name="reset"]').on('click', function (e) {
                    dateInput.val('');
                    fromHoursInput.val('');
                    toHoursInput.val('');
                    $('[name="destinazione_uso"], [name="numero_posti"]').val('');
                    view.doSearch();
                    $('[name="reset"]').hide();
                    $('[name="find_availability"]').show();
                    e.preventDefault();
                });
            },
            onBuildQuery: function (queryParts) {
                var requestVars = [];
                var requestString = '';

                var request = getCurrentRequest();
                if (request.from) {
                    requestVars.push({
                        'key': 'from',
                        'value': request.from_moment.format('DD-MM-YYYY*HH:mm')
                    });
                }
                if (request.to) {
                    requestVars.push({
                        'key': 'to',
                        'value': request.to_moment.subtract(1, 'seconds').format('DD-MM-YYYY*HH:mm')
                    });
                }
                if (request.stuff_id_list){
                    requestVars.push({
                        'key': 'stuff_id_list',
                        'value': request.stuff_id_list
                    });
                }
                if (request.destinazione_uso){
                    requestVars.push({
                        'key': 'destinazione_uso',
                        'value': request.destinazione_uso
                    });
                }
                if (request.numero_posti){
                    requestVars.push({
                        'key': 'numero_posti',
                        'value': request.numero_posti
                    });
                }
                $.each(requestVars, function(index, value){
                    requestString += this.key+'='+this.value+'&';
                });
                return requestString;
            },
            onBeforeSearch: function (query, view) {
                showSpinnerInContainers();
            },
            onLoadResults: function (response, query, appendResults, view) {
                resetContainers();
                var currentRequest = getCurrentRequest();
                if (response.contents.length > 0) {
                    var locations = [];
                    $.each(response.contents, function () {
                        var location = this;
                        location.currentRequest = currentRequest;
                        locations.push(location)
                    });
                    appendToContainers(locations);
                    loadMarkersInMap(response.geo);
                } else {
                    showEmptyInContainers();
                }
            },
            onLoadErrors: function (errorCode, errorMessage, jqXHR, view) {
                view.container.html('<div class="alert alert-danger">' + errorMessage + '</div>')
            }
        }).data('opendataSearchView').init().doSearch();
    };
{/literal}{if stuff_sub_workflow_is_enabled()}{literal}
    $.opendataTools.findAll('classes [attrezzatura_sala]', function(responseData){
        $.each(responseData, function(){
            $('[name="stuff"]').append('<option value="'+this.metadata.id+'">'+this.metadata.name['ita-IT']+'</option>')
        });
        availableAttrezzature = responseData;
        initBookingGui();
    });
{/literal}{else}{literal}
    initBookingGui();
{/literal}{/if}{literal}
});
{/literal}
</script>
