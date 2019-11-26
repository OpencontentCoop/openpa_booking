
{def $states = booking_states()}

{include uri='design:booking/parts/status-style.tpl'}

{def $pivot_circoscrizione_query = concat("subtree [", booking_root_node().node_id, "] and classes [sala_pubblica] facets [circoscrizione|alpha|100] pivot [facet=>[attr_circoscrizione_s,meta_id_si],mincount=>1]")}
{def $pivot_circoscrizione = api_search($pivot_circoscrizione_query).pivot["attr_circoscrizione_s,meta_id_si"]}
{def $sale_grouped_by_circoscrizione = array()}
{if count($pivot_circoscrizione)|gt(1)}
    {foreach $pivot_circoscrizione as $item}
        {def $circoscrizione = $item.value}
        {def $id_list = array()}
        {foreach $item.pivot as $sub_item}
            {set $id_list = $id_list|append($sub_item.value)}
        {/foreach}
        {if $circoscrizione|ne('')}
            {set $sale_grouped_by_circoscrizione = $sale_grouped_by_circoscrizione|append(hash(
            'name', $circoscrizione,
            'id_list', $id_list
            ))}
        {/if}
        {undef $circoscrizione $id_list}
    {/foreach}
{/if}

{def $pivot_sala_query = concat("subtree [", booking_root_node().node_id, "] and classes [prenotazione_sala] facets [raw[subattr_sala___titolo____s]|alpha|500] pivot [facet=>[subattr_sala___titolo____s, submeta_sala___id____si],mincount=>1]")}
{def $pivot_sala = api_search($pivot_sala_query).pivot["subattr_sala___titolo____s,submeta_sala___id____si"]}
{def $sale = array()}
{if count($pivot_sala)|gt(1)}
    {foreach $pivot_sala as $item}
        {set $sale = $sale|append(hash(
        'name', $item.value,
        'id', $item.pivot[0].value
        ))}
    {/foreach}
{/if}

<div class="content-view-full class-folder booking-datatable">

    <div class="spinner text-center">
        <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
        <span class="sr-only">Loading...</span>
    </div>

    <div class="content-list" style="display: none">
        {def $preselected = array()}
        <ul class="list-inline text-center" style="display:none">
            {foreach $states as $state}
                <li class="{$state.identifier} state_filter {if and($preselected|count|gt(0),$preselected|contains($state.identifier))}active{/if}" data-state="{$state.id}">
                    <a href="#" class="label label-{$state.identifier}">
                        {$state.current_translation.name|wash()}
                    </a>
                </li>
            {/foreach}
        </ul>

        <div class="row" style="margin-bottom: 20px">
            <div class="col-sm-2">
                <label for="idFilter" style="display: block">Cerca per ID:</label>
                <input class="form-control" value="" type="text" id="idFilter">
            </div>
            {if count($sale)|gt(1)}
                <div class="col-sm-3">
                    <label for="salaFilter" style="display: block">Sala:</label>
                    <select class="form-control" id="salaFilter" data-placeholder=" ">
                        <option></option>
                        {foreach $sale as $item}
                            <option value="{$item.id|wash()}">{$item.name|trim()|wash()}</option>
                        {/foreach}
                    </select>
                </div>
            {/if}
            {if count($sale_grouped_by_circoscrizione)|gt(1)}
                <div class="col-sm-3">
                    <label for="circoscrizioneFilter" style="display: block">Circoscrizione:</label>
                    <select class="form-control" id="circoscrizioneFilter" data-placeholder=" ">
                        <option></option>
                        {foreach $sale_grouped_by_circoscrizione as $item}
                            <option value="{$item.id_list|implode(',')}">{$item.name|trim()|wash()}</option>
                        {/foreach}
                    </select>
                </div>
            {/if}
            <div class="col-sm-4">
                <label for="stateFilter" style="display: block">Stato:</label>
                <select class="list-inline text-center form-control" id="stateFilter" multiple="multiple" data-placeholder=" ">
                    {foreach $states as $state}
                        <option value="{$state.id}" {if and($preselected|count|gt(0),$preselected|contains($state.identifier))}selected="selected"{/if} data-state="{$state.id}">
                            {$state.current_translation.name|wash()}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="row" style="margin-bottom: 30px">
            <div class="col-sm-4">
                <label for="bookingFilter" style="display: block">Periodo di prenotazione:</label>
                <div class="input-group input-daterange" id="bookingFilter">
                    <input type="text" class="form-control" value="" id="bookingFilterFrom">
                    <div class="input-group-addon">-</div>
                    <input type="text" class="form-control" value="" id="bookingFilterTo">
                </div>
            </div>
            <div class="col-sm-4">
                <label for="publishedFilter" style="display: block">Periodo di creazione:</label>
                <div class="input-group input-daterange" id="publishedFilter">
                    <input type="text" class="form-control" value="" id="publishedFilterFrom">
                    <div class="input-group-addon">-</div>
                    <input type="text" class="form-control" value="" id="publishedFilterTo">
                </div>
            </div>
            <div class="col-sm-4">
                <label class="checkbox" style="margin-top: 30px;">
                    <input type="checkbox" value="1" id="subRequestFilter" /> Mostra anche le prenotazioni multiple
                </label>
            </div>
        </div>

        <hr />

        <div id="table">
            <div class="content-data"></div>
        </div>
    </div>


</div>