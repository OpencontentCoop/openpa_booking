
{if fetch( 'user', 'current_user' ).is_logged_in}

{def $colors = object_handler($node.object).control_booking_sala_pubblica.state_colors}

{if is_set( $view_parameters.error )}
    <div class="alert message-warning">
        {$view_parameters.error|urldecode}
    </div>
{/if}

{def $current_language=ezini('RegionalSettings', 'Locale')}
{def $moment_language = $current_language|explode('-')[1]|downcase()}

{ezscript_require( array(
    'ezjsc::jquery',
    'jquery.opendataTools.js',
    'moment-with-locales.min.js',
    'fullcalendar/fullcalendar.js',
    'fullcalendar/locale/it.js',
    'fullcalendar/locale/de.js'
))}
{ezcss_require( array( 'fullcalendar.min.css' ) )}

{def $min_time = "07:00:00"
     $max_time = "24:00:00"
     $booking_url = concat( "openpa_booking/add/sala_pubblica/", $node.contentobject_id )}

{literal}
    <style>
        .fc-time-grid .fc-slats td {
            height: 33px;
        }
    </style>
    <script>
        $(document).ready(function () {
            $('#calendar').fullCalendar({
                locale: "{/literal}{$moment_language}{literal}",
                defaultView: "agendaWeek",
                allDaySlot: false,
                slotDuration: '00:60:00',
                minTime: "{/literal}{$min_time}{literal}",
                maxTime: "{/literal}{$max_time}{literal}",
                contentHeight: 600,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                events: {url: {/literal}{concat('openpa/data/booking_sala_pubblica?sala=', $node.contentobject_id)|ezurl()}{literal} },
                selectable: false,
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
    <div id='calendar'></div>
{/if}

<ul class="list list-inline">
    <li><span style="display: inline-block; width: 10px; height: 10px;background: {$colors[3]}"></span>
        Confermato
    </li>
    <li>
        <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[0]}"></span>
        In attesa di approvazione
    </li>
    <li>
        <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[1]}"></span>
        In attesa di pagamento
    </li>
    <li>
        <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[2]}"></span>
        In attesa di verifica pagamento
    </li>
    <li>
        <span style="display: inline-block; width: 10px; height: 10px;background: {$colors[4]}"></span>
        Rifiutato
    </li>
    <li>
        <span style="display: inline-block; width: 10px; height: 10px;background: {$colors['none']}"></span>
        Non accessibile
    </li>
</ul>

