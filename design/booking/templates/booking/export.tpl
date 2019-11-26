{ezcss_require( array(
    'plugins/chosen.css',
    'bootstrap-datepicker/bootstrap-datepicker.min.css',
    'dataTables.bootstrap.css'
))}
{ezscript_require(array(
    'ezjsc::jquery',
    'plugins/chosen.jquery.js',
    'moment-with-locales.min.js',
    'moment-timezone-with-data.js',
    'jquery.dataTables.js',
    'dataTables.bootstrap.js',
    'bootstrap-datepicker/bootstrap-datepicker.min.js',
    'bootstrap-datepicker/locales/bootstrap-datepicker.it.min.js',
    'jquery.opendataDataTable.js',
    'jquery.opendataTools.js',
    'openpa_booking_sala_pubblica_list.js'
))}

<script type="text/javascript" language="javascript" class="init">
    {def $current_language=ezini('RegionalSettings', 'Locale')}
    {def $moment_language = $current_language|explode('-')[1]|downcase()}
    moment.locale('{$moment_language}');
    moment.tz.setDefault("Europe/Rome");
    $.opendataTools.settings('accessPath', "{'/'|ezurl(no,full)}");
    $.opendataTools.settings('mainQuery', 'classes [prenotazione_sala]');
    $.opendataTools.settings('datatableDom', "<'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>");
    $.opendataTools.settings('showLinkColumn', false);
    $.opendataTools.settings('endpoint', {ldelim}
        'search': '{'/opendata/api/content/search/'|ezurl(no,full)}',
        'class': '{'/opendata/api/classes/'|ezurl(no,full)}'
    {rdelim});
    $.opendataTools.settings('stuff_sub_workflow_is_enabled', {cond(stuff_sub_workflow_is_enabled(), 'true', 'false')});
</script>

<section class="hgroup"><h1>Esporta prenotazioni</h1></section>

{include uri='design:booking/parts/booking_datatable.tpl'}

<div class="row">
    <div class="col-xs-12 text-center" style="margin-top: 20px">
        <a id="exportButton" href="#" data-base_href="{'openpa_booking/export/'|ezurl(no)}" class="btn btn-lg btn-success">Esporta risultati in CSV</a>
    </div>
</div>

