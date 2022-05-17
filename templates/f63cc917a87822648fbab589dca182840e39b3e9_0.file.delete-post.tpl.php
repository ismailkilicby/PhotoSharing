<?php
/* Smarty version 3.1.30, created on 2018-10-18 16:49:18
  from "C:\xampp\htdocs\pixelphoto\apps\default\main\templates\modals\delete-post.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bc89d6e5f4f28_64584182',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'f63cc917a87822648fbab589dca182840e39b3e9' => 
    array (
      0 => 'C:\\xampp\\htdocs\\pixelphoto\\apps\\default\\main\\templates\\modals\\delete-post.tpl',
      1 => 1524234008,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bc89d6e5f4f28_64584182 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div class="confirm--modal delpost--modal" style="display: none !important;">
	<div class="confirm--modal--inner">
		<div class="confirm--modal--body">
			<h5><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-delete" color="#ff1100" style="background-color: rgba(255, 17, 0, 0.25)"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg> <?php echo lang('delete_post');?>
?</h5>
			<p><?php echo lang('confirm_del_post');?>
</p>
		</div>
		<div class="confirm--modal--footer">
			<button class="btn btn-default" data-confirm--modal-dismiss><?php echo lang('cancel');?>
</button>
			<button class="btn btn-danger btn-red delete--post"><?php echo lang('delete');?>
</button>
		</div>
	</div>
</div><?php }
}
