{def $sala = $content_object.data_map.sala.content}

<div class="global-view-full">
    <section class="hgroup">
        <h1 class="text-center">
            Ricalendarizza prenotazione <a href="{concat('openpa_booking/view/sala_pubblica/',$content_object.id)|ezurl(no,full)}" class="label label-default">{$content_object.id}</a>
            {if $parent_object}
            <br/><a href="{concat('openpa_booking/view/sala_pubblica/',$parent_object.id)|ezurl(no,full)}">
                <small>Prenotazione principale <span class="label label-default">{$parent_object.id}</span></small>
            </a>
            {/if}
        </h1>
    </section>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info" style="font-size: 1.3em;">
                <table class="table">
                    <tr>
                        <th>{'Richiesta'|i18n('booking')}</th>
                        <td colspan="2">
                            <a href="{concat('openpa_booking/locations/', $sala.main_node_id)|ezurl(no)}">{$sala.name|wash()}</a>
                        </td>
                    </tr>
                    <tr>
                        <th>{'Data'|i18n('booking')}</th>
                        <th>{'Inizio'|i18n('booking')}</th>
                        <th>{'Termine'|i18n('booking')}</th>
                    </tr>
                    <tr>
                        <td>{$content_object.data_map.from_time.content.timestamp|l10n(date)}</td>
                        <td>{$content_object.data_map.from_time.content.timestamp|l10n(shorttime)}</td>
                        <td>{$content_object.data_map.to_time.content.timestamp|l10n(shorttime)}</td>
                    </tr>
                </table>
            </div>

            <div class="text-center" style="font-size: 1.5em;margin: 40px 0">
                {if is_set($error)}
                    <div class="alert alert-warning">{$error|wash}</div>
                {/if}
                <form method="post" action="{concat('openpa_booking/edit/sala_pubblica/',$content_object.id)|ezurl(no,full)}">
                    Ricalendarizza al giorno <input name="NewDay" type="text" class="date form-control" style="width: 200px;display: inline" value="{$content_object.data_map.from_time.content.timestamp|datetime('custom', '%d-%m-%Y')}">
                    dalle ore <input type="text" name="NewFrom" class="time from-time form-control" style="width: 100px;display: inline" value="{$content_object.data_map.from_time.content.timestamp|l10n(shorttime)}">
                    alle ore <input type="text" name="NewTo" class="time to-time form-control" style="width: 100px;display: inline" value="{$content_object.data_map.to_time.content.timestamp|l10n(shorttime)}">
                    <input type="submit" class="btn btn-success" value="Salva" />
                    <a href="{concat('openpa_booking/view/sala_pubblica/',$content_object.id)|ezurl(no,full)}" class="btn btn-danger">Annulla</a>
                </form>
            </div>

            <div class="panel panel-info">
                <div id="calendar" style="padding: 5px;"></div>
            </div>
        </div>

    </div>

</div>


{ezscript_require( array(
    'ezjsc::jquery',
    'jquery.opendataTools.js',
    'moment-with-locales.min.js',
    'moment-timezone-with-data.js',
    'fullcalendar/fullcalendar.js',
    'fullcalendar/locale/it.js',
    'fullcalendar/locale/de.js',
    'jquery.timepicker.js',
    'bootstrap-datepicker/bootstrap-datepicker.min.js',
    'bootstrap-datepicker/locales/bootstrap-datepicker.it.min.js'
))}
{ezcss_require( array( 'fullcalendar.min.css', 'jquery.timepicker.css', 'bootstrap-datepicker/bootstrap-datepicker.min.css' ) )}

{def $min_time = "07:00:00"
     $max_time = "24:00:00"
     $booking_url = "openpa/data/booking_sala_pubblica/"|ezurl(no)}
{def $current_language=ezini('RegionalSettings', 'Locale')}
{def $moment_language = $current_language|explode('-')[1]|downcase()}

{literal}
    <style>
        .fc-time-grid .fc-slats td {
            height: 33px;
        }
    </style>
<script>
    moment.locale('{/literal}{$moment_language}{literal}');
    moment.tz.setDefault("Europe/Rome");
    $(document).ready(function () {

        $('.time').timepicker({
            'timeFormat': 'H:i',
            step: 30,
            disableTimeRanges: [['00:00','07:00']]
        });

        $('.date').datepicker({
            format: "dd-mm-yyyy",
            startDate: {/literal}"{currentdate()|datetime('custom', '%d-%m-%Y')}"{literal}
        });

        var calendar = $('#calendar');
        var validateAndSetDateTime = function(s, e){
            var notBefore = moment().startOf('day');

            // workaround timezone
            var test = moment();
            test.year(s.year());
            test.month(s.month());
            test.date(s.date());
            test.hours(s.hours());
            test.minutes(s.minutes());

            if (!test.isBefore(notBefore) && s.isSame(e.clone().subtract(1, 'seconds'), 'day')){
                $('.date').datepicker('update', s.format('DD-MM-YYYY'));
                if (s.hasTime()){
                    var fromTime = $('.from-time');
                    var toTime = $('.to-time');
                    var currentFrom = fromTime.timepicker('getTime').getTime() / 1000;
                    var currentTo = toTime.timepicker('getTime').getTime() / 1000;
                    var diff = currentTo - currentFrom;
                    fromTime.val(s.format('HH:mm'));
                    toTime.val(s.add(diff, 'second').format('HH:mm'));

                    return true;
                }
            }

            return false;
        };
        calendar.fullCalendar({
            locale: "{/literal}{$moment_language}{literal}",
            defaultView: "month",
            allDaySlot: false,
            slotDuration: '00:60:00',
            minTime: "{/literal}{$min_time}{literal}",
            maxTime: "{/literal}{$max_time}{literal}",
            contentHeight: 600,
            defaultDate: new Date({/literal}{$content_object.data_map.from_time.content.timestamp|mul(1000)}{/literal}),
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            loading: function (isLoading) {
                if (isLoading === true) {
                    calendar.css('opacity', '0.5');
                }else {
                    calendar.css('opacity', '1');
                }
            },
            events: {
                url: "{/literal}{$booking_url}{literal}",
                data: function(){
                    var query = [];
                    query['sala'] = {/literal}{$sala.id}{literal};
                    query['current'] = [{/literal}{$content_object.id}{literal}];
                    return query;
                }
            },
            eventClick: function(calEvent, jsEvent, view) {
                return false;
            },
            editable: true,
            eventDurationEditable: false,
            selectable: true,
            selectLongPressDelay: 1,
            eventDrop: function(event, delta, revertFunc) {
                if (event.id !== {/literal}{$content_object.id}{literal}){
                    revertFunc();
                }else{
                    var s = event.start.clone();
                    var e = event.end.clone();
                    if (!validateAndSetDateTime(s, e)){
                        revertFunc();
                    }
                }
            },
            select: function(s, e, jsEvent, view){
                if (!validateAndSetDateTime(s, e)){
                    calendar.fullCalendar('unselect');
                }
            }
        });
    });

</script>
{/literal}