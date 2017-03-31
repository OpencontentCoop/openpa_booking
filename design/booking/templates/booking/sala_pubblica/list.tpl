{ezcss_require( array(
    'plugins/chosen.css',
    'dataTables.bootstrap.css'
))}
{ezscript_require(array(
    'ezjsc::jquery',
    'plugins/chosen.jquery.js',
    'moment.min.js',
    'jquery.dataTables.js',
    'dataTables.bootstrap.js',
    'jquery.opendataDataTable.js',
    'jquery.opendataTools.js',
    'openpa_booking_sala_pubblica_list.js'
))}

<script type="text/javascript" language="javascript" class="init">
    $.opendataTools.settings('accessPath', "{'/'|ezurl(no,full)}");
    $.opendataTools.settings('endpoint', {ldelim}
        'search': '{'/opendata/api/content/search/'|ezurl(no,full)}',
        'class': '{'/opendata/api/classes/'|ezurl(no,full)}'
        {rdelim});
</script>

<section class="hgroup">
    <h1>
        Prenotazioni
    </h1>
</section>

{def $states = booking_states()
     $state_colors = booking_state_colors()}

<style>
{literal}
    .chosen-search input, .chosen-container-multi input {
        height: auto !important
    }
    .center-pills {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .nav-pills>li.state_filter{
        opacity: .3;
    }
    .nav-pills>li.active{
        opacity: 1 !important;
    }
{/literal}
{foreach $state_colors as $identifier => $color}
    .nav-pills>li.{$identifier}>a, .nav-pills>li.{$identifier}>a:hover, .nav-pills>li.{$identifier}>a:focus,.label-{$identifier}{ldelim}background-color:{$color};color:#fff{rdelim}
{/foreach}
</style>
<div class="content-view-full class-folder">

    <div class="spinner text-center">
        <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
        <span class="sr-only">Loading...</span>
    </div>

    <div class="content-main" style="display: none">
        {def $preselected = array()}
        <ul class="nav nav-pills center-pills">
        {foreach $states as $state}
            <li class="{$state.identifier} state_filter {if and($preselected|count|gt(0),$preselected|contains($state.identifier))}active{/if}" data-state="{$state.id}">
                <a href="#">
                    {$state.current_translation.name|wash()}
                </a>
            </li>
        {/foreach}
        </ul>

        <div id="table">
            <div class="content-data"></div>
        </div>

    </div>


</div>
