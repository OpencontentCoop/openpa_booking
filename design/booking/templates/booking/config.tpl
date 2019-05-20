{def $locales = fetch( 'content', 'translation_list' )}
{ezscript_require( array( 'ezjsc::jquery', 'jquery.quicksearch.min.js' ) )}
{literal}
<script type="text/javascript">
$(document).ready(function(){  
  $('input.quick_search').quicksearch('table tr');
});  
</script>
{/literal}


<section class="hgroup">
    <h1>{'Settings'|i18n('booking/menu')}</h1>
</section>

<div class="row">
    <div class="col-md-12">
        <ul class="list-unstyled">
            <li>{'Modifica impostazioni generali'|i18n('booking/config')} {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$root redirect_if_discarded='/openpa_booking/config' redirect_after_publish='/openpa_booking/config'}</li>
        </ul>
        

        <hr />

        <div class="row">

            <div class="col-md-3">
              <ul class="nav nav-pills nav-stacked">
                <li role="presentation" {if $current_part|eq('users')}class="active"{/if}><a href="{'openpa_booking/config/users'|ezurl(no)}">{'Utenti'|i18n('booking/config')}</a></li>
				<li role="presentation" {if $current_part|eq('moderators')}class="active"{/if}><a href="{'openpa_booking/config/moderators'|ezurl(no)}">{'Responsabili'|i18n('agenda/config')}</a></li>
			  {if $data|count()|gt(0)}
				{foreach $data as $item}
				  <li role="presentation" {if $current_part|eq(concat('data-',$item.contentobject_id))}class="active"{/if}><a href="{concat('openpa_booking/config/data-',$item.contentobject_id)|ezurl(no)}">{$item.name|wash()}</a></li>
				{/foreach}
			  {/if}
			  	<li><a href="{'shop/orderlist'|ezurl(no)}">{"Order list"|i18n("design/ezwebin/shop/orderlist")}</a></li>
              </ul>
            </div>

            <div class="col-md-9">

			  {if $current_part|eq('moderators')}
              <div class="tab-pane active" id="moderators">
                <form class="form-inline" action="{'openpa_booking/config/moderators'|ezurl(no)}">
                  <div class="form-group">
                    <input type="text" class="form-control" name="s" placeholder="{'Cerca'|i18n('openpa_booking/config')}" value="{$view_parameters.query|wash()}" autofocus>
                  </div>
                  <button type="submit" class="btn btn-success"><i class="fa fa-search"></i></button>
                </form>
                {include name=users_table uri='design:booking/config/moderators_table.tpl' view_parameters=$view_parameters moderator_parent_node_id=$moderators_parent_node_id redirect='/openpa_booking/config/moderators'}
                <div class="pull-right"><a class="btn btn-danger" href="{concat('add/new/user/?parent=',$moderators_parent_node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi moderatore'|i18n('openpa_booking/config')}</a>
                  <form class="form-inline" style="display: inline" action="{'openpa_booking/config/moderators'|ezurl(no)}" method="post">
                    <button class="btn btn-danger" name="AddModeratorLocation" type="submit"><i class="fa fa-plus"></i> {'Aggiungi utente esistente'|i18n('openpa_booking/config')}</button>
                  </form>
              </div>
              </div>
              {/if}

              {if $current_part|eq('users')}
              <div class="tab-pane active" id="users">
                <form class="form-inline" action="{'openpa_booking/config/users'|ezurl(no)}">
                  <div class="form-group">
                    <input type="text" class="form-control" name="s" placeholder="{'Cerca'|i18n('booking/config')}" value="{$view_parameters.query|wash()}" autofocus>
                  </div>
                  <button type="submit" class="btn btn-success"><i class="fa fa-search"></i></button>
                </form>
                {include name=users_table uri='design:booking/config/users_table.tpl' view_parameters=$view_parameters user_parent_node=$user_parent_node}
                <div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/user/',ezini("UserSettings", "DefaultUserPlacement"))|ezurl(no)}">{'Esporta in CSV'|i18n('booking/config')}</a></div>
              </div>
              {/if}

            {def $booking_classes = location_class_identifiers()|merge(stuff_class_identifiers())}
			{if $data|count()|gt(0)}
			  {foreach $data as $item}
				{if $current_part|eq(concat('data-',$item.contentobject_id))}
				{def $add_classes = array()}				
				{if $item|has_attribute('tags')}
					{foreach $item|attribute('tags').content.keyword_string|explode(', ') as $class}
						{set $add_classes = $add_classes|merge(hash($class,$class))}
					{/foreach}					
				{elseif is_set($item.children[0])}
					{set $add_classes = hash($item.children[0].class_identifier, $item.children[0].class_name)}
				{/if}
				<div class="tab-pane active" id="{$item.name|slugize()}">
				  {if $item.children_count|gt(0)}
				  <form action="#">
					<fieldset>
					  <input type="text" name="search" value="" class="quick_search form-control" placeholder="{'Cerca'|i18n('booking/config')}" autofocus />
					</fieldset>
				  </form>
				  <table class="table table-hover">
					{foreach $item.children as $child}
					<tr>
					  <td>
                        {if $booking_classes|contains($child.class_identifier)}	
                        	<h4>{$child.name|wash()} <small>{$child.class_name|wash()}</small></h4>
                        	<li class="list-group-item">
                    			<h5 class="list-group-item-heading">Referenti:</h5>
	                        	<p class="list-group-item-text">	                        	
	                        	{foreach $child.data_map.reservation_manager.content.relation_list as $manager}
		                        	{def $obj = fetch(content,node,hash('node_id', $manager.node_id))}<a href="{$obj.url_alias|ezurl(no)}">{$obj.name|wash()}</a>{undef $obj}{delimiter}, {/delimiter}
	                        	{/foreach}
	                        	</p>
	                        </li>
	                        
	                        {if and(is_set($child.data_map.manual_price), $child.data_map.manual_price.data_int|eq(1))}
	                        	<li class="list-group-item">
                        			<h5 class="list-group-item-heading">{$child|attribute('manual_price').contentclass_attribute_name}</h5>                        			
	                        	</li>
	                        {elseif $child|has_attribute('price_range')}
	                        	<li class="list-group-item">
                        			<h5 class="list-group-item-heading">{$child|attribute('price_range').contentclass_attribute_name}</h5>
                        			<p class="list-group-item-text">{attribute_view_gui attribute=$child|attribute('price_range')}</p>	                        		
	                        	</li>
                        	{elseif $child|has_attribute('price')}
	                        	<li class="list-group-item">
                        			<h5 class="list-group-item-heading">{$child|attribute('price').contentclass_attribute_name}</h5>
                        			<p class="list-group-item-text">{attribute_view_gui attribute=$child|attribute('price')}</p>	                        		
	                        	</li>
	                        {/if}
	                        </ul>
                        {else}
                        	{$child.name|wash()}
                        {/if}
					  </td>
					  <td>              
						{foreach $child.object.available_languages as $language}
						  {foreach $locales as $locale}
							{if $locale.locale_code|eq($language)}
							  <img src="{$locale.locale_code|flag_icon()}" />
							{/if}
						  {/foreach}
						{/foreach}
					  </td>
					  <td width="1">{include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$child redirect_if_discarded=concat('/openpa_booking/config/data-',$item.contentobject_id) redirect_after_publish=concat('/openpa_booking/config/data-',$item.contentobject_id)}</td>
					  <td width="1">{include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$child redirect_if_cancel=concat('/openpa_booking/config/data-',$item.contentobject_id) redirect_after_remove=concat('/openpa_booking/config/data-',$item.contentobject_id)}</td>
					</tr>
					{/foreach}
				  </table>
                  {/if}   
                  {foreach $add_classes as $class_identifier => $class_name}               
					  	<div class="clearfix" style="margin-bottom: 10px">
						  <div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/', $class_identifier, '/',$item.node_id)|ezurl(no)}">{'Esporta in CSV'|i18n('booking/config')} {$class_name|wash()}</a></div>
						  <div class="pull-right"><a class="btn btn-danger"<a href="{concat('add/new/', $class_identifier, '/?parent=',$item.node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi %classname'|i18n('booking/config',, hash( '%classname', $class_name ))}</a></div>
						</div>
				  {/foreach}
                  {undef $add_classes}
				</div>
				{/if}
			  {/foreach}
			{/if} 
			{undef $booking_classes} 

          </div>

      </div>
  </div>
</div>
