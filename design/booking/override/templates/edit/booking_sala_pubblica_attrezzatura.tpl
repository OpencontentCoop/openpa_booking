{let class_content=$attribute.class_content
     class_list=fetch( class, list, hash( class_filter, $class_content.class_constraint_list ) )
     can_create=true()
     new_object_initial_node_placement=false()
     browse_object_start_node=false()}

{default html_class='full' placeholder=false()}

{if $placeholder}
<label>{$placeholder}</label>
{/if}

{default attribute_base=ContentObjectAttribute}
{let parent_node=cond( and( is_set( $class_content.default_placement.node_id ),
                       $class_content.default_placement.node_id|eq( 0 )|not ),
                       $class_content.default_placement.node_id, 1 )
     nodesList=cond( and( is_set( $class_content.class_constraint_list ), $class_content.class_constraint_list|count|ne( 0 ) ),
                     fetch( content, tree,
                            hash( parent_node_id, $parent_node,
                                  class_filter_type,'include',
                                  class_filter_array, $class_content.class_constraint_list,
                                  sort_by, array( 'priority',true() ),
                                  main_node_only, true() ) ),
                     fetch( content, list,
                            hash( parent_node_id, $parent_node,
                                  sort_by, array( 'priority', true() )
                                 ) )
                    )
}
{def $stuff_id_string = 'null'
     $stuff_ids = array()}
{section var=node loop=$nodesList}
    {set $stuff_ids = $stuff_ids|append($node.contentobject_id)}
    <div class="checkbox">
        <label>
        <input type="checkbox" data-stuff_id="{$node.contentobject_id}" name="{$attribute_base}_data_object_relation_list_{$attribute.id}[{$node.node_id}]" value="{$node.contentobject_id}"
                {if ne( count( $attribute.content.relation_list ), 0)}
                    {foreach $attribute.content.relation_list as $item}
                        {if eq( $item.contentobject_id, $node.contentobject_id )}
                            checked="checked"
                            {break}
                        {/if}
                    {/foreach}
                {/if}
        />
        {$node.name|wash}
        </label>
    </div>
{/section}
<div id="stuff"></div>
{set $stuff_id_string = concat('[', $stuff_ids|implode('-'), ']')}

<script type="text/javascript">
    {literal}

    $(document).ready(function () {

        var getCurrentStuffRequest = function(){
            var currentMoment = moment("{/literal}{$attribute.object.data_map.from_time.content.timestamp|l10n(shortdate)|explode('/')|implode('-')}{/literal}", "DD-MM-YYYY");
            var fromHours = "{/literal}{$attribute.object.data_map.from_time.content.timestamp|l10n(shorttime)|explode(':')[0]}{literal}";
            var toHours = "{/literal}{$attribute.object.data_map.to_time.content.timestamp|l10n(shorttime)|explode(':')[0]}{literal}";
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
                location: "{/literal}{$attribute.object.data_map.sala.content.id}{literal}"
            };
        };

        $('#stuff').opendataSearchView({
            query: '',
            onInit: function (view) {
            },
            onBuildQuery: function(queryParts){
                var request = getCurrentStuffRequest();
                var from = request.from_moment.format('DD-MM-YYYY*HH:mm');
                var to = request.to_moment.format('DD-MM-YYYY*HH:mm');
                return "from="+from+"&to="+to+"&stuff="+request.stuff_id_list+"&numero_posti="+request.numero_posti+"&location="+request.location+"&";
            },
            onBeforeSearch: function (query, view) {
            },
            onLoadResults: function (response, query, appendResults, view) {
                if (response.features.length > 0){
                    $.each(response.features, function () {
                        var location = this.properties.content;
                        $.each(location.stuff_bookings, function(index, value){
                            if (value > 0)
                                $('[data-stuff_id="'+index+'"]').attr('disabled', 'disabled');
                        });
                    });
                }
            },
            onLoadErrors: function (errorCode, errorMessage, jqXHR, view) {
            }
        }).data('opendataSearchView').init().doSearch();
    });

    {/literal}
</script>
