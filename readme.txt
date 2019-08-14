=== BL Contact Details ===

Contributors: Bruce McKinnon, with thanks to stvwhtly
Tags: contact, global, details, options, info, phone, fax, mobile, email, address, microdata, trading hours
Requires at least: 4.8
Tested up to: 5.1.1
Stable tag: 2019.13

Adds the ability to easily save contact information (e.g., address, phone, fax, email, trading hours).




== Description ==

Provides a variety of SEO and general website support features:

* - The ability to easily save contact information (e.g., address, phone, fax, email, trading hours).

* - Display on your web site via shortcodes which are correctly marked up with JSON-LD

* - Automatically generate JSON-LD markup on every page (for SEO purposes).

* - Automatically generate your Google Analytics markup, using your GA account code.

* - Display an embedded location map, using the lat/lng values you provide. Supports Leaflet/OpenStreetMaps maps.

* - Provides a popup box to provide EU privacy law compliance.

* - Provides FAQPage schema.org markup for FAQ lists.




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
	misc1, misc2




= How do I include details in my template? =

You can use the following function call to output details in your templates:

<?php if ( function_exists( 'contact_detail' ) ) { contact_detail( 'fax' ); } ?>




= How do you fetch contact details without outputting the value? =

The fifth parameter passed to 'contact_detail()' determines whether the value is returned, by setting the value to false.

'$phone = contact_detail( 'phone', '<b>', '</b>', '', false );'

The above code will fetch the phone number stored and wrap the response in bold tags.


= How do you display the street and town with regular comma delimiting

Use the ‘standardformatting’ option:

[blcontact type="street" standardformatting=1] [blcontact type="town"]




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
googlemap - defaults to 0 (OpenStreetMap). Will display a Google Map if set to 1 and a Google Maps JS key is aved in the settings.
minheight - set a min height for the map. Default = 250px
minwidth - set the min width of the map. Default = 100%



= How do I display a cluster map? =

Use the shortcode [blcontact-show-cluster-map]

lat - center latitude value
lng - center the saved longitude value
title
pin
zoom - defaults to 10 if not specified
googlemap - defaults to 0 (OpenStreetMap). Will display a Google Map if set to 1 and a Google Maps JS key is aved in the settings.
minheight - set a min height for the map. Default = 250px
minwidth - set the min width of the map. Default = 100%
latlngfile - specify the path to a text file containing the lat/lng values to be mapped.

Format is:

[
	{"lat":-33.43631744,"lng":151.43966675},
	{"lat":-33.43179703,"lng":151.44288635},
	{"lat":-33.43848038,"lng":151.43057251},
	{"lat":-33.43848038,"lng":151.43057251}
]



= How do I include SEO markup for a FAQ list? =

Use the shortcode [blcontact-faq]

For example:

[blcontact-faq wrapper_class="li.faq” question_class=“a.question” answer_class="div.answer”]
<ul>
	<li class=“faq”>
		<a class=“question”>Is this my question?</a>
		<div class=“answer”>Yes, it is.</div>
	</li>
	<li class=“faq”>
		<a class=“question”>Is this another question?</a>
		<div class=“answer”>Yes, it is another uestion.</div>
	</li>
</ul>
[/blcontact-faq]





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

2019.04 - 11 Apr 2019 - Various minor bug fixes.

2019.05 - 18 Apr 2019 - Re-introduced support for Google Maps. Use [blcontact-show-map googlemap="1"] shortcode. You must also provide a Google Maps JS key.

2019.06 - 3 May 2019	- Added cluster maps. User [blcontact-show-cluster-map].
											- [blcontact-show-cluster-map] and [blcontact-show-cluster-map] now support the minheight="y" and minwidth="x" parameters to set minimum pixel or pecentage dimensions.

2019.07 - 10 May 2019 - Added support for the nolink parameter when using 'email' and 'email2'.

2019.08 - 16 May 2019 - Linked address fields had incorrectly formatted target values. Was missing the opening doble-quote.
											- Open/Close hours now default to 9am and 5pm for Mon-Fri, closed Sat/Sun

2019.09 - 22 May 2019	- Added misc1 and misc2 options. Allows misc URLs to be stored

2019.10 - 1 Jul 2019	- Added the [blcontact-faq] shortcode.

2019.11 - 1 Jul 2019	- Added cleanString() to clear non-ASCII characters from SEO markup.
											- Apply shortcodes to FAQ content.

2019.12 - 14 Aug 2019 - Fixed an issue with trading hours, where setting nolink=false was not correctly compacting the trading hours display.

2019.13 - 16 Aug 2019 - Added the 'standardformatting' option. When true, commas are added between address components. When false, spaces are used. Defaults to false.
											- When displaying just the street, town, state, postcode as individual items, do not follow with a space.


