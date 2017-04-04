<div class="form-group">
    <label for="from" class="">{'Seleziona data'|i18n('booking')}</label>
    <input type="text" class="form-control date" name="date" placeholder="{'Data'|i18n('booking')}" value="" />
</div>
<div id="schedule-alert"></div>
<div id="add-schedule"></div>

{include uri='design:booking/parts/tpl-spinner.tpl'}
{include uri='design:booking/parts/tpl-scheduler.tpl'}

{def $stuff_id_string = 'null'
     $stuff_ids = array()}
{if $object.data_map.stuff.has_content}
    {foreach $object.data_map.stuff.content.relation_list as $item}
        {set $stuff_ids = $stuff_ids|append($item.contentobject_id)}
    {/foreach}
    {set $stuff_id_string = concat('[', $stuff_ids|implode('-'), ']')}
{/if}
{def $dateParts = $object.data_map.from_time.content.timestamp|l10n(shortdate)|explode('/')
     $mindate = concat('new Date(', $dateParts[2], ',', $dateParts[1]|sub(1), ',', $dateParts[0]|inc(), ')')}

<script type="text/javascript">
{literal}

    $(document).ready(function () {
        $( ".date" ).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd-mm-yy",
            numberOfMonths: 1,
            minDate: {/literal}{$mindate}{literal}
        });

        var spinner = $($.templates("#tpl-spinner").render({}));

        var getCurrentRequest = function(){
            var currentMoment = moment($('[name="date"]').val(), "DD-MM-YYYY");
            var fromHours = "{/literal}{$object.data_map.from_time.content.timestamp|l10n(shorttime)|explode(':')[0]}{literal}";
            var toHours = "{/literal}{$object.data_map.to_time.content.timestamp|l10n(shorttime)|explode(':')[0]}{literal}";
            var fromMoment = currentMoment.clone().set('hour', fromHours);
            var from = fromMoment.format('X');
            var toMoment = currentMoment.clone().set('hour', toHours);
            var to = toMoment.format('X');
            return {
                date: currentMoment.format("DD-MM-YYYY"),
                from_moment: fromMoment,
                to_moment: toMoment,
                date_formatted: currentMoment.format("dddd D MMMM YYYY"),
                from_hours_formatted: fromHours,
                to_hours_formatted: toHours,
                from: parseInt(from),
                to: parseInt(to),
                has_stuff: {/literal}{cond(count(stuff_ids)|gt(0), 'true', 'false')}{literal},
                stuff: [],
                stuff_id_list: {/literal}{$stuff_id_string}{literal},
                numero_posti:  null,
                location: "{/literal}{$object.data_map.sala.content.id}{literal}"
            };
        };

        var scheduler = $('#add-schedule');
        var encodedScheduler = $('textarea.ezcca-prenotazione_sala_scheduler');

        var initEncodedScheduler = function(){
            var currentValue = encodedScheduler.val();
            if (currentValue.length > 0) {
                var items = JSON.parse(currentValue);
                $.each(items, function () {
                    var content = $('<div class="checkbox" id="'+this.from+'"></div>');
                    content.append(this.content);
                    scheduler.append(content);
                });
            }
        };
        initEncodedScheduler();

        var updateEncodedScheduler = function(){
            var data = [];
            scheduler.find(':checked').each(function(){
                var item = $(this).data();
                item.content = $(this).parents('div.checkbox').html();
                data.push(item);
            });
            encodedScheduler.val(JSON.stringify(data));
        };

        var sortScheduler = function(){
            scheduler.find('div.checkbox').sort(function(a, b) {
                return parseInt(a.id) - parseInt(b.id);
            }).each(function() {
                var elem = $(this);
                elem.remove();
                $(elem).appendTo(scheduler);
            });
        };

        $('#schedule-alert').opendataSearchView({
            query: '',
            onInit: function (view) {
                $('[name="date"]').on('change', function(e){
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
                var to = request.to_moment.format('DD-MM-YYYY*HH:mm');
                return "from="+from+"&to="+to+"&stuff="+request.stuff_id_list+"&numero_posti="+request.numero_posti+"&location="+request.location+"&";
            },
            onBeforeSearch: function (query, view) {
                view.container.html(spinner);
            },
            onLoadResults: function (response, query, appendResults, view) {
                view.container.html('');
                var currentRequest = getCurrentRequest();
                var template = $.templates("#tpl-schedule");
                $.views.helpers($.opendataTools.helpers);
                if (response.features.length > 0){
                    $.each(response.features, function () {
                        var location = this.properties.content;
                        location.currentRequest = currentRequest;
                        if(location.stuff_available && location.location_available) {
                            if (scheduler.find('[data-from="' + currentRequest.from_moment + '"]').length == 0) {
                                var output = $(template.render(location));
                                scheduler.append(output);
                                sortScheduler();
                                scheduler.find('input').on('change', function (e) {
                                    updateEncodedScheduler();
                                });
                                updateEncodedScheduler();
                            }
                        }else{
                            view.container.html($.templates("#tpl-empty"));
                        }
                    });
                }else{
                    view.container.html($.templates("#tpl-empty"));
                }
            },
            onLoadErrors: function (errorCode, errorMessage, jqXHR, view) {
                view.container.html('<div class="alert alert-danger">' + errorMessage + '</div>')
            }
        }).data('opendataSearchView').init();
    });

{/literal}
</script>
