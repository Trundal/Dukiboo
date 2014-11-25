<?php /*%%SmartyHeaderCode:21180772975474e7fb6368e8-62636693%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '076258444a63b78f774ea33fa637bf24f269f6db' => 
    array (
      0 => '/data03/virt45210/domeenid/www.dukiboo.ee/htdocs/shop/themes/default-bootstrap/modules/blockmyaccountfooter/blockmyaccountfooter.tpl',
      1 => 1406730120,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '21180772975474e7fb6368e8-62636693',
  'variables' => 
  array (
    'link' => 0,
    'returnAllowed' => 0,
    'voucherAllowed' => 0,
    'HOOK_BLOCK_MY_ACCOUNT' => 0,
    'is_logged' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5474e7fb69fd72_32717047',
  'cache_lifetime' => 31536000,
),true); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5474e7fb69fd72_32717047')) {function content_5474e7fb69fd72_32717047($_smarty_tpl) {?>
<!-- Block myaccount module -->
<section class="footer-block col-xs-12 col-sm-4">
	<h4><a href="http://dukiboo.ee/shop/en/my-account" title="Manage my customer account" rel="nofollow">My account</a></h4>
	<div class="block_content toggle-footer">
		<ul class="bullet">
			<li><a href="http://dukiboo.ee/shop/en/order-history" title="My orders" rel="nofollow">My orders</a></li>
						<li><a href="http://dukiboo.ee/shop/en/order-slip" title="My credit slips" rel="nofollow">My credit slips</a></li>
			<li><a href="http://dukiboo.ee/shop/en/addresses" title="My addresses" rel="nofollow">My addresses</a></li>
			<li><a href="http://dukiboo.ee/shop/en/identity" title="Manage my personal information" rel="nofollow">My personal info</a></li>
						
            <li><a href="http://dukiboo.ee/shop/en/?mylogout" title="Sign out" rel="nofollow">Sign out</a></li>		</ul>
	</div>
</section>
<!-- /Block myaccount module -->
<?php }} ?>
