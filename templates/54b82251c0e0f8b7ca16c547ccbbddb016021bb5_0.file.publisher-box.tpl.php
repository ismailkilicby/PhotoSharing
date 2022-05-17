<?php
/* Smarty version 3.1.30, created on 2018-10-18 16:49:18
  from "C:\xampp\htdocs\pixelphoto\apps\default\home\templates\home\js\publisher-box.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bc89d6e3a1c88_76635823',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '54b82251c0e0f8b7ca16c547ccbbddb016021bb5' => 
    array (
      0 => 'C:\\xampp\\htdocs\\pixelphoto\\apps\\default\\home\\templates\\home\\js\\publisher-box.tpl',
      1 => 1534146344,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bc89d6e3a1c88_76635823 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div id="create-newpost"></div>
<?php echo '<script'; ?>
>
  jQuery(document).ready(function($) {
    $(".create-new-post").click(function(event) {
    	var post_type = $(this).attr('data-type');
    	if (post_type) {
    		$("#modal-progress").removeClass('hidden');
    		$.ajax({
    			url: link('posts/new-' + post_type),
    			type: 'GET',
    			dataType: 'json',
    		})
    		.done(function(data) {
    			if (data.status == 200) {
    				$('body').addClass('active');
    				$("#create-newpost").html(data.html).fadeIn(100);
    			}
                else{
                    if (data.message) {
                        $.toast(data.message,{
                          duration: 5000, 
                          type: '',
                          align: 'top-right',
                          singleton: false
                        });
                    }
                }
    			$("#modal-progress").addClass('hidden');
    		});
    	}
    });
    $(document).on('click','#close-anim-modal',function(event) {
		$("#create-newpost").fadeOut(100,function(){
			$(this).empty();
			$("body").removeClass('active');
		});
	});
	$(document).keyup(function(e) {
		if (e.keyCode == 27) {
			$("#create-newpost").fadeOut(100,function(){
			$(this).empty();
			$("body").removeClass('active');
		});
		}
	});
  });
<?php echo '</script'; ?>
>

<template class="template" id="post-editing-template">
    <div class="content post-editing-form">
        <div class="user-heading">
            <img src="<?php echo $_smarty_tpl->tpl_vars['me']->value['avatar'];?>
" width="35px" class="img-circle">
            <span><?php echo $_smarty_tpl->tpl_vars['me']->value['username'];?>
</span>
            <span class="pull-right"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><polygon points="16 3 21 8 8 21 3 21 3 16 16 3"></polygon></svg> <?php echo lang('edit_post');?>
</span>
        </div>
        <form class="form" id="edit-post-caption" action="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
/aj/posts/update" autocomplete="true">
            <div class="form-group">
                <textarea class="form-control" name="caption" rows="4" id="caption" placeholder="<?php echo lang('add_post_caption');?>
"></textarea>
            </div>
            <div class="form-group publish">
                <button type="submit" class="btn btn-info">
                    <i class="la la-paper-plane"></i> <?php echo lang('save_changes');?>

                </button>
                <button type="reset" class="btn btn-default" id="close-anim-modal">
                    <i class="la la-ban"></i> <?php echo lang('close');?>

                </button>
            </div>
            <input type="hidden" id="post_id" name="post_id">
            <input type="hidden" name="hash" value="<?php echo $_smarty_tpl->tpl_vars['csrf_token']->value;?>
">
        </form>
    </div>
</template><?php }
}
