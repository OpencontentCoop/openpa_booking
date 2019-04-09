
{def $colors = object_handler($node.object).control_booking_sala_pubblica.state_colors}

{if is_set( $view_parameters.error )}
    <div class="alert alert-danger message-warning">
        {$view_parameters.error|urldecode|wash()}
    </div>
{/if}

{ezscript_require( array(
    'ezjsc::jquery',
    'jquery.opendataTools.js',
    'moment-with-locales.min.js',
    'moment-timezone-with-data.js',
    'fullcalendar/fullcalendar.js',
    'fullcalendar/locale/it.js',
    'fullcalendar/locale/de.js',
    'jquery.timepicker.js',
    'jsrender.js'
))}
{ezcss_require( array( 'fullcalendar.min.css', 'jquery.timepicker.css' ) )}

{def $query = concat('openpa/data/booking_sala_pubblica?sala=', $node.contentobject_id)|ezurl()}
{if and(stuff_sub_workflow_is_enabled(),is_set($stuff))}
    {set $query = concat('openpa/data/booking_sala_pubblica?stuff=', $stuff)|ezurl()}
{/if}

{def $min_time = "07:00:00"
     $max_time = "24:00:00"
     $booking_url = concat( "openpa_booking/add/sala_pubblica/", $node.contentobject_id )}


{include uri='design:booking/parts/tpl-add-booking.tpl'}

{literal}

    <script id="tpl-select-hours" type="text/x-jsrender">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">
                Seleziona, per il giorno {{: date_formatted}}, l'orario di inizio e di fine di utilizzo
            </h4>
        </div>
        <div class="modal-body">
            {{if display_alert}}
             <div class="alert alert-danger">Orario non disponibile</div>
            {{/if}}
            <form class="form-inline">
                <div class="form-group">
                    <label for="from_hours">{/literal}{'Dalle ore'|i18n('booking')}{literal}</label>
                    <input class="form-control time" type="text" name="from_hours" placeholder="{/literal}{'Dalle ore'|i18n('booking')}{literal}" value="" />
                </div>
                <div class="form-group">
                    <label for="to_hours">{/literal}{'Alle ore'|i18n('booking')}{literal}</label>
                    <input class="form-control time" type="text" name="to_hours" placeholder="{/literal}{'Alle ore'|i18n('booking')}{literal}" value="" />
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
            <button type="button" class="btn btn-success" name="continue">Continua</button>
          </div>
    </script>

    <script id="tpl-start-booking" type="text/x-jsrender">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"></h4>
        </div>
        <div class="modal-body">

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
          </div>
    </script>

    <style>
        .fc-time-grid .fc-slats td {
            height: 33px;
        }
    </style>
    <script>
        {/literal}
        {def $current_language=ezini('RegionalSettings', 'Locale')}
        {def $moment_language = $current_language|explode('-')[1]|downcase()}
        moment.locale('{$moment_language}');
        moment.tz.setDefault("Europe/Rome");
        $.opendataTools.settings('endpoint',{ldelim}
            'search': '{'/openpa/data/booking_sala_pubblica/(availability)/search/'|ezurl(no,full)}/'
        {rdelim});
        $.opendataTools.settings('accessPath', "{''|ezurl(no,full)}");
        $.opendataTools.settings('language', "{$current_language}");
        $.opendataTools.settings('locale', "{$moment_language}");

        var PreventNextBookingHours = {if $node|has_attribute('prevent_next_booking_hours')}{$node|has_attribute('prevent_next_booking_hours').data_int}{else}24{/if};
        {literal}

        $(document).ready(function () {
            var dialog = $('#dialog');

            var buildRequest = function(start,end){
                var requestVars = [];
                var requestString = '';
                requestVars.push({
                    'key': 'from',
                    'value': start.format('DD-MM-YYYY*HH:mm')
                });
                requestVars.push({
                    'key': 'to',
                    'value': end.format('DD-MM-YYYY*HH:mm')
                });
                requestVars.push({
                    'key': 'location',
                    'value': "{/literal}{$node.object.id}{literal}"
                });
                requestVars.push({
                    'key': 'show_unavailable',
                    'value': 1
                });
                $.each(requestVars, function(index, value){
                    requestString += this.key+'='+this.value+'&';
                });
                return requestString;
            };

            var findAvailability = function(start,end,fromSelectHours){
                $.opendataTools.find(buildRequest(start,end), function (response) {
                    var htmlOutput = '';
                    if (response.locations.length > 0) {
                        var templatePrenotazione = $.templates("#tpl-prenotazione");
                        $.views.helpers($.opendataTools.helpers);
                        var location = response.locations[0];
                        location.currentRequest = {
                            date: start,
                            from_moment: start,
                            to_moment: end,
                            date_formatted: start.format("dddd D MMMM YYYY"),
                            from_hours_formatted: start.format("HH:mm"),
                            to_hours_formatted: end.format("HH:mm"),
                            from: moment().year(start.year()).month(start.month()).dates(start.dates()).hours(start.hours()).minutes(start.minutes()).seconds(start.seconds()).format('X'),
                            to: moment().year(end.year()).month(end.month()).dates(end.dates()).hours(end.hours()).minutes(end.minutes()).seconds(end.seconds()).format('X'),
                            has_stuff: false
                        };
                        htmlOutput = templatePrenotazione.render(response.locations);
                        var template = $.templates("#tpl-start-booking");
                        dialog.find('.modal-content').html('');
                        dialog.find('.modal-content').append(
                                template.render(start)
                        );
                        dialog.find('.modal-body').html(htmlOutput);
                        dialog.find('.book-calendar-button').hide();
                        dialog.modal();
                    }else{
                        if (fromSelectHours) {
                            displayHoursForm(start,true);
                        }else{
                            dialog.modal('hide');
                        }
                        calendar.fullCalendar('unselect');
                    }
                });
            };

            var displayHoursForm = function(start,displayAlert){
                var template = $.templates("#tpl-select-hours");
                dialog.find('.modal-content').html('');
                var date_formatted = start.format("dddd D MMMM YYYY");
                dialog.find('.modal-content').append(
                        template.render({
                            date: start,
                            date_formatted: date_formatted,
                            display_alert: displayAlert
                        })
                );
                dialog.find('.time').timepicker({
                    'timeFormat': 'H:i',
                    step: 30,
                    disableTimeRanges: [['00:00','07:00']]
                });
                var fromHoursInput = dialog.find('[name="from_hours"]');
                var toHoursInput = dialog.find('[name="to_hours"]');
                fromHoursInput.on('changeTime', function() {
                    var currentDate = $(this).timepicker('getTime');
                    if (toHoursInput.val() == '') {
                        currentDate.setMinutes(currentDate.getMinutes() + 30);
                        toHoursInput.timepicker('setTime', currentDate);
                    }else{
                        var toDate = toHoursInput.timepicker('getTime');
                        if (currentDate >= toDate){
                            currentDate.setMinutes(currentDate.getMinutes()+30);
                            toHoursInput.timepicker('setTime', currentDate);
                        }
                    }
                });
                toHoursInput.on('changeTime', function() {
                    var currentDate = $(this).timepicker('getTime');
                    if (fromHoursInput.val() == ''){
                        var hours = toHoursInput.timepicker('getTime').getHours() - 2;
                        currentDate.setHours(currentDate.getHours()-1);
                        fromHoursInput.timepicker('setTime', currentDate);
                    }else{
                        var fromDate = fromHoursInput.timepicker('getTime');
                        if (currentDate <= fromDate){
                            currentDate.setHours(fromDate.getHours()+1);
                            toHoursInput.timepicker('setTime', currentDate);
                        }
                    }
                });
                dialog.find('[name="continue"]').on('click', function(e){
                    var fromHours = fromHoursInput.timepicker('getTime').getHours();
                    var fromMinutes = fromHoursInput.timepicker('getTime').getMinutes();
                    var fromMoment = start.clone().set('hour', fromHours);
                    fromMoment.set('minutes', fromMinutes);
                    var toHours = toHoursInput.timepicker('getTime').getHours();
                    var toMinutes = toHoursInput.timepicker('getTime').getMinutes();
                    var toMoment = start.clone().set('hour', toHours);
                    toMoment.set('minutes', toMinutes);
                    findAvailability(fromMoment, toMoment, true);
                    e.preventDefault();
                });
                dialog.modal();
            };

            var calendar = $('#calendar').fullCalendar({
                locale: "{/literal}{$moment_language}{literal}",
                defaultView: "month",
                allDaySlot: false,
                timezone: "Europe/Rome",
                slotDuration: '00:30:00',
                displayEventEnd: true,
                minTime: "{/literal}{$min_time}{literal}",
                maxTime: "{/literal}{$max_time}{literal}",
                contentHeight: 600,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                events: {url: {/literal}{$query}{literal} },
{/literal}{if fetch( 'user', 'current_user' ).is_logged_in}{if and(stuff_sub_workflow_is_enabled(),is_set($stuff))|not()}{literal}
                selectable: true,
                selectLongPressDelay: 1,
                select: function(s, e, jsEvent, view){
                    var notBefore = moment().startOf('day');                                        
                    if (PreventNextBookingHours > 0){
                        notBefore.add(PreventNextBookingHours, 'h');
                    }
                    
                    // workaround timezone
                    var test = moment();
                    test.year(s.year());
                    test.month(s.month());
                    test.date(s.date());
                    test.hours(s.hours());
                    test.minutes(s.minutes());

                    if (!test.isBefore(notBefore) && s.isSame(e.clone().subtract(1, 'seconds'), 'day')){
                        if (s.hasTime()){
                            findAvailability(s,e);
                        }else{
                            displayHoursForm(s);
                        }
                    }else{
                        calendar.fullCalendar('unselect');
                    }
                },
{/literal}{/if}{/if}{literal}
                eventClick: function(event) {
                    if (event.url) {
                        window.open(event.url);
                        return false;
                    }
                }
            });
        });
    </script>
{/literal}


{include uri='design:booking/parts/status-style.tpl'}

<div class="panel">
    <div class="panel-body">
        {if fetch( 'user', 'current_user' ).is_logged_in}
        <div style="margin-bottom: 20px">
            <p class="lead">Per inserire una prenotazione clicca sul calendario. I periodi disponibili sono evidenziati in verde</p>
        </div>
        {else}
            <div style="margin-bottom: 20px">
                <p class="lead">Per inserire una prenotazione <a href="#login">devi iscriverti</a></p>
            </div>
        {/if}
        <div id='calendar'></div>
        {if fetch( 'user', 'current_user' ).is_logged_in}
        <div style="margin-top: 20px">
            <p><strong>Legenda</strong></p>
            {foreach booking_states() as $state}
                {if array('scaduto','rifiutato')|contains($state.identifier)|not()}
                    <p>
                        <span class="label label-{$state.identifier}" style="width: 20px; display: inline-block; height: 20px; vertical-align: middle"></span>
                        Prenotazione in stato {$state.current_translation.name|wash()}
                    </p>
                {/if}
            {/foreach}
            {def $is_manager = false()}
            {foreach $node.data_map.reservation_manager.content.relation_list as $manager}
                {if $manager.contentobject_id|eq(fetch(user,current_user).contentobject_id)}
                    {set $is_manager = true()}
                {/if}
            {/foreach}
            {if $is_manager|not()}
            <p>
                <span class="label label-none" style="width: 20px; display: inline-block; height: 20px; vertical-align: middle"></span>
                Prenotazioni di altri utenti
            </p>
            {/if}
        </div>
        {/if}
    </div>
</div>

<div id="dialog" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="dialogLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        </div>
    </div>
</div>



