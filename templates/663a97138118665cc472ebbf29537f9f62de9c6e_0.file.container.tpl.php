<?php
/* Smarty version 3.1.30, created on 2018-10-18 16:49:09
  from "C:\xampp\htdocs\pixelphoto\apps\default\main\templates\container.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bc89d65631724_40050477',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '663a97138118665cc472ebbf29537f9f62de9c6e' => 
    array (
      0 => 'C:\\xampp\\htdocs\\pixelphoto\\apps\\default\\main\\templates\\container.tpl',
      1 => 1534158116,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:apps/".((string)$_smarty_tpl->tpl_vars[\'config\']->value[\'theme\'])."/main/templates/header/header.tpl' => 1,
    'file:apps/".((string)$_smarty_tpl->tpl_vars[\'config\']->value[\'theme\'])."/main/templates/js/extra-js.tpl' => 1,
    'file:apps/".((string)$_smarty_tpl->tpl_vars[\'config\']->value[\'theme\'])."/main/templates/includes/timeago.tpl' => 1,
  ),
),false)) {
function content_5bc89d65631724_40050477 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="title" content="<?php echo $_smarty_tpl->tpl_vars['config']->value['site_name'];?>
">
	<meta name="description" content="<?php echo $_smarty_tpl->tpl_vars['config']->value['site_desc'];?>
">
	<meta name="keywords" content="<?php echo $_smarty_tpl->tpl_vars['config']->value['meta_keywords'];?>
">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</title>
	<link rel="shortcut icon" type="image/png" href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
/media/img/icon.png"/>
	<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/js/libs/jquery-3.2.1.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/css/libs/bs3/js/bootstrap.js"><?php echo '</script'; ?>
>
	<link rel="stylesheet" href="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/css/libs/bs3/css/bootstrap.css">
	<?php if ($_smarty_tpl->tpl_vars['app_name']->value == 'welcome' || $_smarty_tpl->tpl_vars['app_name']->value == 'signup') {?>
	<link rel="stylesheet" href="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/css/styles.welcome.css">
	<?php } else { ?>
	<link rel="stylesheet" href="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/css/styles.master.css">
	<?php }?>
	<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/js/libs/jquery-form.v3.51.0.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/js/script.master.js"><?php echo '</script'; ?>
>
	<link href="https://fonts.googleapis.com/css?family=Open+Sans|Roboto:400,500" rel="stylesheet">
	<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_6038891875bc89d65624bf6_15113192', "additional_static");
?>

	<?php echo '<script'; ?>
>
		function xhr_url(){
			return '<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
/aj/';
		}

		function site_url(path){
			return '<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
/' + path;
		}
	<?php echo '</script'; ?>
>
</head>
<body data-app="<?php echo $_smarty_tpl->tpl_vars['app_name']->value;?>
" class="body-<?php echo $_smarty_tpl->tpl_vars['app_name']->value;?>
">
	<input type="hidden" class="hidden csrf-token" value="<?php echo $_smarty_tpl->tpl_vars['csrf_token']->value;?>
">
	<?php if ($_smarty_tpl->tpl_vars['config']->value['header']) {?>
		<?php $_smarty_tpl->_subTemplateRender("file:apps/".((string)$_smarty_tpl->tpl_vars['config']->value['theme'])."/main/templates/header/header.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

	<?php }?>
	<main class="container container-<?php echo $_smarty_tpl->tpl_vars['app_name']->value;?>
 container-<?php echo $_smarty_tpl->tpl_vars['app_name']->value;?>
-main">
		<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_20691641775bc89d65627cd6_39598141', "content");
?>

	</main>
	
	<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_6092049385bc89d65628748_67944210', "footer");
?>

	
	<?php if (!empty($_smarty_tpl->tpl_vars['exjs']->value)) {?>
		<?php ob_start();
$_smarty_tpl->_subTemplateRender("file:apps/".((string)$_smarty_tpl->tpl_vars['config']->value['theme'])."/main/templates/js/extra-js.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
$_smarty_tpl->assign('exjs', ob_get_clean());
?>

		<?php echo minify_js($_smarty_tpl->tpl_vars['exjs']->value);?>

	<?php }?>

	<?php $_smarty_tpl->_subTemplateRender("file:apps/".((string)$_smarty_tpl->tpl_vars['config']->value['theme'])."/main/templates/includes/timeago.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

	
	<div class="lightbox__container"></div>	
	<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_12944516165bc89d6562f271_64315772', "footer_static");
?>

	<?php if ($_smarty_tpl->tpl_vars['config']->value['google_analytics']) {?>
		<?php echo decode($_smarty_tpl->tpl_vars['config']->value['google_analytics']);?>

	<?php }?>
</body>
</html><?php }
/* {block "additional_static"} */
class Block_6038891875bc89d65624bf6_15113192 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block "additional_static"} */
/* {block "content"} */
class Block_20691641775bc89d65627cd6_39598141 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block "content"} */
/* {block "footer"} */
class Block_6092049385bc89d65628748_67944210 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block "footer"} */
/* {block "footer_static"} */
class Block_12944516165bc89d6562f271_64315772 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
}
}
/* {/block "footer_static"} */
}
