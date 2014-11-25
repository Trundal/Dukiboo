{capture name=path}{l s='Banklink payment via Maksekeskus' mod='banklinkmaksekeskus'}{/capture}
<h2>{l s='Order summary' mod='banklinkmaksekeskus'}</h2>

{assign var='current_step' value='payment'}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}

<h3>{l s='Banklink payment via Maksekeskus' mod='banklinkmaksekeskus'}</h3>
<form action="{$link->getModuleLink('banklinkmaksekeskus', 'validation', array(), true)|escape:'html'}" method="post">
<p>
	<img src="{$this_path}banklinkmaksekeskus.gif" alt="{l s='Banklink payment via Maksekeskus' mod='banklinkmaksekeskus'}" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='You have chosen to pay by Maksekeskus.' mod='banklinkmaksekeskus'}
	<br/><br />
	{l s='Here is a short summary of your order:' mod='banklinkmaksekeskus'}
</p>
<p style="margin-top:20px;">
	- {l s='The total amount of your order is' mod='banklinkmaksekeskus'}
	{if $currencies|@count > 1}
		{foreach from=$currencies item=currency}
			<span id="amount_{$currency.id_currency}" class="price" style="display:none;">{convertPriceWithCurrency price=Tools::convertPrice($total, $currency) currency=$currency}</span>
		{/foreach}
	{else}
		<span id="amount_{$currencies.0.id_currency}" class="price">{convertPriceWithCurrency price=Tools::convertPrice($total, $currency) currency=$currencies.0}</span>
	{/if}
	{l s='(tax incl.)' mod='banklinkmaksekeskus'}
</p>
<p>
	-
	{if $currencies|@count > 1}
		{l s='We accept several currencies to be payd by Maksekeskus payment gateway.' mod='banklinkmaksekeskus'}
		<br /><br />
		{l s='Choose one of the following:' mod='banklinkmaksekeskus'}
		<select id="currency_payement" name="currency_payement" onchange="showElemFromSelect('currency_payement', 'amount_')">
			{foreach from=$currencies item=currency}
				<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
			{/foreach}
		</select>
		<script language="javascript">showElemFromSelect('currency_payement', 'amount_');</script>
	{else}
		{l s='We accept the following currency to be payd by Maksekeskus:' mod='banklinkmaksekeskus'}&nbsp;<b>{$currencies.0.name}</b>
		<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}">
	{/if}
</p>
<p>
	{l s='Payment shall be started on the next page.' mod='banklinkmaksekeskus'}
	<br /><br />
	<b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='banklinkmaksekeskus'}.</b>
</p>
<p class="cart_navigation">
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='banklinkmaksekeskus'}</a>
	<input type="submit" name="submit" value="{l s='I confirm my order' mod='banklinkmaksekeskus'}" class="exclusive_large" />
</p>
</form>
{/if}