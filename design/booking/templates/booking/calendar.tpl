{ezscript_require( array(
    'ezjsc::jquery',
    'jquery.opendataTools.js',
    'moment-with-locales.min.js',
    'moment-timezone-with-data.js',
    'fullcalendar/fullcalendar.js',
    'fullcalendar/locale/it.js',
    'fullcalendar/locale/de.js',
    'chosen.jquery.js'
))}
{ezcss_require( array( 'fullcalendar.min.css', 'chosen.css' ) )}

<section class="hgroup">
    <h1>Riepilogo delle prenotazioni confermate</h1>
</section>

<div class="form-group">
    <label for="locations" class="sr-only">Filtra per sala</label>
    <select name="locations" id="locations" multiple data-placeholder="Filtra per sala pubblica o attrezzatura">
        {foreach $locations as $location}
            <option value="{$location.node_id}">{$location.class_name|wash()}: {$location.name|wash()}</option>
        {/foreach}
    </select>
</div>

<div class="panel">
    <div class="panel-body" style="position: relative">
        <div id='calendar'></div>
        <div id="spinner" class="spinner text-center" style="display: none;position: absolute;top: 0;width: 100%;background: #fff;height: 100%;z-index: 1;opacity: .7;">
            <i style="position: absolute;top: 50%;" class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div>

<script>
    {def $query = 'openpa/data/booking_sala_pubblica/(approved_calendar)/'|ezurl()}
    {def $current_language=ezini('RegionalSettings', 'Locale')}
    {def $moment_language = $current_language|explode('-')[1]|downcase()}
    moment.locale('{$moment_language}');
    moment.tz.setDefault("Europe/Rome");
    $.opendataTools.settings('accessPath', "{''|ezurl(no,full)}");
    $.opendataTools.settings('language', "{$current_language}");
    $.opendataTools.settings('locale', "{$moment_language}");
    {literal}
    $(document).ready(function () {
        var spinner = $('#spinner');
        var locations = $('#locations').chosen({width:'100%'}).on('change', function (e) {
            calendar.fullCalendar('removeEvents');
            calendar.fullCalendar('render');
            calendar.fullCalendar('refetchEvents');
        });
        var calendar = $('#calendar').fullCalendar({
            loading: function (isLoading) {
                isLoading ? spinner.show() : spinner.hide();
            },
            locale: "{/literal}{$moment_language}{literal}",
            defaultView: "month",
            allDaySlot: false,
            timezone: "Europe/Rome",
            displayEventEnd: true,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            events: {
                url: {/literal}{$query}{literal},
                data: function(){
                    return {'locations': locations.val()};
                }
            },
            eventClick: function(event) {
                if (event.url) {
                    window.open(event.url);
                    return false;
                }
            }
        });
    });
</script>{/literal}