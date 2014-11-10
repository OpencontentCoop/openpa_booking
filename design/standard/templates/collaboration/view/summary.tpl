{default parent_group_id=0
         current_depth=0
         offset=$view_parameters.offset item_limit=10
         summary_indentation=10}

{let group_tree=fetch("collaboration","group_tree",hash("parent_group_id",$parent_group_id))
     latest_item_count=fetch("collaboration","item_count",hash("parent_group_id",$parent_group_id))}


  <div class="global-view-full">

        <h1 class="context-title">{'Item list'|i18n( 'design/admin/collaboration/view/summary' )}</h1>

          {if $latest_item_count}

            {include uri="design:collaboration/item_list.tpl" item_list=fetch( "collaboration", "item_list", hash( "limit", $item_limit, "offset", $offset ) )}

            {include name=Navigator
                     uri='design:navigator/google.tpl'
                     page_uri="/collaboration/view/summary"
                     item_count=$latest_item_count
                     view_parameters=$view_parameters
                     item_limit=$item_limit}

          {else}
            <div class="block">
              <p>{"No new items to be handled."|i18n('design/admin/collaboration/view/summary')}</p>
            </div>
          {/if}



  </div>


  {*include uri="design:collaboration/group_tree.tpl" group_tree=$group_tree current_depth=$current_depth summary_indentation=$summary_indentation parent_group_id=$parent_group_id*}


{/let}

{/default}


