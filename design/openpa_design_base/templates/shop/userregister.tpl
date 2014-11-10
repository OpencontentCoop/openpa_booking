<div id="path">
  <p>
    <span class="path-text">1. {"Shopping basket"|i18n("design/ezwebin/shop/basket")}</span>
    <span class="path-separator">»</span>
    <span class="path-text"><strong>2. {"Account information"|i18n("design/ezwebin/shop/basket")}</strong></span>
    <span class="path-separator">»</span>
    <span class="path-text">3. {"Confirm order"|i18n("design/ezwebin/shop/basket")}</span>
  </p>
</div>

<div class="global-view-full block">

<form method="post" action={"/shop/userregister/"|ezurl}>

  <h1>{"Your account information"|i18n("design/ezwebin/shop/userregister")}</h1>

  <p>{"All fields marked with * must be filled in."|i18n("design/ezwebin/shop/userregister")}</p>

  {section show=$input_error}
    <div class="warning">
    <p>
    {"Input did not validate. All fields marked with * must be filled in."|i18n("design/ezwebin/shop/userregister")}
    </p>
    </div>
  {/section}


  <div class="block">
    <label>{"First name"|i18n("design/ezwebin/shop/userregister")}:*</label>
    <input class="box" type="text" name="FirstName" size="20" value="{$first_name|wash}" />
  </div>

  <div class="block">
    <label>{"Last name"|i18n("design/ezwebin/shop/userregister")}:*</label>
    <input class="box" type="text" name="LastName" size="20" value="{$last_name|wash}" />
  </div>

  <div class="block">
    <label>{"Email"|i18n("design/ezwebin/shop/userregister")}:*</label>
    <input class="box" type="text" name="EMail" size="20" value="{$email|wash}" />
  </div>

  <div class="block">
    <label>{"Company"|i18n("design/ezwebin/shop/userregister")}:</label>
    <input class="box" type="text" name="Street1" size="20" value="{$street1|wash}" />
  </div>

  <div class="block">
    <label>{"Street"|i18n("design/ezwebin/shop/userregister")}:*</label>
    <input class="box" type="text" name="Street2" size="20" value="{$street2|wash}" />
  </div>

  <div class="block">
    <label>{"Zip"|i18n("design/ezwebin/shop/userregister")}:*</label>
    <input class="box" type="text" name="Zip" size="20" value="{$zip|wash}" />
  </div>

  <div class="block">
    <label>{*"Place"|i18n("design/ezwebin/shop/userregister")*}Comune:*</label>
    <input class="box" type="text" name="Place" size="20" value="{$place|wash}" />
  </div>

  <div class="block">
    <label>{*"State"|i18n("design/ezwebin/shop/userregister")*}Provincia:</label>
    <input class="box" type="text" name="State" size="20" value="{$state|wash}" />
  </div>

  <div class="block">
    <label>{"Country"|i18n("design/ezwebin/shop/userregister")}:*</label>
    {include uri='design:shop/country/edit.tpl' select_name='Country' select_size=5 current_val=cond( $country, $country, "Italy")}
  </div>

  <div class="block">
    <label>{"Comment"|i18n("design/ezwebin/shop/userregister")}:</label>
    <textarea name="Comment" cols="80" rows="5">{$comment|wash}</textarea>
  </div>


  <div class="buttonblock">
      <input class="button" type="submit" name="CancelButton" value="{"Cancel"|i18n('design/ezwebin/shop/userregister')}"/>
      <input class="defaultbutton" type="submit" name="StoreButton" value="{"Continue"|i18n( 'design/ezwebin/shop/userregister')}"  style="float:right"  />
  </div>

</form>

</div>
