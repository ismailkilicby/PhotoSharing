<?php
/* Smarty version 3.1.30, created on 2018-10-18 16:49:18
  from "C:\xampp\htdocs\pixelphoto\apps\default\main\templates\js\extra-js.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bc89d6e65b325_35981186',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2955221d382959011ebaf80dfe1a284b1b8cfa9f' => 
    array (
      0 => 'C:\\xampp\\htdocs\\pixelphoto\\apps\\default\\main\\templates\\js\\extra-js.tpl',
      1 => 1530841526,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bc89d6e65b325_35981186 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>
	function follow(user_id,object){
		if (!user_id || !object) { return false; }
		if (not(is_logged())) {
			redirect('welcome');
			return false;
		}

		object = $(object);

		if (object.hasClass('btn-following') == false) {
			object.find('span').text("<?php echo lang('following');?>
");
			object.find('svg').html('<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline>');

			if (!object.hasClass('btn-following')) {
				object.addClass('btn-following');
			}
		}
		else if(object.hasClass('btn-following') == true){
			object.find('span').text("<?php echo lang('follow');?>
");
			object.find('svg').html('<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line>');

			if (object.hasClass('btn-following')) {
				object.removeClass('btn-following');
			}
		}
		else{
			return false;
		}
		$.ajax({
			url: link('main/follow'),
			type: 'GET',
			dataType: 'json',
			
			data: {user_id:user_id},
			
		}).done(function(data) {});
	}

	function report_post(post_id,zis) {
		if (not(is_logged())) {
			redirect('welcome');
			return false;
		}
		if (!post_id || !zis) {
			return false;
		}

		$.ajax({
			url: link('posts/report'),
			type: 'POST',
			dataType: 'json',
			
			data: {id: post_id},
			
		})
		.done(function(data) {
			if (data.status == 200 && data.code == 1) {
				$(zis).find('a').text('<?php echo lang("cancel_report");?>
');
			}
			else if(data.status == 200 && data.code == 0){
				$(zis).find('a').text('<?php echo lang("report_post");?>
');
			}

			$.toast(data.message,{
              duration: 5000, 
              type: '',
              align: 'top-right',
              singleton: false
            });
		});
	}
<?php echo '</script'; ?>
><?php }
}
