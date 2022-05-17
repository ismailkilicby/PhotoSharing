<?php
/* Smarty version 3.1.30, created on 2018-10-18 16:49:18
  from "C:\xampp\htdocs\pixelphoto\apps\default\main\templates\js\h-script.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5bc89d6e36e873_17496337',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '11f593bb776076bb0a68ce9d2f5762f7b78e511d' => 
    array (
      0 => 'C:\\xampp\\htdocs\\pixelphoto\\apps\\default\\main\\templates\\js\\h-script.tpl',
      1 => 1530991716,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5bc89d6e36e873_17496337 (Smarty_Internal_Template $_smarty_tpl) {
echo '<script'; ?>
>
jQuery(document).ready(function($) {
    <?php if (IS_LOGGED) {?>
    $("#get-notifications").click(function(event) {
        var notf_list = $("#notifications__list");
        var preloader = notf_list.next('.preloader').clone().removeClass('hidden');
        notf_list.html(preloader);
        get_notifications();
        delay(function(){
        
        },400);
    });
    <?php }?>

    $("input#search-users").blur(function(event) {
        delay(function(){
            $(".search-result").fadeOut(10);
        },500);
    });

    $("input#search-users").focus(function(event) {
        delay(function(){
            $(".search-result").fadeIn(10);
        },500);
    });

    $("input#search-users").keyup(function(event) {
        var keyword =  $(this).val();
        var desturl = link('main/search-users');
        var zis     = $(this);

        if (/^\#(.+)/.test(keyword)) {
            desturl = link('main/search-posts');
            keyword = keyword.substring(1);
        }

        if (keyword.length >= 3) {
            zis.siblings('.pp_head_search_loader').fadeIn(100);
            $.ajax({
                url: desturl,
                type: 'POST',
                dataType: 'json',
                
                data: {kw:keyword},
                
            })
            .done(function(data) {
                if (data.status == 200) {
                    $(".search-result").html(data.html);
                }
                else{
                    $(".search-result").empty();
                }
            });

            delay(function(){
                zis.siblings('.pp_head_search_loader').fadeOut(100);
            },500);
        }
    });
});


var didScroll;
var lastScrollTop = 0;
var delta = 5;
var navbarHeight = $('#header_nav').outerHeight();

$(window).scroll(function(event){
    didScroll = true;
});

setInterval(function() {
    if (didScroll) {
        hasScrolled();
        didScroll = false;
    }
}, 250);

function hasScrolled() {
    var st = $(this).scrollTop();
    if(Math.abs(lastScrollTop - st) <= delta)
        return;
    if (st > lastScrollTop && st > navbarHeight){
        $('nav.navbar-fixed-top').removeClass('nav-down').addClass('nav_up');
    } else {
        if(st + $(window).height() < $(document).height()) {
            $('nav.navbar-fixed-top').removeClass('nav_up').addClass('nav-down');
        }
    }
    lastScrollTop = st;
}
<?php echo '</script'; ?>
><?php }
}
