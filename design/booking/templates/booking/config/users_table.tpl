{def $locales = fetch( 'content', 'translation_list' )}
{def $item_limit=30}
{def $query = false()}
{if $view_parameters.query}
    {set $query = concat('(*',$view_parameters.query|downcase(),'*) OR ',$view_parameters.query|downcase())}
{/if}

{def $search = fetch( ezfind, search, hash( query, $query, subtree_array, array( $user_parent_node.node_id ), class_id, $user_class.id, limit, $item_limit, offset, $view_parameters.offset, sort_by, hash( 'name', 'asc' ) ) )}

{def $users_count = $search.SearchCount
     $users = $search.SearchResult}

<table class="table table-hover">
    {foreach $users as $user}
        {def $userSetting = $user|user_settings()}
        <tr>
            <td>
                {if $userSetting.is_enabled|not()}<span style="text-decoration: line-through">{/if}
                    {$user.name|wash()} <small><em>{$user.data_map.user_account.content.email|wash()}</em></small>
                    {if $userSetting.is_enabled|not()}</span>{/if}
            </td>
            <td width="1">
                <a href="{concat('social_user/setting/',$user.contentobject_id)|ezurl(no)}"><i class="fa fa-user"></i></a>
            </td>
            <td width="1">{include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$user redirect_if_cancel='/openpa_booking/config/users' redirect_after_remove='/openpa_booking/config/users'}</td>
        </tr>
        {undef $userSetting}
    {/foreach}

</table>

{include name=navigator
         uri='design:navigator/google.tpl'
         page_uri='openpa_booking/config/users'
         item_count=$users_count
         view_parameters=$view_parameters
         item_limit=$item_limit}
