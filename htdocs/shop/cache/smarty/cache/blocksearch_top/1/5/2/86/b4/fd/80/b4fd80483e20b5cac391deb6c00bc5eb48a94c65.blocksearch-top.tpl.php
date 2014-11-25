<?php /*%%SmartyHeaderCode:6974980395474e7f8305d07-08113105%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b4fd80483e20b5cac391deb6c00bc5eb48a94c65' => 
    array (
      0 => '/data03/virt45210/domeenid/www.dukiboo.ee/htdocs/shop/themes/default-bootstrap/modules/blocksearch/blocksearch-top.tpl',
      1 => 1406730120,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '6974980395474e7f8305d07-08113105',
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5474e91db84cd4_56461365',
  'has_nocache_code' => false,
  'cache_lifetime' => 31536000,
),true); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5474e91db84cd4_56461365')) {function content_5474e91db84cd4_56461365($_smarty_tpl) {?><!-- Block search module TOP -->
<div id="search_block_top" class="col-sm-4 clearfix">
	<form id="searchbox" method="get" action="http://dukiboo.ee/shop/et/search" >
		<input type="hidden" name="controller" value="search" />
		<input type="hidden" name="orderby" value="position" />
		<input type="hidden" name="orderway" value="desc" />
		<input class="search_query form-control" type="text" id="search_query_top" name="search_query" placeholder="Otsi" value="" />
		<button type="submit" name="submit_search" class="btn btn-default button-search">
			<span>Otsi</span>
		</button>
	</form>
</div>
<!-- /Block search module TOP --><?php }} ?>
