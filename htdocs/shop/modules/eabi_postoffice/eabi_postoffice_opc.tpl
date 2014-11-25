<td class="carrier_action radio">
            <input {if (!$eabi_carrierValue)}disabled="disabled" title="{l s='Please select pick-up point from the right' mod='eabi_postoffice'}"{/if} type="radio" name="id_carrier" onclick="{$eabi_carrier.js}" value="{$eabi_carrierValue}" id="id_carrier{$eabi_carrier.id_carrier|intval}" {if $eabi_carrier.isSelected == 1}checked="checked"{/if}
                                        onchange="{if $eabi_carrier.opc}updateCarrierSelectionAndGift();{else}updateExtraCarrier($eabi_('#id_carrier{$eabi_carrier.id_carrier}').val(), {$eabi_carrier.id_address});{/if}"  
                                        onclick="{if $eabi_carrier.opc}updateCarrierSelectionAndGift();{else}updateExtraCarrier($eabi_('#id_carrier{$eabi_carrier.id_carrier}').val(), {$eabi_carrier.id_address});{/if}"  
                                        />
</td>
<td class="carrier_name">
    <label for="id_carrier{$eabi_carrier.id_carrier|intval}">
{if $eabi_carrier.img}<img src="{$eabi_carrier.img|escape:'htmlall':'UTF-8'}" alt="{$eabi_carrier.name|escape:'htmlall':'UTF-8'}" />{else}{$eabi_carrier.name|escape:'htmlall':'UTF-8'}{/if}
    </label>
</td>
<td class="carrier_infos">    <label for="id_carrier{$eabi_carrier.id_carrier|intval}">
        {$eabi_carrier.delay}</label>
    {if ($eabi_ERROR_MESSAGE)}<ol class='errorm'>{$eabi_ERROR_MESSAGE}</ol>{/if}
</td>
<td class="carrier_price">
    <label for="id_carrier{$eabi_carrier.id_carrier|intval}">

        {if $eabi_carrier.price}
    {if $eabi_priceDisplay == 1}{convertPrice price=$eabi_carrier.price}{else}{convertPrice price=$eabi_carrier.price}{/if}
    {if $eabi_priceDisplay == 1} {l s='(tax excl.)' mod='eabi_postoffice'}{else} {l s='(tax incl.)' mod='eabi_postoffice'}{/if}
{else}
    {l s='Free!' mod='eabi_postoffice'}
{/if}
</label>
</td>

<script type="text/javascript">
                                            /* <![CDATA[ */
                                            jQuery('#{$eabi_carrierId}').val('');
                                            jQuery.ajax('{$eabi_url}', {
                                                'type': 'POST',
                                                data: {
                                                    carrier_id: '{$eabi_carrierId}',
                                                    carrier_code: '{$eabi_carrierCode}',
                                                    div_id: '{$eabi_divId}',
                                                    address_id: '{$eabi_addressId}'
                                                },
                                                success: function(transport) {
                                                    jQuery('#{$eabi_divId}').html(transport);
                                                }
                                            });
    {$eabi_extraJs}
                                            /* ]]> */
</script>
