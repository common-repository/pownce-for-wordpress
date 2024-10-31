=== Pownce for WordPress ===
Contributors: cavemonkey50
Donate link: http://cavemonkey50.com/code/
Tags: pownce, widget, widgets
Requires at least: 2.2
Tested up to: 2.6
Stable tag: 1.3

Displays your public Pownce messages for all to read.

== Description ==

Pownce for WordPress displays your public Pownce messages for all to read.

**Usage**

If you use WordPress widgets, just drag the widget into your sidebar and configure. If widgets aren't your thing, use the following code to display your public Pownce messages:

`<?php pownce_messages('username'); ?>`

Pownce for WordPress also has several configurable options. Here's what you can configure:

`<?php pownce_messages('username', num_of_msgs, output_in_list, display_timestamp, display_replies); ?>`

So, if I wanted to display 3 messages, not in a list, with no replies, I would use the following:

`<?php pownce_messages('cavemonkey50', 3, false, true, false); ?>`

== Installation ==

Drop pownce.php into /wp-content/plugins/ and activate the plugin.