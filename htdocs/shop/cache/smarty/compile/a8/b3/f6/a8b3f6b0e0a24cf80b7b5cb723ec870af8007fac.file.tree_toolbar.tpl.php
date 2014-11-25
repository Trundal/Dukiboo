<?php /* Smarty version Smarty-3.1.19, created on 2014-11-25 22:47:53
         compiled from "/data03/virt45210/domeenid/www.dukiboo.ee/htdocs/shop/ip8noom6um2jf9re/themes/default/template/controllers/products/helpers/tree/tree_toolbar.tpl" */ ?>
<?php /*%%SmartyHeaderCode:8033675595474eaf9da0b54-45119395%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a8b3f6b0e0a24cf80b7b5cb723ec870af8007fac' => 
    array (
      0 => '/data03/virt45210/domeenid/www.dukiboo.ee/htdocs/shop/ip8noom6um2jf9re/themes/default/template/controllers/products/helpers/tree/tree_toolbar.tpl',
      1 => 1406730120,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '8033675595474eaf9da0b54-45119395',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'actions' => 0,
    'action' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5474eaf9e81c36_44288576',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5474eaf9e81c36_44288576')) {function content_5474eaf9e81c36_44288576($_smarty_tpl) {?>
<div class="tree-actions pull-right">
	<?php if (isset($_smarty_tpl->tpl_vars['actions']->value)) {?>
	<?php  $_smarty_tpl->tpl_vars['action'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['action']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['actions']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['action']->key => $_smarty_tpl->tpl_vars['action']->value) {
$_smarty_tpl->tpl_vars['action']->_loop = true;
?>
		<?php echo $_smarty_tpl->tpl_vars['action']->value->render();?>

	<?php } ?>
	<?php }?>
</div><?php }} ?>
