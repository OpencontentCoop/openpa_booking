<div class="row">
    <div class="col-md-8 form-group">
        <label>
            Ragione sociale {if $settings['first_name']['is_required']}*{/if}
        </label>
        <input class="form-control" type="text" name="{$settings['first_name']['input_name']}" size="20" value="{$settings['first_name']['value']|wash}"/>
        <input class="form-control" type="hidden" name="{$settings['last_name']['input_name']}" size="20" value="null"/>
    </div>        
    <div class="col-md-4 form-group">
        <label>
            {"Email"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['email']['is_required']}*{/if}
        </label>
        <input class="form-control" type="text" name="{$settings['email']['input_name']}" size="20" value="{$settings['email']['value']|wash}"/>
    </div>
</div>


<div class="row">
    <div class="col-md-12 form-group">
        <div class="checkbox">
            <input type="checkbox" name="{$settings['split_payment']['input_name']}" size="20" value="1" {if $settings['split_payment']['value']|eq(1)}checked="checked"{/if}"/> In regime di split payment
        </div>
    </div>
</div>

<div class="row fattura_elettronica_pa">
    <div class="col-md-6 form-group">
        <div class="checkbox">
            <input type="checkbox" name="{$settings['fattura_elettronica_pa']['input_name']}" size="20" value="1" {if $settings['fattura_elettronica_pa']['value']|eq(1)}checked="checked"{/if}/> classificato come Pubblica Amministrazione
        </div>
    </div>
    <div class="col-md-6 form-group">
        <label data-label="Codice IPA">
            Codice IPA
        </label>
        <input class="form-control codice_ipa" type="text" name="{$settings['codice_ipa']['input_name']}" size="20" value="{$settings['codice_ipa']['value']|wash}"/>
    </div>
</div>

<script>{literal}
$(document).ready(function(){
    var checkbox = $('.fattura_elettronica_pa input[type="checkbox"]');
    var inputText = $('.fattura_elettronica_pa input.codice_ipa');
    var inputTextLabel = inputText.prev();
    var onChange = function(){
        if (checkbox.is(':checked')){
            inputText.attr('required', true);
            inputTextLabel.html(inputTextLabel.data('label') + ' *');
        }else{
            inputText.attr('required', false);
            inputTextLabel.html(inputTextLabel.data('label'));
        }
    };
    checkbox.on('change', onChange);
    onChange();
});
{/literal}</script>

<div class="row">
    <div class="col-md-4 form-group">
         <label>
            Codice fiscale {if $settings['vat_code']['is_required']}*{/if}
        </label>
        <input class="form-control" type="text" name="{$settings['vat_code']['input_name']}" size="20" value="{$settings['vat_code']['value']|wash}"/>
    </div>
    <div class="col-md-4 form-group">
         <label>
            Partita IVA {if $settings['vat_code2']['is_required']}*{/if}
        </label>
        <input class="form-control" type="text" name="{$settings['vat_code2']['input_name']}" size="20" value="{$settings['vat_code2']['value']|wash}"/>
    </div>
    <div class="col-md-4 form-group">
        <label>
            Telefono cellulare {if $settings['phone']['is_required']}*{/if}
        </label>
        <input class="form-control" type="text" name="{$settings['phone']['input_name']}" size="20" value="{$settings['phone']['value']|wash}"/>
    </div>    
</div>

<div class="row">
    <div class="col-md-8 form-group">
        <label>
            {"Street"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['street2']['is_required']}*{/if}
        </label>
        <input class="form-control" type="text" name="{$settings['street2']['input_name']}" size="20" value="{$settings['street2']['value']|wash}"/>
    </div>
    <div class="col-md-4 form-group">
        <label>
            {"Zip"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['zip']['is_required']}*{/if}
        </label>
        <input class="form-control" type="text" name="{$settings['zip']['input_name']}" size="20" value="{$settings['zip']['value']|wash}"/>
    </div>    
</div>

<div class="row">
    <div class="col-md-4 form-group">
        <label>
            {"Place"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['place']['is_required']}*{/if}
        </label>
        <input class="form-control" type="text" name="{$settings['place']['input_name']}" size="20" value="{$settings['place']['value']|wash}"/>
    </div>
    <div class="col-md-4 form-group">
        <label>
            {"Provincia"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['state']['is_required']}*{/if}
        </label>
        <select class="form-control" name="State">
            {foreach ezini( 'ProvinceSettings', 'List', 'province.ini' ) as $k => $v}
                <option value="{$k}" {if $settings['state']['value']|eq($k)}selected="selected"{/if}>{$v}</option>
            {/foreach}
        </select>
    </div>
    <div class="col-md-4 form-group">
        <label>
            {"Country"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['country']['is_required']}*{/if}
        </label>
        {def $countries = fetch( 'content', 'country_list' )}
        <select name="{$settings['country']['input_name']}" class="form-control">
            {foreach $countries as $_country}
                <option {if or(eq( $_country['Alpha2'], $settings['country']['value'] ), eq( $_country['Name'], $settings['country']['value'] ))} selected="selected" {/if}
                            value="{$_country['Alpha2']}">
                    {$_country['Name']}
                </option>
            {/foreach}
        </select>
    </div>
</div>

<div class="form-group">
    <label>
        {"Comment"|i18n("design/ocbootstrap/shop/userregister")} {if $settings['comment']['is_required']}*{/if}
    </label>
    <textarea name="{$settings['comment']['input_name']}" class="form-control" cols="80" rows="2">{$settings['comment']['value']|wash}</textarea>
</div>
