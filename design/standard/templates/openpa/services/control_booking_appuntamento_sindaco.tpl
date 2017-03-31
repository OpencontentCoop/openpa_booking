{if count( $openpa.control_booking_appuntamento_sindaco.time_slots )|gt(0)}

<div class="oggetti-correlati" {if is_set( $view_parameters.error )}id="error"{/if}>
    <div class="border-header border-box box-trans-blue box-allegati-header">
        <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
        <div class="border-ml"><div class="border-mr"><div class="border-mc">
        <div class="border-content">
            <h2>Prenota un appuntamento con il Sindaco</h2>
        </div>
    </div></div></div>
    </div>
    <div class="border-body border-box box-violet box-allegati-content">
    <div class="border-ml"><div class="border-mr"><div class="border-mc">
    <div class="border-content col">
    <div class="col-content"><div class="col-content-design">
        <p>Clicca sull'orario che preferisci per richiedere una prenotazione, ti verr&agrave; inviata un'email di conferma.</p>
        {if $current_user.is_logged_in}

            {def $style='bgdark'}
            {foreach $openpa.control_booking_appuntamento_sindaco.grouped_time_slots as $slot}
                {if $slot.count|gt(0)}
                    {if $style|eq( 'bgdark' )}{set $style = 'bglight'}{else}{set $style = 'bgdark'}{/if}
                    {include name=booking_slot slot=$slot uri='design:openpa/services/control_booking_appuntamento_sindaco/booking_day_slot.tpl' style=$style}
                {/if}
            {/foreach}

        {else}
            {include name=smart_login redirect_uri=$node.url_alias uri='design:smartlogin/login.tpl'}
        {/if}

        </div></div>
    </div>
    </div></div></div>
    <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
    </div>
</div>

{/if}
