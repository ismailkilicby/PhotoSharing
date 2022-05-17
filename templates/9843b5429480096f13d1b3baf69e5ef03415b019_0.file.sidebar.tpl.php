<?php
/* Smarty version 3.1.30, created on 2018-10-18 16:49:18
  from "C:\xampp\htdocs\pixelphoto\apps\default\home\templates\home\includes\sidebar.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bc89d6e491147_04795867',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '9843b5429480096f13d1b3baf69e5ef03415b019' => 
    array (
      0 => 'C:\\xampp\\htdocs\\pixelphoto\\apps\\default\\home\\templates\\home\\includes\\sidebar.tpl',
      1 => 1530855404,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:apps/".((string)$_smarty_tpl->tpl_vars[\'config\']->value[\'theme\'])."/main/templates/footer/sidebar-footer.tpl' => 1,
  ),
),false)) {
function content_5bc89d6e491147_04795867 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div class="home-sidebar-right">
    <?php if ($_smarty_tpl->tpl_vars['ad2']->value) {?>
	<div class="col-md-12">
		<?php echo $_smarty_tpl->tpl_vars['ad2']->value;?>

	</div>
	<?php }?>
	<div class="clear"></div>

	<div id="home-sidebar-sticky">
		<?php if ($_smarty_tpl->tpl_vars['trending']->value) {?>
			<div class="featured-posts">
				<h5><?php echo lang('featured_posts');?>
</h5>
				<div class="fluid list">
					<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['trending']->value, 'post_data');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['post_data']->value) {
?>
						<div class="item" id="">
							<a href="#" class="fluid" onclick="lightbox('<?php echo $_smarty_tpl->tpl_vars['post_data']->value['post_id'];?>
','profile');">
								<div class="thumb" style="background-image: url('<?php echo media($_smarty_tpl->tpl_vars['post_data']->value['thumb']);?>
');"></div>
								<div class="caption">
									<div class="uname">
										<h6><?php echo $_smarty_tpl->tpl_vars['post_data']->value['username'];?>
</h6>
									</div>
									<span>
										<time><?php echo time2str($_smarty_tpl->tpl_vars['post_data']->value['time']);?>
</time>
									</span>
								</div>
							</a>
						</div>
					<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

					<div class="clear"></div>
				</div>
			</div>
		<?php }?>
		<?php if ($_smarty_tpl->tpl_vars['config']->value['story_system'] == 'on') {?>
			<div class="stories">
				<h5><?php echo lang('stories');?>

				<?php if ($_smarty_tpl->tpl_vars['stories']->value) {?>
					<span class="create-new-post btn btn-link pull-right" data-type="story"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> <?php echo lang('add_story');?>
</span>
				<?php } else { ?>
				<?php }?>
				</h5>
				<div class="fluid list">
					<?php if ($_smarty_tpl->tpl_vars['stories']->value) {?>
						<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['stories']->value, 'story');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['story']->value) {
?>
							<div class="item" data-story="<?php echo $_smarty_tpl->tpl_vars['story']->value['user_id'];?>
">
								<?php if (!$_smarty_tpl->tpl_vars['story']->value['owner'] && $_smarty_tpl->tpl_vars['story']->value['new_stories']) {?>
									<div class="wrapper active">
										<img class="img-circle" src="<?php echo $_smarty_tpl->tpl_vars['story']->value['avatar'];?>
" alt="Picture" />
										<b class="recent-stroires badge" data-story="<?php echo $_smarty_tpl->tpl_vars['story']->value['user_id'];?>
"><?php echo $_smarty_tpl->tpl_vars['story']->value['new_stories'];?>
</b>
									</div>
							  	<?php } else { ?>	
									<div class="wrapper">
										<img class="img-circle" src="<?php echo $_smarty_tpl->tpl_vars['story']->value['avatar'];?>
" alt="Picture" />
									</div>
								<?php }?>
							  	<div class="caption">
							  		<a href="javascript:void(0);"><?php echo $_smarty_tpl->tpl_vars['story']->value['name'];?>
</a>
							  		<time><?php echo time2str($_smarty_tpl->tpl_vars['story']->value['time']);?>
</time>
							  	</div>
							</div>
						<?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

					<?php } else { ?>
						<span class="fluid text-center">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
							<?php echo lang('stories_from_people');?>

							<span class="create-new-post btn btn-primary" data-type="story">
					      		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> <?php echo lang('add_story');?>

					      	</span>
						</span>
					<?php }?>
				</div>
			</div>
		<?php }?>

        <?php if ($_smarty_tpl->tpl_vars['ad3']->value) {?>
    	<div class="col-md-12">
    		<?php echo $_smarty_tpl->tpl_vars['ad3']->value;?>

    	</div>
    	<?php }?>

		<div class="clear"></div>
		
		<div class="footer__container">
			<?php if ($_smarty_tpl->tpl_vars['config']->value['footer']) {?>
				<?php $_smarty_tpl->_subTemplateRender("file:apps/".((string)$_smarty_tpl->tpl_vars['config']->value['theme'])."/main/templates/footer/sidebar-footer.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>

			<?php }?>
		</div>
		<div class="clear"></div>
	</div>


</div>
<?php echo '<script'; ?>
>
	jQuery(document).ready(function($) {
		
		$("div[data-story]").click(function(event) {
			var id = $(this).data('story');
			$(this).find('.wrapper').addClass('anim_border');
			if (id) {
				$.ajax({
					url: link('story/show'),
					type: 'GET',
					dataType: 'json',
					data: {user_id:id},
				})
				.done(function(data) {
					if ($('body').find('.story-container').length) {
						$('.story-container').replaceWith($(data.html));
						$('.wrapper').removeClass('anim_border');
					}
					else{
						$('body').prepend($(data.html));
						$('.wrapper').removeClass('anim_border');
					}
				});
			}
		});
		
	});
<?php echo '</script'; ?>
><?php }
}
