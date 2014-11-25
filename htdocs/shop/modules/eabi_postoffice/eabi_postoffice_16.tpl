<td class="delivery_option_radio">
    <div class="radio" id="uniform-delivery_option_{$eabi_carrier.id_address}_{$eabi_carrier.id_carrier}">
        <span>
            <input {if (!$eabi_carrierValue)}disabled="disabled" title="{l s='Please select pick-up point from the right' mod='eabi_postoffice'}"{/if} class="delivery_option_radio" type="radio" name="delivery_option[{$eabi_carrier.id_address}]" onclick="{$eabi_carrier.js}" value="{$eabi_carrierValue}" id="id_carrier{$eabi_carrier.id_carrier}" {if $eabi_carrier.isDefault == 1 && false}checked="checked"{/if}
                                        data-id_address="{$eabi_carrier.id_address}"
                                        data-key="{$eabi_carrierValue}"
                                        onchange="{if $eabi_carrier.opc}updateCarrierSelectionAndGift();{else}updateExtraCarrier($eabi_('#id_carrier{$eabi_carrier.id_carrier}').val(), {$eabi_carrier.id_address});{/if}"  
                                        onclick="{if $eabi_carrier.opc}updateCarrierSelectionAndGift();{else}updateExtraCarrier($eabi_('#id_carrier{$eabi_carrier.id_carrier}').val(), {$eabi_carrier.id_address});{/if}"  
                                        />
        </span>
    </div>
</td>
<td class="delivery_option_logo">
{if $eabi_carrier.img}<img src="{$eabi_carrier.img|escape:'htmlall':'UTF-8'}" alt="{$eabi_carrier.name|escape:'htmlall':'UTF-8'}" />{else}{$eabi_carrier.name|escape:'htmlall':'UTF-8'}{/if}
</td>
<td>{$eabi_carrier.delay}
    {if ($eabi_ERROR_MESSAGE)}<p class='error'>{$eabi_ERROR_MESSAGE}</p>{/if}
</td>
<td class="delivery_option_price">
    <div class="delivery_option_price">

        {if $eabi_carrier.price}
    {if $eabi_priceDisplay == 1}{convertPrice price=$eabi_carrier.price}{else}{convertPrice price=$eabi_carrier.price}{/if}
    {if $eabi_priceDisplay == 1} {l s='(tax excl.)' mod='eabi_postoffice'}{else} {l s='(tax incl.)' mod='eabi_postoffice'}{/if}
{else}
    {l s='Free!' mod='eabi_postoffice'}
{/if}
</div>
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
