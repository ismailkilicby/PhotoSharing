<?php
/* Smarty version 3.1.30, created on 2018-10-18 16:49:18
  from "C:\xampp\htdocs\pixelphoto\apps\default\main\templates\modals\delete-comment.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bc89d6e621a71_33634852',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '73cda0bbc9a9584963f0831276edbbaf11e66913' => 
    array (
      0 => 'C:\\xampp\\htdocs\\pixelphoto\\apps\\default\\main\\templates\\modals\\delete-comment.tpl',
      1 => 1524233720,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bc89d6e621a71_33634852 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div class="confirm--modal delcomment--modal" style="display: none !important;">
	<div class="confirm--modal--inner">
		<div class="confirm--modal--body">
			<h5><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-slash" color="#ff1100" style="background-color: rgba(255, 17, 0, 0.25)"><path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path><line x1="18" y1="9" x2="12" y2="15"></line><line x1="12" y1="9" x2="18" y2="15"></line></svg> <?php echo lang('delete_comment');?>
?</h5>
			<p><?php echo lang('confirm_del_comment');?>
</p>
		</div>
		<div class="confirm--modal--footer">
			<button class="btn btn-default" data-confirm--modal-dismiss><?php echo lang('cancel');?>
</button>
			<button class="btn btn-danger btn-red delete--comment"><?php echo lang('delete');?>
</button>
		</div>
	</div>
</div><?php }
}
