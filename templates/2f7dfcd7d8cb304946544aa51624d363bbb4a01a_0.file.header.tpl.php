<?php
/* Smarty version 3.1.30, created on 2018-10-18 16:49:18
  from "C:\xampp\htdocs\pixelphoto\apps\default\main\templates\header\header.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bc89d6e23a855_61266215',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2f7dfcd7d8cb304946544aa51624d363bbb4a01a' => 
    array (
      0 => 'C:\\xampp\\htdocs\\pixelphoto\\apps\\default\\main\\templates\\header\\header.tpl',
      1 => 1530991818,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:apps/".((string)$_smarty_tpl->tpl_vars[\'config\']->value[\'theme\'])."/main/templates/js/h-script.tpl' => 1,
  ),
),false)) {
function content_5bc89d6e23a855_61266215 (Smarty_Internal_Template $_smarty_tpl) {
?>
<nav id="header_nav" class="navbar navbar-default navbar-fixed-top nav-down">
    <div class="container container-<?php echo $_smarty_tpl->tpl_vars['app_name']->value;?>
 container-<?php echo $_smarty_tpl->tpl_vars['app_name']->value;?>
-header">
        <div id="navbar-collapse">
            <ul class="nav navbar-nav navbar-left">
                <li class="logo">
                    <a href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
">
                        <img src="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
/media/img/logo.png" width="42px">
                    </a>
                </li>
				<?php if (IS_LOGGED) {?>
					<?php if ($_smarty_tpl->tpl_vars['app_name']->value == 'home') {?>
					<li class="pp_front_menu active">
                        <a href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#66757f" class="feather feather-home"><defs xmlns="http://www.w3.org/2000/svg"><linearGradient x1="19.28%" y1="86.72%" x2="88.05%" y2="12.24%" id="home"><stop stop-color="#5cb933" offset="0%"/><stop stop-color="#459522" offset="49.5%"/><stop stop-color="#41991a" offset="100%"/></linearGradient></defs><path d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z" fill="url(#home)"/></svg> <span><?php echo lang('home');?>
</span>
                        </a>
                    </li>
					<?php } else { ?>
					<li class="pp_front_menu">
                        <a href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#66757f" class="feather feather-home"><path d="M9,19V13H11L13,13H15V19H18V10.91L12,4.91L6,10.91V19H9M12,2.09L21.91,12H20V21H13V15H11V21H4V12H2.09L12,2.09Z" /></svg> <span><?php echo lang('home');?>
</span>
                        </a>
                    </li>
                    <?php }?>
					
					<?php if ($_smarty_tpl->tpl_vars['app_name']->value == 'explore') {?>
					<li class="pp_front_menu last_menu active">
                        <a href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
/explore">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#66757f" class="feather feather-compass"><defs xmlns="http://www.w3.org/2000/svg"><linearGradient x1="19.28%" y1="86.72%" x2="88.05%" y2="12.24%" id="explore"><stop stop-color="#3D3695" offset="0%"/><stop stop-color="#953594" offset="49.5%"/><stop stop-color="#DA2129" offset="100%"/></linearGradient></defs><path d="M12 10.9c-.61 0-1.1.49-1.1 1.1s.49 1.1 1.1 1.1c.61 0 1.1-.49 1.1-1.1s-.49-1.1-1.1-1.1zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm2.19 12.19L6 18l3.81-8.19L18 6l-3.81 8.19z" fill="url(#explore)"/></svg> <span><?php echo lang('explore');?>
</span>
						</a>
                    </li>
					<?php } else { ?>
					<li class="pp_front_menu last_menu">
                        <a href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
/explore">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#66757f" class="feather feather-compass"><path d="M7,17L10.2,10.2L17,7L13.8,13.8L7,17M12,11.1A0.9,0.9 0 0,0 11.1,12A0.9,0.9 0 0,0 12,12.9A0.9,0.9 0 0,0 12.9,12A0.9,0.9 0 0,0 12,11.1M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4Z" /></svg> <span><?php echo lang('explore');?>
</span>
                        </a>
                    </li>
					<?php }?>
				<?php }?>
                <li>
                    <form class="form navbar-search">
                        <div class="input">
                            <input type="text" class="form-control" placeholder="<?php echo lang('search');?>
.." id="search-users">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <div class="pp_head_search_loader" id="pp_loader"><div class="speeding_wheel"></div></div>
                        </div>
                        <div class="search-result"></div>
                    </form>
                </li>
            </ul>

            <ul class="nav navbar-nav navbar-right">
                <?php if (IS_LOGGED) {?>
                    <li class="hide_head_link <?php if ($_smarty_tpl->tpl_vars['app_name']->value == 'messages') {?>active<?php }?>">
                        <a href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
/messages" class="_messages">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            <small class="badge" id="new__messages"></small>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown notifications-list" id="get-notifications">
                            <span class="dropdown-toggle pointer" data-toggle="dropdown">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"></path></svg>
                                <small class="badge" id="new__notif"></small>
                            </span>
                            <div class="dropdown-menu zoom">
                                <div class="header"><?php echo lang('notifications');?>

                                    <a href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
/settings/notifications/<?php echo $_smarty_tpl->tpl_vars['me']->value['username'];?>
">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-sliders"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                                    </a>
                                </div>
                                <ul id="notifications__list"></ul>
                                <div class="preloader hidden">
                                    <div id="pp_loader"><div class="speeding_wheel"></div></div>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </li>
                    <?php if (IS_ADMIN) {?>
                        <li class="hide_head_link" title="<?php echo lang('admin_panel');?>
">
                            <a href="<?php echo pxp_link('admin-panel');?>
">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layout"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                            </a>
                        </li>
                    <?php }?>
                    <li class="hide_head_link <?php if ($_smarty_tpl->tpl_vars['app_name']->value == 'settings') {?>active<?php }?>">
                        <a href="<?php echo $_smarty_tpl->tpl_vars['me']->value['url'];?>
">
                            <img src="<?php echo $_smarty_tpl->tpl_vars['me']->value['avatar'];?>
" width="24px" height="24px" class="img-circle">
                        </a>
                    </li>
                <?php } else { ?>
                    <li>
                        <a href="<?php echo pxp_link('welcome');?>
"><?php echo lang('login');?>
</a>
                    </li>
					<li>
                        <a href="<?php echo pxp_link('signup');?>
"><?php echo lang('signup');?>
</a>
                    </li>
                <?php }?>
            </ul>
        </div>
		
		<?php if (IS_LOGGED) {?>
			<div id="second_header">
				<ul>
				<?php if ($_smarty_tpl->tpl_vars['app_name']->value == 'home') {?>
					<li class="pp_front_menu active">
                        <a href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#66757f" class="feather feather-home"><defs xmlns="http://www.w3.org/2000/svg"><linearGradient x1="19.28%" y1="86.72%" x2="88.05%" y2="12.24%" id="sec_home"><stop stop-color="#5cb933" offset="0%"/><stop stop-color="#459522" offset="49.5%"/><stop stop-color="#41991a" offset="100%"/></linearGradient></defs><path d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z" fill="url(#sec_home)"/></svg>
                        </a>
                    </li>
				<?php } else { ?>
					<li class="pp_front_menu">
                        <a href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#66757f" class="feather feather-home"><path d="M9,19V13H11L13,13H15V19H18V10.91L12,4.91L6,10.91V19H9M12,2.09L21.91,12H20V21H13V15H11V21H4V12H2.09L12,2.09Z" /></svg>
                        </a>
                    </li>
				<?php }?>
					
				<?php if ($_smarty_tpl->tpl_vars['app_name']->value == 'explore') {?>
					<li class="pp_front_menu last_menu active">
                        <a href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
/explore">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#66757f" class="feather feather-compass"><defs xmlns="http://www.w3.org/2000/svg"><linearGradient x1="19.28%" y1="86.72%" x2="88.05%" y2="12.24%" id="sec_explore"><stop stop-color="#3D3695" offset="0%"/><stop stop-color="#953594" offset="49.5%"/><stop stop-color="#DA2129" offset="100%"/></linearGradient></defs><path d="M12 10.9c-.61 0-1.1.49-1.1 1.1s.49 1.1 1.1 1.1c.61 0 1.1-.49 1.1-1.1s-.49-1.1-1.1-1.1zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm2.19 12.19L6 18l3.81-8.19L18 6l-3.81 8.19z" fill="url(#sec_explore)"/></svg>
						</a>
                    </li>
				<?php } else { ?>
					<li class="pp_front_menu last_menu">
                        <a href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
/explore">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#66757f" class="feather feather-compass"><path d="M7,17L10.2,10.2L17,7L13.8,13.8L7,17M12,11.1A0.9,0.9 0 0,0 11.1,12A0.9,0.9 0 0,0 12,12.9A0.9,0.9 0 0,0 12.9,12A0.9,0.9 0 0,0 12,11.1M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4Z" /></svg>
                        </a>
                    </li>
				<?php }?>
				<li class="msgz <?php if ($_smarty_tpl->tpl_vars['app_name']->value == 'messages') {?>active<?php }?>">
					<a href="<?php echo $_smarty_tpl->tpl_vars['site_url']->value;?>
/messages" class="_messages">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
						<small class="badge" id="new__messages_sec"></small>
					</a>
				</li>
				<?php if (IS_ADMIN) {?>
					<li title="<?php echo lang('admin_panel');?>
">
						<a href="<?php echo pxp_link('admin-panel');?>
">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layout"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
						</a>
					</li>
				<?php }?>
                    <li class="<?php if ($_smarty_tpl->tpl_vars['app_name']->value == 'settings') {?>active<?php }?>">
                        <a href="<?php echo $_smarty_tpl->tpl_vars['me']->value['url'];?>
">
                            <img src="<?php echo $_smarty_tpl->tpl_vars['me']->value['avatar'];?>
" width="21px" height="21px" class="img-circle">
                        </a>
                    </li>
				</ul>
			</div>
		<?php }?>
		
		
    </div>
    <div class="loadding-pgbar"><div class="bar"></div></div>
</nav>

<?php ob_start();
$_smarty_tpl->_subTemplateRender("file:apps/".((string)$_smarty_tpl->tpl_vars['config']->value['theme'])."/main/templates/js/h-script.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
$_smarty_tpl->assign('script', ob_get_clean());
?>

<?php echo minify_js($_smarty_tpl->tpl_vars['script']->value);
}
}
