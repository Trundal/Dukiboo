<?php /* Smarty version Smarty-3.1.19, created on 2014-11-25 22:34:14
         compiled from "/data03/virt45210/domeenid/www.dukiboo.ee/htdocs/shop/themes/default-bootstrap/modules/blockbestsellers/blockbestsellers-home.tpl" */ ?>
<?php /*%%SmartyHeaderCode:12483644965474e7c62db4c0-90217582%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '433ffe7a95c1a0d53e34bbbe07ba44560ef8ef93' => 
    array (
      0 => '/data03/virt45210/domeenid/www.dukiboo.ee/htdocs/shop/themes/default-bootstrap/modules/blockbestsellers/blockbestsellers-home.tpl',
      1 => 1406730120,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '12483644965474e7c62db4c0-90217582',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'best_sellers' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5474e7c62ec048_81383862',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5474e7c62ec048_81383862')) {function content_5474e7c62ec048_81383862($_smarty_tpl) {?>
<?php if (isset($_smarty_tpl->tpl_vars['best_sellers']->value)&&$_smarty_tpl->tpl_vars['best_sellers']->value) {?>
<?php echo $_smarty_tpl->getSubTemplate (((string)$_smarty_tpl->tpl_vars['tpl_dir']->value)."./product-list.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('products'=>$_smarty_tpl->tpl_vars['best_sellers']->value,'class'=>'blockbestsellers tab-pane','id'=>'blockbestsellers'), 0);?>

<?php } else { ?>
<ul id="blockbestsellers" class="blockbestsellers tab-pane">
	<li class="alert alert-info"><?php echo smartyTranslate(array('s'=>'No best sellers at this time.','mod'=>'blockbestsellers'),$_smarty_tpl);?>
</li>
</ul>
<?php }?><?php }} ?>
