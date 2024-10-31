<?php

/*
Plugin Name: Pownce
Version: 1.3
Plugin URI: http://cavemonkey50.com/code/pownce/
Description: Displays your public Pownce messages for all to read.
Author: Ronald Heft, Jr.
Author URI: http://cavemonkey50.com/
*/

// Display Pownce messages
function pownce_messages($username = '', $num = 1, $list = true, $update = true, $reply = true) {
	include_once(ABSPATH . WPINC . '/rss.php');
	$messages = fetch_rss("http://pownce.com/feeds/public/$username");

	if ($list) echo '<ul class="pownce">';
	
	if ($username == '') {
		if ($list) echo '<li>';
		echo 'Username Not Configured';
		if ($list) echo '</li>';
	}
	
	if ( empty($messages->items) ) {
		if ($list) echo '<li>';
		echo 'No public Pownce messages.';
		if ($list) echo '</li>';
	} else {
		foreach ( $messages->items as $message ) {
			$msg = $message['summary'];
			$related = $message['link_related'];
			$event_name = $message['pownce']['event_name'];
			$event_location = $message['event_location'];
			$event_date = pownce_relative($message['event_date'], true);
			$replies = $message['pownce']['replies'];
			$replies_text = ($replies == 1) ? ' Reply' : ' Replies';
			$updated = pownce_relative($message['updated']);
			$link = $message['link'];
		
			if ($list) echo '<li class="pownce-message">'; elseif ($num != 1) echo '<p class="pownce-message">';
			echo $msg;
			if ($related) echo ' <span class="pownce-link">(<a href="' . $related . '">Link</a>)</span>';
			if ($event_name) echo ' <span class="pownce-event">(Event: ' . $event_name;
			if ($event_location && $event_name) echo ' at ' . $event_location;
			if ($event_date && $event_name) echo ' on ' . $event_date;
			if ($event_name) echo ')</span>';
			if ($reply) echo ' <span class="pownce-replies">(<a href="' . $link . '" title="' . $replies . $replies_text . '">' . $replies . '</a>)</span>';
			if ($update) echo ' <span class="pownce-timestamp"><em>' . $updated . '</em></span>';
			if ($list) echo '</li>'; elseif ($num != 1) echo '</p>';
		
			$i++;
			if ( $i >= $num ) break;
		}
	}
	
	if ($list) echo '</ul>';
}

// Present the date nicer
function pownce_relative($time, $event = false) {
	$offset = ($event) ? 4 : 5; // No clue why the timezone are different. Hopefully this won't change, or if it does, Pownce corrected it.
	$time = explode('T', substr($time, 0, -1));
	$date = explode('-', $time[0]);
	$time = explode(':', $time[1]);
	$time_orig = @gmmktime($time[0]+$offset, $time[1], $time[2], $date[1], $date[2], $date[0]);
	$time = time() - $time_orig;
	
	if ($event) return date('M j, g:ia', $time_orig);
	
	if ( $time < 3600 ) {
		$time = floor($time / 60);
		$time = ($time == 1) ? "$time min ago" : "$time mins ago";
	} else if ( $time < 86400 ) {
		$time = floor($time / 60 / 60);
		$time = ($time == 1) ? "$time hour ago" : "$time hours ago";
	} else
		$time = date('M j, g:ia', $time_orig);
	
	return $time;
}

// Pownce widget stuff
function widget_pownce_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_pownce($args) {
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		include_once(ABSPATH . WPINC . '/rss.php');
		$options = get_option('widget_pownce');
		$title = $options['title'];
		$username = $options['username'];
		$num = $options['num'];
		$update = ($options['update']) ? false : true;
		$reply = ($options['reply']) ? false : true;
		$messages = fetch_rss("http://pownce.com/feeds/public/$username");

		// These lines generate our output. Widgets can be very complex
		// but as you can see here, they can also be very, very simple.
		echo $before_widget . $before_title . $title . $after_title;
		pownce_messages($username, $num, true, $update, $reply);
		echo $after_widget;
	}

	// This is the function that outputs the form to let the users edit
	// the widget's title. It's an optional feature that users cry for.
	function widget_pownce_control() {

		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_pownce');
		if ( !is_array($options) )
			$options = array('title'=>'', 'username'=>'', 'num'=>'1');
		if ( $_POST['pownce-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['pownce_title']));
			$options['username'] = strip_tags(stripslashes($_POST['pownce_username']));
			$options['num'] = strip_tags(stripslashes($_POST['pownce_num']));
			$options['update'] = isset($_POST['pownce_update']);
			$options['reply'] = isset($_POST['pownce_reply']);
			update_option('widget_pownce', $options);
		}

		// Be sure you format your options to be valid HTML attributes.
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$username = htmlspecialchars($options['username'], ENT_QUOTES);
		$num = htmlspecialchars($options['num'], ENT_QUOTES);
		$update_checked = ($options['update']) ? 'checked="checked"' : '';
		$reply_checked = ($options['reply']) ? 'checked="checked"' : '';
		
		// Here is our little form segment. Notice that we don't need a
		// complete form. This will be embedded into the existing form.
		echo '<p style="text-align:right;"><label for="pownce_title">' . __('Title:') . ' <input style="width: 150px;" id="pownce_title" name="pownce_title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="pownce_username">' . __('Username:') . ' <input style="width: 150px;" id="pownce_username" name="pownce_username" type="text" value="'.$username.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="pownce_num">' . __('Number of Messages:') . ' <input style="width: 25px;" id="pownce_num" name="pownce_num" type="text" value="'.$num.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="pownce_update">' . __('Hide Timestamp:') . ' <input id="pownce_update" name="pownce_update" type="checkbox"'.$update_checked.' /></label></p>';
		echo '<p style="text-align:right;"><label for="pownce_reply">' . __('Hide Reply Count:') . ' <input id="pownce_reply" name="pownce_reply" type="checkbox"'.$reply_checked.' /></label></p>';
		echo '<input type="hidden" id="pownce-submit" name="pownce-submit" value="1" />';
	}
	
	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget(array('Pownce', 'widgets'), 'widget_pownce');

	// This registers our optional widget control form. Because of this
	// our widget will have a button that reveals a 300x100 pixel form.
	register_widget_control(array('Pownce', 'widgets'), 'widget_pownce_control', 250, 180);
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_pownce_init');

?>