<?php /* Smarty version Smarty-3.1.19, created on 2014-11-25 21:23:53
         compiled from "/data03/virt45210/domeenid/www.dukiboo.ee/htdocs/shop/ip8noom6um2jf9re/themes/default/template/helpers/list/list_action_delete.tpl" */ ?>
<?php /*%%SmartyHeaderCode:18690879145474d749b103a5-88971133%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0b1a5acc695187b1f9f12206484df98aee1f22fc' => 
    array (
      0 => '/data03/virt45210/domeenid/www.dukiboo.ee/htdocs/shop/ip8noom6um2jf9re/themes/default/template/helpers/list/list_action_delete.tpl',
      1 => 1406730120,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '18690879145474d749b103a5-88971133',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'href' => 0,
    'confirm' => 0,
    'action' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5474d749b35163_34167402',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5474d749b35163_34167402')) {function content_5474d749b35163_34167402($_smarty_tpl) {?>
<a href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['href']->value, ENT_QUOTES, 'UTF-8', true);?>
"<?php if (isset($_smarty_tpl->tpl_vars['confirm']->value)) {?> onclick="if (confirm('<?php echo $_smarty_tpl->tpl_vars['confirm']->value;?>
')){return true;}else{event.stopPropagation(); event.preventDefault();};"<?php }?> title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['action']->value, ENT_QUOTES, 'UTF-8', true);?>
" class="delete">
	<i class="icon-trash"></i> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['action']->value, ENT_QUOTES, 'UTF-8', true);?>

</a><?php }} ?>
