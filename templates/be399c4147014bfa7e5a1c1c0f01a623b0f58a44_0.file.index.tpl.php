<?php
/* Smarty version 3.1.30, created on 2018-10-18 16:49:17
  from "C:\xampp\htdocs\pixelphoto\apps\default\home\templates\home\index.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bc89d6dea7882_68477787',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'be399c4147014bfa7e5a1c1c0f01a623b0f58a44' => 
    array (
      0 => 'C:\\xampp\\htdocs\\pixelphoto\\apps\\default\\home\\templates\\home\\index.tpl',
      1 => 1530855476,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:apps/".((string)$_smarty_tpl->tpl_vars[\'config\']->value[\'theme\'])."/main/templates/container.tpl' => 1,
    'file:js/publisher-box.tpl' => 1,
    'file:includes/post-image.tpl' => 2,
    'file:includes/post-video.tpl' => 1,
    'file:includes/post-youtube.tpl' => 1,
    'file:includes/post-vimeo.tpl' => 1,
    'file:includes/post-dailymotion.tpl' => 1,
    'file:includes/follow.tpl' => 3,
    'file:includes/no-posted.tpl' => 1,
    'file:includes/sidebar.tpl' => 1,
    'file:apps/".((string)$_smarty_tpl->tpl_vars[\'config\']->value[\'theme\'])."/main/templates/includes/lazy-load.tpl' => 1,
    'file:apps/".((string)$_smarty_tpl->tpl_vars[\'config\']->value[\'theme\'])."/main/templates/modals/delete-post.tpl' => 1,
    'file:apps/".((string)$_smarty_tpl->tpl_vars[\'config\']->value[\'theme\'])."/main/templates/modals/delete-comment.tpl' => 1,
  ),
),false)) {
function content_5bc89d6dea7882_68477787 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_5196936865bc89d6de72759_11129282', "additional_static");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_19210637565bc89d6dea4766_31216339', "content");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_18780060395bc89d6dea6f09_77846463', "footer_static");
$_smarty_tpl->inheritance->endChild();
$_smarty_tpl->_subTemplateRender("file:apps/".((string)$_smarty_tpl->tpl_vars['config']->value['theme'])."/main/templates/container.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 2, true);
}
/* {block "additional_static"} */
class Block_5196936865bc89d6de72759_11129282 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

	
	<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/js/libs/gridAlicious/jquery.grid-a-licious.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/js/libs/afterglow.min.js"><?php echo '</script'; ?>
>
	<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/js/libs/jquery.pause.js"><?php echo '</script'; ?>
>
<?php
}
}
/* {/block "additional_static"} */
/* {block "content"} */
class Block_19210637565bc89d6dea4766_31216339 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>


	<div class="row">
		<div class="home-page-container">
		    <?php if ($_smarty_tpl->tpl_vars['ad1']->value) {?>
			<div class="col-md-12">
			    <?php echo $_smarty_tpl->tpl_vars['ad1']->value;?>

			</div><br><br>
			<?php }?>
			<div class="col-md-8">
				<?php if ($_smarty_tpl->tpl_vars['post_sys']->value) {?>
					<div class="postin-area">
						<?php ob_start();
$_smarty_tpl->_subTemplateRender("file:js/publisher-box.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
$_smarty_tpl->assign('publisher_box', ob_get_clean());
?>

						<?php echo minify_js($_smarty_tpl->tpl_vars['publisher_box']->value);?>

						<div class="clear"></div>
					</div>
				
					<div class="pp_pubbox_opt">
						<?php if ($_smarty_tpl->tpl_vars['config']->value['upload_images'] == 'on') {?>
							<div class="nd1 nds" title="<?php echo lang('add_img');?>
">
								<span class="create-new-post" data-type="image">
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#4caf50" class="feather feather-camera"><path d="M4,4H7L9,2H15L17,4H20A2,2 0 0,1 22,6V18A2,2 0 0,1 20,20H4A2,2 0 0,1 2,18V6A2,2 0 0,1 4,4M12,7A5,5 0 0,0 7,12A5,5 0 0,0 12,17A5,5 0 0,0 17,12A5,5 0 0,0 12,7M12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9Z" /></svg>
								</span>
							</div>	
						<?php }?>
						<?php if ($_smarty_tpl->tpl_vars['config']->value['upload_videos'] == 'on') {?>
							<div class="nd3 nds" title="<?php echo lang('add_vid');?>
">
								<span class="create-new-post" data-type="video">
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#2396f3" class="feather feather-video"><path d="M17,10.5V7A1,1 0 0,0 16,6H4A1,1 0 0,0 3,7V17A1,1 0 0,0 4,18H16A1,1 0 0,0 17,17V13.5L21,17.5V6.5L17,10.5Z" /></svg>
								</span>
							</div>
						<?php }?>
						<?php if ($_smarty_tpl->tpl_vars['config']->value['import_images'] == 'on') {?>
							<div class="nd5 nds" title="<?php echo lang('imp_gif');?>
">
								<span class="create-new-post" data-type="gif" style="padding: 5.9px 0;">
									<svg fill="#6b376b" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg" class="feather feather-user-plus" style="width: 34px;height: 34px;margin-top: -2px;"><path d="M0 0h24v24H0z" fill="none"></path><defs> <path d="M24 24H0V0h24v24z" id="a"></path> </defs> <clipPath id="b"> <use overflow="visible" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#a"></use> </clipPath> <path clip-path="url(#b)" d="M11.5 9H13v6h-1.5zM9 9H6c-.6 0-1 .5-1 1v4c0 .5.4 1 1 1h3c.6 0 1-.5 1-1v-2H8.5v1.5h-2v-3H10V10c0-.5-.4-1-1-1zm10 1.5V9h-4.5v6H16v-2h2v-1.5h-2v-1z"></path></svg>
								</span>
							</div>
						<?php }?>
						<?php if ($_smarty_tpl->tpl_vars['config']->value['import_videos'] == 'on') {?>
							<div class="nd4 nds" title="<?php echo lang('imp_vid');?>
">
								<span class="create-new-post" data-type="embed">
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#cc8317" class="feather feather-link"><path d="M10.59,13.41C11,13.8 11,14.44 10.59,14.83C10.2,15.22 9.56,15.22 9.17,14.83C7.22,12.88 7.22,9.71 9.17,7.76V7.76L12.71,4.22C14.66,2.27 17.83,2.27 19.78,4.22C21.73,6.17 21.73,9.34 19.78,11.29L18.29,12.78C18.3,11.96 18.17,11.14 17.89,10.36L18.36,9.88C19.54,8.71 19.54,6.81 18.36,5.64C17.19,4.46 15.29,4.46 14.12,5.64L10.59,9.17C9.41,10.34 9.41,12.24 10.59,13.41M13.41,9.17C13.8,8.78 14.44,8.78 14.83,9.17C16.78,11.12 16.78,14.29 14.83,16.24V16.24L11.29,19.78C9.34,21.73 6.17,21.73 4.22,19.78C2.27,17.83 2.27,14.66 4.22,12.71L5.71,11.22C5.7,12.04 5.83,12.86 6.11,13.65L5.64,14.12C4.46,15.29 4.46,17.19 5.64,18.36C6.81,19.54 8.71,19.54 9.88,18.36L13.41,14.83C14.59,13.66 14.59,11.76 13.41,10.59C13,10.2 13,9.56 13.41,9.17Z" /></svg>
								</span>
							</div>	
						<?php }?>
					</div>
				<?php }?>
				
				<div class="home-posts-container">
					<?php if ($_smarty_tpl->tpl_vars['posts']->value) {?>
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['posts']->value, 'post_data', false, 'key');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['key']->value => $_smarty_tpl->tpl_vars['post_data']->value) {
?>
							<?php if ($_smarty_tpl->tpl_vars['post_data']->value['type'] == 'image') {?>
						   		<?php $_smarty_tpl->_subTemplateRender("file:includes/post-image.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>
 
						   	<?php } elseif ($_smarty_tpl->tpl_vars['post_data']->value['type'] == 'video') {?>
						   		<?php $_smarty_tpl->_subTemplateRender("file:includes/post-video.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

						   	<?php } elseif ($_smarty_tpl->tpl_vars['post_data']->value['type'] == 'gif') {?>
						   		<?php $_smarty_tpl->_subTemplateRender("file:includes/post-image.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

						   	<?php } elseif ($_smarty_tpl->tpl_vars['post_data']->value['type'] == 'youtube') {?>
						   		<?php $_smarty_tpl->_subTemplateRender("file:includes/post-youtube.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

						   	<?php } elseif ($_smarty_tpl->tpl_vars['post_data']->value['type'] == 'vimeo') {?>
						   		<?php $_smarty_tpl->_subTemplateRender("file:includes/post-vimeo.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>
 
						   	<?php } elseif ($_smarty_tpl->tpl_vars['post_data']->value['type'] == 'dailymotion') {?>
						   		<?php $_smarty_tpl->_subTemplateRender("file:includes/post-dailymotion.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>
 
						   	<?php }?>

							<?php if ($_smarty_tpl->tpl_vars['key']->value == 0 && count($_smarty_tpl->tpl_vars['follow']->value) > 1) {?>
								<?php $_smarty_tpl->_subTemplateRender("file:includes/follow.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

						   	<?php } elseif ($_smarty_tpl->tpl_vars['key']->value == 15 && count($_smarty_tpl->tpl_vars['follow']->value) > 15) {?>
								<?php $_smarty_tpl->_subTemplateRender("file:includes/follow.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

							<?php }?>
						<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

					<?php } else { ?>
						<?php $_smarty_tpl->_subTemplateRender("file:includes/follow.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

						<?php $_smarty_tpl->_subTemplateRender("file:includes/no-posted.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
 
					<?php }?>
				</div>
				<div class="posts__loader hidden">
					<div id="pp_loader"><div class="speeding_wheel"></div></div>
				</div>
			</div>
			<div class="col-md-4 custom-fixed-element no-padding-left">
				<?php $_smarty_tpl->_subTemplateRender("file:includes/sidebar.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

			</div>
			<div class="clear"></div>
		</div>
		<?php if ($_smarty_tpl->tpl_vars['post_sys']->value) {?>
			<div class="add-post-bf--controls clearfix">
				<div class="btns">
					<?php if ($_smarty_tpl->tpl_vars['config']->value['import_images'] == 'on') {?>
						<div class="nd5 nds" flow="left" tooltip="<?php echo lang('imp_gif');?>
">
							<span class="create-new-post" data-type="gif">
					      		<svg fill="#676767" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg" class="feather feather-gif" style="width: 27px;height: 27px;"><path d="M0 0h24v24H0z" fill="none"></path><defs> <path d="M24 24H0V0h24v24z" id="a"></path> </defs> <clipPath id="b"> <use overflow="visible" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#a"></use> </clipPath> <path clip-path="url(#b)" d="M11.5 9H13v6h-1.5zM9 9H6c-.6 0-1 .5-1 1v4c0 .5.4 1 1 1h3c.6 0 1-.5 1-1v-2H8.5v1.5h-2v-3H10V10c0-.5-.4-1-1-1zm10 1.5V9h-4.5v6H16v-2h2v-1.5h-2v-1z"></path></svg>
					      	</span>
						</div>
					<?php }?>
					<?php if ($_smarty_tpl->tpl_vars['config']->value['import_videos'] == 'on') {?>
						<div class="nd4 nds" flow="left" tooltip="<?php echo lang('imp_vid');?>
">
					      	<span class="create-new-post" data-type="embed">
					      		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
					      	</span>
						</div>	
					<?php }?>
					<?php if ($_smarty_tpl->tpl_vars['config']->value['upload_videos'] == 'on') {?>
						<div class="nd3 nds" flow="left" tooltip="<?php echo lang('add_vid');?>
">
							<span class="create-new-post" data-type="video">
					      		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-video"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
					      	</span>
						</div>
					<?php }?>
					<?php if ($_smarty_tpl->tpl_vars['config']->value['upload_images'] == 'on') {?>
						<div class="nd1 nds" flow="left" tooltip="<?php echo lang('add_img');?>
">
					      	<span class="create-new-post" data-type="image">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
							</span>
						</div>	
					<?php }?>
				</div>
				<div id="floating-button">
					<span class="plus">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
					</span>
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x edit"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
				</div>
			</div>
		<?php }?>
		<div class="clear"></div>
	</div>


	<?php $_smarty_tpl->_subTemplateRender("file:apps/".((string)$_smarty_tpl->tpl_vars['config']->value['theme'])."/main/templates/includes/lazy-load.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

	<?php $_smarty_tpl->_subTemplateRender("file:apps/".((string)$_smarty_tpl->tpl_vars['config']->value['theme'])."/main/templates/modals/delete-post.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

	<?php $_smarty_tpl->_subTemplateRender("file:apps/".((string)$_smarty_tpl->tpl_vars['config']->value['theme'])."/main/templates/modals/delete-comment.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

	
	
	<?php echo '<script'; ?>
>
	jQuery(document).ready(function() {jQuery('.custom-fixed-element').theiaStickySidebar({additionalMarginTop: 65});});
		var pxScrolled = 180;
		$(window).scroll(function() {
			if ($(this).scrollTop() > pxScrolled) {
				$('.add-post-bf--controls').css({'bottom': '32px', 'transition': '.3s'});
			} else {
				$('.add-post-bf--controls').css({'bottom': '-60px'});
			} 
		});
		jQuery(document).ready(function($) {
			var scrolled = 0;
			var last_id  = 0;
			var posts_cn = $('.home-posts-container');

			$(window).scroll(function() {
			    if($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
			    	if (scrolled == 0) {
		                scrolled = 1;
		                posts_cn.siblings('.posts__loader').removeClass('hidden');
			            if ($('[data-post-id]').length > 0) {
			            	last_id  = $('[data-post-id]').last().data('post-id');
			            }
			            
						$.ajax({
							url: link('posts/load-tl-posts'),
							type: 'GET',
							dataType: 'json',
							data: {offset:last_id},
						}).done(function(data) {
							if (data.status == 200) {
								posts_cn.append($(data.html));
								scrolled = 0;
							}
							else{
								$(window).unbind('scroll');
							}

							posts_cn.siblings('.posts__loader').addClass('hidden');
						});
		       		}
			    }
			});
		});
	<?php echo '</script'; ?>
>
	
<?php
}
}
/* {/block "content"} */
/* {block "footer_static"} */
class Block_18780060395bc89d6dea6f09_77846463 extends Smarty_Internal_Block
{
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/js/libs/lightGallery/src/js/lightgallery.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/js/libs/lightGallery/modules/lg-zoom.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/js/libs/lightGallery/modules/lg-fullscreen.js"><?php echo '</script'; ?>
>
<link rel="stylesheet" href="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/js/libs/lightGallery/src/css/lightgallery.css">
<link rel="stylesheet" href="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
/main/static/js/libs/lightGallery/src/css/lg-transitions.css">
<?php
}
}
/* {/block "footer_static"} */
}
