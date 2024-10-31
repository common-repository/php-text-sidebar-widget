<?php

/* 
	Plugin Name: PHP.Text Widget
	Plugin URI: http://www.dragonsoft.us/
	Description: Simple SideBar PHP enabled Widget. Specify and execute arbitrary PHP code as your sidebar block.
	Version: 1.3
	Author: Serguei Dosyukov
	Author URI: http://www.dragonsoft.us/
*/ 

function wp_widget_phptext($args, $number = 1) {
	extract($args);
	$options = get_option('widget_phptext');
	$title = $options[$number]['title'];
	$text = apply_filters( 'widget_phptext', $options[$number]['text'] );
?>
		<?php echo $before_widget; ?>
			<?php if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
			<div class="phptextwidget"><?php eval($text); ?></div>
		<?php echo $after_widget; ?>
<?php
}

function wp_widget_phptext_control($number) {
	$options = $newoptions = get_option('widget_phptext');
	if ( !is_array($options) )
		$options = $newoptions = array();

	if ( $_POST["phptext-submit-$number"] ) {
		if ( $_POST["phptext-blocked-$number"] )
		{	$newoptions[$number]['title'] = $options[$number]['title'];
			$newoptions[$number]['text']  = $options[$number]['text'];
		}
		else
		{	$newoptions[$number]['title'] = strip_tags(stripslashes($_POST["phptext-title-$number"]));
			$newoptions[$number]['text'] = stripslashes($_POST["phptext-text-$number"]);
		}
	}

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_phptext', $options);
	}

	$title = attribute_escape($options[$number]['title']);
	$text = format_to_edit($options[$number]['text']);

	if ( current_user_can('manage_options') ) {
?>
			<input style="width: 450px;" id="phptext-title-<?php echo $number; ?>" name="phptext-title-<?php echo $number; ?>" type="text" value="<?php echo $title; ?>" />
			<textarea style="width: 450px; height: 280px;" id="phptext-text-<?php echo $number; ?>" name="phptext-text-<?php echo $number; ?>"><?php echo $text; ?></textarea>
			<input type="hidden" id="phptext-submit-<?php echo "$number"; ?>" name="phptext-submit-<?php echo "$number"; ?>" value="1" />
<?php
	}
	else
	{
?>
			<p>Current user does not have rights to modify this content</p>
			<input type="hidden" id="phptext-blocked-<?php echo "$number"; ?>" name="phptext-blocked-<?php echo "$number"; ?>" value="1" />
			<input type="hidden" id="phptext-submit-<?php echo "$number"; ?>" name="phptext-submit-<?php echo "$number"; ?>" value="1" />
<?php
	}
}

function wp_widget_phptext_setup() {
	$options = $newoptions = get_option('widget_phptext');
	if ( isset($_POST['phptext-number-submit']) ) {
		$number = (int) $_POST['phptext-number'];
		if ( $number > 9 ) $number = 9;
		if ( $number < 1 ) $number = 1;
		$newoptions['number'] = $number;
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_phptext', $options);
		wp_widget_phptext_register($options['number']);
	}
}

function wp_widget_phptext_page() {
	$options = $newoptions = get_option('widget_phptext');
?>
	<div class="wrap">
		<form method="POST">
			<h2><?php _e('PHP Text Widgets'); ?></h2>
			<p style="line-height: 30px;"><?php _e('How many PHP text widgets would you like?'); ?>
			<select id="phptext-number" name="phptext-number" value="<?php echo $options['number']; ?>">
<?php for ( $i = 1; $i < 10; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
			</select>
			<span class="submit"><input type="submit" name="phptext-number-submit" id="phptext-number-submit" value="<?php echo attribute_escape(__('Save')); ?>" /></span></p>
		</form>
	</div>
<?php
}

function wp_widget_phptext_register() {
	$options = get_option('widget_phptext');
	$number = $options['number'];
	if ( $number < 1 ) $number = 1;
	if ( $number > 9 ) $number = 9;
	$dims = array('width' => 460, 'height' => 350);
	$class = array('classname' => 'widget_phptext');
	for ($i = 1; $i <= 9; $i++) {
		$name = sprintf(__('PHP Text %d'), $i);
		$id = "phptext-$i"; // Never never never translate an id
		wp_register_sidebar_widget($id, $name, $i <= $number ? 'wp_widget_phptext' : /* unregister */ '', $class, $i);
		wp_register_widget_control($id, $name, $i <= $number ? 'wp_widget_phptext_control' : /* unregister */ '', $dims, $i);
	}
	add_action('sidebar_admin_setup', 'wp_widget_phptext_setup');
	add_action('sidebar_admin_page', 'wp_widget_phptext_page');
}

function wp_widget_phptext_init() {
	if ( !is_blog_installed() )
		return;

	wp_widget_phptext_register();
}

add_action('init', 'wp_widget_phptext_init', 1);

?>
