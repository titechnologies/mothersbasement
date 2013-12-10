<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=980px;" />
<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_enqueue_script('jquery'); ?>
<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
<?php wp_head(); ?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#logo').mouseover(function() {
		jQuery('#logo').animate({"margin-top": "0 auto","padding-top": "20px"}, 'slow');
		jQuery('#mainMenu').fadeIn('slow');
		});
});

function preload(imgArray) {
    jQuery(imgArray).each(function(){
        jQuery('<img/>')[0].src = this;
    });
}

	var menuImgs =  new Array("<?php echo site_url(); ?>/wp-content/uploads/2012/08/1.jpg",
							"<?php echo site_url(); ?>/wp-content/uploads/2012/08/2.jpg",
							"<?php echo site_url(); ?>/wp-content/uploads/2012/08/3.jpg",
							"<?php echo site_url(); ?>/wp-content/uploads/2012/08/4.jpg",
							"<?php echo site_url(); ?>/wp-content/uploads/2012/08/5.jpg");
	preload(menuImgs);

function menuMainImg(x) {
		mainDivImg = jQuery('#mainImg').css("background-image");
		if(mainDivImg != menuImgs[x]) { 
			jQuery('<img/>').attr('src', menuImgs[x]).load(function() {
				jQuery('#mainImg').css("background-image", "url("+menuImgs[x]+")"); 
			});
		}
}
</script>
<?php 
global $post;
$mainImgList = get_post_meta($post->ID, 'background-images', true);
if($mainImgList != '') {
$mainImgs = explode(',',$mainImgList);
?>
<style type="text/css">
#mainImg { background-image: url('<?php echo $mainImgs[rand(0,count($mainImgs))]; ?>'); }
</style>
<?
}
?>
</head>
<body <?php body_class(); ?>>
<div id="wrapper">
<div id="page">
    <div id="header">
        <h1><a href="<?php echo get_option('home'); ?>/" id="logo"><?php bloginfo('name'); ?></a></h1>
        <div id="mainMenu">
					<ul>
            <li><a href="http://www.nina-naustdal.com/comingsoon.html" onmouseover="menuMainImg(0);">Main Menu</a></li>
            <li id="menuCollections"><a href="#" onmouseover="menuMainImg(1);">Collections</a>
              <ul>
                <li><a href="<?php echo site_url(); ?>/couture-collection/">Couture Collection</a></li>
                <li><a href="<?php echo site_url(); ?>/sample-gallery/ready-to-wear/">Ready to Wear</a></li>
                <li><a href="<?php echo site_url(); ?>/bridal-collection/">Bridal collection</a></li>
                <li><a href="<?php echo site_url(); ?>/jewellery/">Jewellery</a></li>
                <li><a href="<?php echo site_url(); ?>/shoes/">Shoes</a></li>
                <li><a href="<?php echo site_url(); ?>/fur-and-leather/">Fur and Leather</a></li>
                <li><a href="<?php echo site_url(); ?>/perfume/">Perfume</a></li>
                <li><a href="<?php echo site_url(); ?>/accessories/">Accessories</a></li>
                <li><a href="<?php echo site_url(); ?>/interior-design/">Interior Design</a></li>
              </ul>
            </li>
            <li id="menuContact"><a href="#" onmouseover="menuMainImg(2);">Contact</a>
              <ul>
                <li><a href="<?php echo site_url(); ?>/find-nina-naustdal/">Find Nina Naustdal</a></li>
                <li><a href="<?php echo site_url(); ?>/become-member/">Become Member</a></li>
              </ul>
            </li>
            <li onmouseover="menuMainImg(3);"><a href="http://ninanaustdal.wordpress.com/">Press</a></li>
            <li onmouseover="menuMainImg(4);"><a href="http://www.facebook.com/pages/NINA-NAUSTDAL-COUTURE-LONDON/142127695859095">Blog</a></li>
          </ul>
        </div>
    </div>