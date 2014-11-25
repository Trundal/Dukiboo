<p class="payment_module">
	<a href="{$link->getModuleLink('banklinkmaksekeskus', 'payment')}" title="{l s='Pay by Maksekeskus' mod='banklinkmaksekeskus'}">
		<img src="{$this_path}banklinkmaksekeskus.gif" alt="{l s='Pay by Maksekeskus' mod='banklinkmaksekeskus'}" />
		{$title}
	</a>{if $description != ''}<br/><span class="payment-description">{$description}</span><br/>{/if}
</p>