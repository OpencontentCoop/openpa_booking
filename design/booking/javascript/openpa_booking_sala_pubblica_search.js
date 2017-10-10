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
        step: 30,
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
                var fromMinutes = fromHoursInput.timepicker('getTime').getMinutes();
                var fromMoment = currentMoment.clone().set('hour', fromHours);
                fromMoment.set('minutes', fromMinutes);
                var from = fromMoment.format('X');
                currentRequest.from_moment = fromMoment;
                currentRequest.from_hours_formatted = fromMoment.format('HH:mm');
                currentRequest.from = parseInt(from);
            }

            if (toHoursInput.timepicker('getTime')) {
                var toHours = toHoursInput.timepicker('getTime').getHours();
                var toMinutes = toHoursInput.timepicker('getTime').getMinutes();
                var toMoment = currentMoment.clone().set('hour', toHours);
                toMoment.set('minutes', toMinutes);
                var to = toMoment.format('X');
                currentRequest.to_moment = toMoment;
                currentRequest.to_hours_formatted = toMoment.format('HH:mm');;
                currentRequest.to = parseInt(to);
            }
        }

        if ($.opendataTools.settings('stuff_sub_workflow_is_enabled')){
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
        }
        return currentRequest;
    };


    var initBookingGui = function() {
        var markerBuilder, loadMarkersInMap;
        try {
            var map = L.map('map').setView([0, 0], 1);
            L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'}).addTo(map);
            var markers = L.markerClusterGroup();
            map.addLayer(markers);
            map.scrollWheelZoom.disable();
            markerBuilder = function (response) {
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
                            var location = e.target.feature.properties.location;
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

            loadMarkersInMap = function (data) {
                markers.clearLayers();
                if (data.features.length > 0) {
                    var geoJsonLayer = markerBuilder(data);
                    markers.addLayer(geoJsonLayer);
                    map.fitBounds(markers.getBounds());
                }
            };

        }catch(err) {
            loadMarkersInMap = function (data) {};
        }


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
                        'value': request.to_moment.format('DD-MM-YYYY*HH:mm')
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
                if (response.locations.length > 0) {
                    var locations = [];
                    $.each(response.locations, function () {
                        var location = this;
                        location.currentRequest = currentRequest;
                        locations.push(location)
                    });
                    appendToContainers(locations);
                    loadMarkersInMap(response.geojson);
                } else {
                    showEmptyInContainers();
                }
            },
            onLoadErrors: function (errorCode, errorMessage, jqXHR, view) {
                view.container.html('<div class="alert alert-danger">' + errorMessage + '</div>')
            }
        }).data('opendataSearchView').init().doSearch();
    };
    if ($.opendataTools.settings('stuff_sub_workflow_is_enabled')) {
        $.opendataTools.findAll('classes [attrezzatura_sala]', function (responseData) {
            $.each(responseData, function () {
                $('[name="stuff"]').append('<option value="' + this.metadata.id + '">' + this.metadata.name['ita-IT'] + '</option>')
            });
            availableAttrezzature = responseData;
            initBookingGui();
        });
    }else {
        initBookingGui();
    }
});
