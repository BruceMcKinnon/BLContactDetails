=== BL Contact Details ===

Contributors: Bruce McKinnon, with thanks to stvwhtly
Tags: contact, global, details, options, info, phone, fax, mobile, email, address, microdata, trading hours
Requires at least: 4.8
Tested up to: 5.1.1
Stable tag: 2019.03

Adds the ability to easily save contact information (e.g., address, phone, fax, email, trading hours).




== Description ==

Provides a variety of SEO and general website support features:

* - The ability to easily save contact information (e.g., address, phone, fax, email, trading hours).

* - Display on your web site via shortcodes which are correctly marked up with JSON-LD

* - Automatically generate JSON-LD markup on every page (for SEO purposes).

* - Automatically generate your Google Analytics markup, using your GA account code.

* - Display an embedded location map, using the lat/lng values you provide. Supports Leaflet/OpenStreetMaps maps.

* - Provides a popup box to provide EU privacy law compliance.




== Installation ==

1. Upload the 'bl-contact-details' folder to the '/wp-content/plugins/' directory.

2. Activate the plugin through the 'Plugins' menu in WordPress.

3. Enter contact details on the options page 'BL Contacts'.

4. Display the details using either the shortcodes or function calls.




== Frequently Asked Questions ==



= How do I edit my contact details? =

Navigate to the settings page by clicking on 'BL Contacts' on the left hand menu.




= What contact details can I store? =

Current available contact fields are:

	phone, phone2
	fax, fax2
	email, email2
	mobile, mobile2
	address, address2
	town, town2
	state, state2
	postcode, postcode2
	postal
	lat, lng, lat2, lng2
	zoom, zoom2
	pin_colour, pin_colour2
	abn
	facebook
	twitter
	instagram
	linkedin
	pinterest
	youtube
	hours




= How do I include details in my template? =

You can use the following function call to output details in your templates:

<?php if ( function_exists( 'contact_detail' ) ) { contact_detail( 'fax' ); } ?>




= How do you fetch contact details without outputting the value? =

The fifth parameter passed to 'contact_detail()' determines whether the value is returned, by setting the value to false.

'$phone = contact_detail( 'phone', '<b>', '</b>', '', false );'

The above code will fetch the phone number stored and wrap the response in bold tags.




= How do I display the map? =

Use the shortcode [blcontact-show-map]

You can style the map via the .blmap CSS class.

By default the map will be centered around the Lat/Lng values. But you can override it with the following parameters:

lat - override the saved latitude value
lng - override the saved longitude value
title
pin
zoom - defaults to 15 if not saved
addr_number - defaults to lat/lng 1, but you may also use lat/lng2






== Screenshots ==

1. The contact details management page.


== Changelog ==

2016.03 - 8 Jul 2016 - Added the 'innercontent' flag and Postal address.

2016.04 - 11 Jul 2016 - Address may now have non-Google map components wrapped in <span> tags.

2016.05 - 13 Jul 2016 - Supports line breaks in the textarea boxes.

2017.01 - 2 Mar 2017 - Fixes to address to support <span></span> in the middle of an address.

2017.01 - 15 Aug 2017 - Added support for the class and displaytext params

2017.02 - 20 Aug 2017 - Added microdata markup for the addresses and phone numbers

2017.03 - 11 Oct 2017 - Add support for Google Maps - Lat, Lng, Zoom

2017.04 - 19 Oct 2017 - Removed email form support - not required
											- Added support for second address/ph/fax/email, etc
											- Added CSS for settings form.

2017.05 - 8 Dec 2017 - Now support <script> tags for GA JS tracking code. NOTE - You also have to modify the functions.php in the theme so that extra <script> tags are not inluded.

2018.01 - 15 Feb 2018 - Added opening and closing hours. Now also providiing JSON-LD markups.
											- BREAKING CHANGE - Shortcode is now 'blcontact'.

2018.02 - 1 Mar 2018	- type=address - you can now add a clas that will wrap the entire address.

2018.03 - 10 Apr 2018 - Added 'url' type to display the URL in an a tag
											- Fixed bug in the opening hours display reduction.
											- 'hours' - if 'nolink = true, then do not try and group days together.

2018.04 - 25 May 2018	- Added jquery-eu-cookie-law-popup support
											- Complete GA script box dropped in favour of just providing the GA code. Allows the plugin to control when the GA code is initialised.

2018.05 - 28 Jun 2018	- build() - Now supports the use of the 'street' and 'street2' shortcode types.
											- Added startsWith() and endsWith() to the BLContactDetails class.

2018.06 - 10 Oct 2018 - The 'hours' option now supports the 'class' option.
											- build() now renamed bl_build().
											- shortcode() now renamed to bl_shortcode().
											- Updated code to align with WP PHP coding standards.
											- Added checkbox for disabling EU cookie popup

2018.07 - 11 Oct 2018 - Added BitBucket auto-updating via https://github.com/YahnisElsts/plugin-update-checker#bitbucket-integration

2019.01 - 9 Apr 2019  - Added the [blcontact-show-map] shortcode. Displays a Leaflet/OpenStreetMap on the page, using the lat/lng values.

2019.02 - 9 Apr 2019	- Fixed class calls to endsWith().
											- Fixed problem in bl_build() where we referenced $type, not $atts['type'].

2019.03	- 10 Apr 2019	- bl_insert_cookie_warning() - Now references jQuery.noconflict.
