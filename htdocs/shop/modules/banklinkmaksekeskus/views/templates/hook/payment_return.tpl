{if $status == 'ok'}
	<p>{l s='Your order on' mod='banklinkmaksekeskus'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='banklinknordea'}
		<br /><br />
		{l s='Payment with:' mod='banklinknordea'}
		<br /><br />- {l s='an amout of' mod='banklinknordea'} <span class="price">{$total_to_pay}</span>
		<br /><br />{l s='For any questions or for further information, please contact our' mod='banklinknordea'} <a href="{$base_dir_ssl}contact-form.php">{l s='customer support' mod='banklinknordea'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='banklinknordea'} 
		<a href="{$base_dir_ssl}contact-form.php">{l s='customer support' mod='banklinknordea'}</a>.
	</p>
{/if}
