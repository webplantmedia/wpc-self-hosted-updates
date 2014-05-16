# WP Canvas - Insert Code #

**Contributors:** cbaldelomar  
**Donate link:** http://webplantmedia.com/donate/  
**Tags:** insert javascript, insert js, insert html, insert css, html, javascript, js, css, code, custom code, google analytics, head section, head, footer section, footer, head area, footer area, top of page, scripts, cutsom scripts, insert scripts  
**Requires at least:** 3.9.1  
**Tested up to:** 3.9.1  
**Stable tag:** trunk  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Easily insert HTML, Javascript, CSS, into the head and footer areas of your site.

## Description ##

This plugin makes it easy for you to add custom scripts to the head and footer sections of your site. A theme can also add theme support to enable the insert of custom code (HTML, Javascript, and CSS) at the top of a page, above header, below header, above content, and below content.

[Live Demo & Documentation](http://webplantmedia.com/starter-themes/wordpresscanvas/features/plugins/wpc-insert-code/)

### Professional Support

If you need professional plugin support from me, the plugin author, you can contact me at [Web Plant Media](http://webplantmedia.com/).

### Plugin Development

If you're a theme author, plugin author, or just a code hobbyist, you can follow the development of this plugin on it's [GitHub repository](https://github.com/webplantmedia/wpc-insert-code). 

### Donations

Thank you so much for even considering supporting my work. If you have benefited from my WordPress plugins, or are appreciative of my customer support, and feel led to send me a monetary donation, please follow the link [here](http://webplantmedia.com/donate/). I am truly thankful for your hard earned giving.

## Installation ##

1. Uzip the `wpc-insert-code.zip` folder.
2. Upload the `wpc-insert-code` folder to your `/wp-content/plugins` directory.
3. In your WordPress dashboard, head over to the *Plugins* section.
4. Activate *WP Canvas - Web Fonts*.

## Frequently Asked Questions ##

### How do I add theme support

```
// Enable support for custom code to be inserted on various sections of theme
add_theme_support( 'wpc-insert-code', array( 'top-of-page', 'above-header', 'below-header', 'above-content', 'below-content' ) );

<!-- add lines of code in the appropriate section of your theme -->
<?php do_action( 'wpc_insert_code_top_of_page' ); ?>
<?php do_action( 'wpc_insert_code_above_header' ); ?>
<?php do_action( 'wpc_insert_code_below_header' ); ?>
<?php do_action( 'wpc_insert_code_above_content' ); ?>
<?php do_action( 'wpc_insert_code_below_content' ); ?>
```

## Screenshots ##

###1. Insert Code###
[missing image]


## Changelog ##

### Version 1.0

* Plugin released.  Everything is new!
