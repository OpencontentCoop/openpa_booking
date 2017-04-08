
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

{def $query = concat('openpa/data/booking_sala_pubblica?sala=', $node.contentobject_id)|ezurl()}
{if is_set($stuff)}
    {set $query = concat('openpa/data/booking_sala_pubblica?stuff=', $stuff)|ezurl()}
{/if}

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
                defaultView: "month",
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
                events: {url: {/literal}{$query}{literal} },
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


{include uri='design:booking/parts/status-style.tpl'}

<div class="panel">
    <div class="panel-body">
        <div id='calendar'></div>
        <div style="margin-top: 20px">
        {foreach booking_states() as $state}
            <span class="label label-{$state.identifier}">{$state.current_translation.name|wash()}</span>
        {/foreach}
        </div>
    </div>
</div>

{/if}
