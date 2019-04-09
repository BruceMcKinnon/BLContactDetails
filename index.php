<?php
/*
Plugin Name: BL Contacts
Plugin URI: https://wordpress.org/plugins/bl-contacts
Description: Manage contact details and opening hours for your web site. Additionally provides support for schema.org meta markup for contact information and EU cookie policy support.
Based on StvWhtly's original plugin - http://wordpress.org/extend/plugins/contact/
Author: Bruce McKinnon
Version: 2019.03
Author URI: https://ingeni.net


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
2019.01 - 9 Apr 2019  - Added the blcontact-show-map shortcode. Displays a Leaflet/OpenStreetMap on the page, using the lat/lng values.
2019.02 - 9 Apr 2019	- Fixed class calls to endsWith().
											- Fixed problem in bl_build() where we referenced $type, not $atts['type'].
2019.03	- 10 Apr 2019	- bl_insert_cookie_warning() - Now references jQuery.noconflict.



*/



if ( !class_exists( 'BLContactDetails' ) ) {
	class BLContactDetails {
		public $name = 'BL Contacts';
		public $tag = 'contact';
		public $options = array();
		public $messages = array();
		public $details = array();

		public function __construct() {
			add_action( 'init', array( &$this, 'init' ) );
			if ( is_admin() ) {
				add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
				add_action( 'admin_init', array( &$this, 'admin_init' ) );
				add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
				add_action('admin_head', array( &$this, 'admin_register_head' ) );

			} else {
				add_shortcode( 'blcontact', array( &$this, 'bl_shortcode' ) );
				add_shortcode( 'contact', array( &$this, 'bl_shortcode' ) );		// Provide support for the old 'contact' shortcode
				add_shortcode( 'blcontact-json-ld', array( &$this, 'insert_json_ld' ) );	// Insert JSON-LD structured data onto the page
				add_shortcode( 'blcontact-show-map', array( &$this, 'bl_show_open_street_map' ) );	// Insert a Leaflet/OpenStreetMap onto the page

				add_filter( 'contact_detail', array( &$this, 'bl_build'), 1 );

				add_action( 'wp_enqueue_scripts', array( &$this, 'bl_insert_cookiefy' ) );
				add_action('wp_footer', array( &$this, 'bl_insert_cookie_warning'), 20 );

				// And enqueue the Leaflet apis
				add_action( 'wp_enqueue_scripts', array( &$this, 'bl_enqueue_leaflet' ) );


				add_action('wp_head', array( &$this, 'bl_insert_google_analytics' ));
				add_action('wp_footer', array( &$this, 'echo_json_ld' ));
			}

		}


		public function init() {
			$this->details = array(
				'phone' => __( 'Phone', 'contact' ),
				'fax' => __( 'Fax', 'contact' ),
				'email' => __( 'Email', 'contact' ),
				'mobile' => __( 'Mobile', 'contact' ),
				'address' => array(
					'label' => __( 'Address (wrap non Google Map parts of the address in <span> tags)', 'contact' ),
					'input' => 'textarea'
				),
				'town' => __( 'City/Town', 'contact' ),
				'state' => __( 'State', 'contact' ),
				'postcode' => __( 'Post Code', 'contact' ),
				'postal' => array(
					'label' => __( 'Postal', 'contact' ),
					'input' => 'textarea'
				),
				'lat' => __( 'Latitude', 'contact' ),
				'lng' => __( 'Longitude', 'contact' ),
				'zoom' => __( 'Map Zoom', 'contact' ),
				'pin_colour' => __( 'Map Pin Colour (hex)', 'contact' ),
				'abn' => __( 'ABN', 'contact' ),
				'facebook' => __( 'Facebook', 'contact' ),
				'twitter' => __( 'Twitter', 'contact' ),
				'instagram' => __( 'Instagram', 'contact' ),
				'linkedin' => __( 'LinkedIn', 'contact' ),
				'pinterest' => __( 'Pinterest', 'contact' ),
				'youtube' => __( 'YouTube', 'contact' ),
				'googleanalytics_code' => __( 'Google Analytics Tracking Code', 'contact' ),
				'eu_cookie_popup' => array(
					'label' => __( 'Enable Cookie Warning Popup', 'contact' ),
					'input' => 'checkbox'
				),
				'seo_business_type' => __( 'Business Type', 'contact' ),
				
				'open_mon' => array(
					'label' => __( 'Mon Open', 'contact' ),
					'input' => 'select'
				),				
				'close_mon' => array(
					'label' => __( 'Mon Close', 'contact' ),
					'input' => 'select'
				),
				'open_tue' => array(
					'label' => __( 'Tue Open', 'contact' ),
					'input' => 'select'
				),				
				'close_tue' => array(
					'label' => __( 'Tue Close', 'contact' ),
					'input' => 'select'
				),	
				'open_wed' => array(
					'label' => __( 'Wed Open', 'contact' ),
					'input' => 'select'
				),				
				'close_wed' => array(
					'label' => __( 'Wed Close', 'contact' ),
					'input' => 'select'
				),
				'open_thu' => array(
					'label' => __( 'Thur Open', 'contact' ),
					'input' => 'select'
				),				
				'close_thu' => array(
					'label' => __( 'Thur Close', 'contact' ),
					'input' => 'select'
				),
				'open_fri' => array(
					'label' => __( 'Fri Open', 'contact' ),
					'input' => 'select'
				),				
				'close_fri' => array(
					'label' => __( 'Fri Close', 'contact' ),
					'input' => 'select'
				),		
				'open_sat' => array(
					'label' => __( 'Sat Open', 'contact' ),
					'input' => 'select'
				),				
				'close_sat' => array(
					'label' => __( 'Sat Close', 'contact' ),
					'input' => 'select'
				),		
				'open_sun' => array(
					'label' => __( 'Sun Open', 'contact' ),
					'input' => 'select'
				),				
				'close_sun' => array(
					'label' => __( 'Sun Close', 'contact' ),
					'input' => 'select'
				),			
				'phone2' => __( 'Phone #2', 'contact' ),
				'fax2' => __( 'Fax #2', 'contact' ),
				'email2' => __( 'Email #2', 'contact' ),
				'mobile2' => __( 'Mobile #2', 'contact' ),
				'address2' => array(
					'label' => __( 'Address #2 (wrap non Google Map parts of the address in <span> tags)', 'contact' ),
					'input' => 'textarea'
				),
				'town2' => __( 'City/Town #2', 'contact' ),
				'state2' => __( 'State #2', 'contact' ),
				'postcode2' => __( 'Post Code #2', 'contact' ),
				'lat2' => __( 'Latitude #2', 'contact' ),
				'lng2' => __( 'Longitude #2', 'contact' ),
				'zoom2' => __( 'Map Zoom #2', 'contact' ),
				'pin_colour2' => __( 'Map #2 Pin Colour (hex)', 'contact' ),
			
			);

			$this->details = (array) apply_filters( $this->tag . '_details', $this->details, 1 );
			if ( $options = get_option( $this->tag ) ) {
				$this->options = $options;
			} else {
				update_option( $this->tag, array(
					'email' => get_option( 'admin_email' )
				) );
			}



			load_plugin_textdomain(
				$this->tag,
				false,
				basename( dirname( __FILE__ ) ) . '/languages/'
			);

			// Init auto-update from GitHub repo
			require 'plugin-update-checker/plugin-update-checker.php';
			$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
				'https://github.com/BruceMcKinnon/BLContactDetails',
				__FILE__,
				'bl-contact-details'
			);
		}


		// Register CSS for the plugin
		function admin_register_head() {
			$siteurl = get_option('siteurl');
			$url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/bl-contact-admin.css';
			echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
		}

		// Insert the EU privacy policy cookie support
		public function bl_insert_cookiefy() {
			$url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__));
			wp_enqueue_style( 'bl-eu-cookie-style', $url . '/jquery-eu-cookie-law-popup.css' );
			wp_enqueue_script( 'bl-eu-cookie-law-script', $url . '/jquery-eu-cookie-law-popup.js', array('jquery'), "1.0", false );
		}

		// Display the EU cookie warning message
		public function bl_insert_cookie_warning() {
			// First, get the URL to the privacy policy page
			$priv_page_id = get_option('wp_page_for_privacy_policy');
			if ($priv_page_id > 0) {
				$priv_page_url = get_permalink($priv_page_id);
			} else {
				$priv_page_url = "#";
			}

			$auto_accept = false;
			
			if ( $this->value( 'eu_cookie_popup' ) != 'checked' ) { 
				$auto_accept = true;
				?>
				<script>initialiseGoogleAnalytics();</script>
				<?php
			} else {
			?>
			<script type="text/javascript">
				var $jq = jQuery.noConflict();
				$jq(document).euCookieLawPopup().init({
					cookiePolicyUrl : '<?php echo($priv_page_url); ?>',
					popupPosition : 'bottom',
					popupTitle : '',
					popupText : 'By continuing to use the site, you agree to the use of cookies. Click the Learn more button for our Privacy Policy',
					buttonContinueTitle : 'Continue',
					buttonLearnmoreTitle : 'Learn more',
					buttonLearnmoreOpenInNewWindow : true,
					agreementExpiresInDays : 365,
					autoAcceptCookiePolicy : <?php echo( bool2str($auto_accept) ); ?>,
					htmlMarkup : false,
					colorStyle : 'blue'
				});
			</script>
			<?php
			}
		}


		// Set up to insert Google Analytics onto the page. Note, the Google Analytics code will only be
		// initialised after the user accepts the EU cookie popup.
		public function bl_insert_google_analytics() {
			
			$ga_code = $this->value('googleanalytics_code');

			$ga_script = "<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src=\"https://www.googletagmanager.com/gtag/js?id=" . $ga_code . "\"></script>
			<script>
				window.dataLayer = window.dataLayer || [];
				function gtag(){dataLayer.push(arguments);}
			</script>";
			$ga_script .= "<script>function initialiseGoogleAnalytics() { gtag('js', new Date()); gtag('config', '" . $ga_code . "', {'anonymize_ip': true} ); console.log('initialiseGoogleAnalytics initied'); }</script>";

			echo ($ga_script);
			?>

			<script type="text/javascript">
			// Subscribe for the cookie consent events
			var $jq = jQuery.noConflict();
			if ( $jq(document).euCookieLawPopup().alreadyAccepted() ) {
				// User clicked on enabling cookies. Now it’s safe to call the init functions.
				initialiseGoogleAnalytics();
			}

			$jq(document).bind("user_cookie_consent_changed", function(event, object) {
				const userConsentGiven = $jq(object).attr('consent');
				if (userConsentGiven) {
					// User clicked on enabling cookies. Now it’s safe to call the init functions.
					initialiseGoogleAnalytics();
				}
			});
			</script>
		<?php
		}

		//
		// Utility functions
		//
		private function startsWith($haystack, $needle) {
			// search backwards starting from haystack length characters from the end
			return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
		}

		private function endsWith($haystack, $needle) {
			// search forward starting from end minus needle length characters
			return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
		}


		private function get_local_upload_path() {
			$upload_dir = wp_upload_dir();
			return $upload_dir['baseurl'];
		}

		private function fb_log($msg) {
			$upload_dir = wp_upload_dir();
			$logFile = $upload_dir['basedir'] . '/' . 'fb_log.txt';
			date_default_timezone_set('Australia/Sydney');

			// Now write out to the file
			$log_handle = fopen($logFile, "a");
			if ($log_handle !== false) {
				fwrite($log_handle, date("H:i:s").": ".$msg."\r\n");
				fclose($log_handle);
			}
		}



		private function bool2str($value) {
			if ($value)
				return 'true';
			else
				return 'false';
		}
		//
		// End utility functions
		//

		public function admin_menu() {
			add_menu_page(
				__( $this->name, 'contact' ),
				__( $this->name, 'contact' ),
				'publish_pages',
				$this->tag,
				array( &$this, 'settings' )
			);
		}

		public function admin_init() {
			register_setting( $this->tag . '_options', $this->tag );
		}

		public function settings() {
			include_once( 'settings.php' );
		}

		public function plugin_row_meta( $links, $file ) {
			$plugin = plugin_basename( __FILE__ );
			if ( $file == $plugin ) {
				return array_merge(
					$links,
					array( sprintf(
						'<a href="options-general.php?page=%s">%s</a>',
						$this->tag,
						__( 'Edit Details', 'contact' )
					) )
				);
			}
			return $links;
		}


		//
		// Build the content to be returned to the shortcode
		//
		public function bl_build( $args ) {
			$atts = shortcode_atts( array(
				'type' => false,
				'before' => '',
				'after' => '',
				'innercontent' => false,
				'echo' => false,
				'class' => '',
				'displaytext' => '',
				'nolink' => false,
			), $args );

			if ( ($atts['type'] != 'hours') && ($atts['type'] != 'url') ) {
				$value = $this->value( $atts['type'] );

				// 2018.04 - Support the 'street' alias for the address fields.
				if ($atts['type'] == 'street') {
					$value = $this->value( 'address' );
					$atts['nolink'] = true;
				}
				if ($atts['type'] == 'street2') {
					$value = $this->value( 'address2' );
					$atts['nolink'] = true;
				}

				if ( strlen( $value ) == 0 ) {
					return;
				}
			}

			switch ( $atts['type'] ) {
				case 'url':
					$url = get_bloginfo('url');

					// in case scheme relative URI is passed, e.g., //www.google.com/
					$value = trim($url, '/');
					// If scheme not included, prepend it
					if (!preg_match('#^http(s)?://#', $value)) {
							$value = 'http://' . $value;
					}
					$urlParts = parse_url($value);
					// remove www
					$value = preg_replace('/^www\./', '', $urlParts['host']);
					$value = '<a class="'.$atts['class'].'"href="'.$url.'">'.$value.'</a>';

					break;	

				case 'phone':
				case 'phone2':
				case 'mobile':
				case 'mobile2':

						if ( $atts['displaytext'] == '' ) {
							$atts['displaytext'] = $value;
						}
						$value = str_replace(' ' ,'',$value);
						if ($atts['innercontent']) {
							$value = '<a class="'.$atts['class'].'" href="tel:'.$value.'"><span itemprop="telephone" content='.$value.'>'.$atts['before'].$atts['displaytext'].$atts['after'].'</span></a>';
						} else {
							$value = '<a class="'.$atts['class'].'"href="tel:'.$value.'"><span itemprop="telephone" content='.$value.'>'.$atts['displaytext'].'</span></a>';
						}
					break;

				case 'address':
				case 'address2':
				case 'street':
				case 'street2':
						// v2016.04
						// Note, the address prefix (e.g., 'Unit 1, Level 2, xyx Building') which is not used to 
						// work out the Google Map location, may be removed by enclosing it in <span> tags
						$span_close = strpos($value,'</span>');
						$span_start = strpos($value,'<span>');
						if ($span_close !== false) {
							$first_half = substr($value,0,$span_start);
							$second_half = "";
							if ($span_close > 0) {
								$start_addr = $span_close+7;
								$len_addr = strlen($value) - ($span_close+7);
								if ($len_addr > 0)
									$second_half = substr($value,$start_addr,$len_addr);
							}

							$url_value = $first_half . $second_half;
							$url_value = str_replace('  ',' ',$url_value);

						} else {
							$url_value = $value;
						}

						if ($atts['type'] == 'address2') {
							$url_value .= ' ' . $this->value( 'town2' ) . ' ' . $this->value( 'state2' ) . ' ' . $this->value( 'postcode2' );							
						} elseif ($atts['type'] == 'address') {
							$url_value .= ' ' . $this->value( 'town' ) . ' ' . $this->value( 'state' ) . ' ' . $this->value( 'postcode' );
						}

						// v2016.05 - Strip line breaks from the URL
						$url_value = str_replace( '<br />', '', $url_value );
						$url_value = str_replace( '\n', ' ', $url_value );
						$url_value = str_replace( '\r', '', $url_value );
						$url_value = str_replace( PHP_EOL, '', $url_value );

						if ($atts['type'] == 'address2') {
							$value = '<span itemprop="streetAddress">'.$value.'</span> ';
							$value .= '<span itemprop="addressLocality">'.$this->value( 'town2' ).'</span> ';
							$value .= '<span itemprop="addressRegion">'.$this->value( 'state2' ).'</span> ';
							$value .= '<span itemprop="postalCode">'.$this->value( 'postcode2' ).'</span>';
						} elseif ($atts['type'] == 'address') {
							$value = '<span itemprop="streetAddress">'.$value.'</span> ';
							$value .= '<span itemprop="addressLocality">'.$this->value( 'town' ).'</span> ';
							$value .= '<span itemprop="addressRegion">'.$this->value( 'state' ).'</span> ';
							$value .= '<span itemprop="postalCode">'.$this->value( 'postcode' ).'</span>';
						}

						if ( ($atts['type'] == 'street') || ($atts['type'] == 'street2') ) {
							$value = '<span itemprop="streetAddress">'.$value.'</span> ';
						}

						if (!$atts['nolink']) {
							if ($atts['innercontent']) {
								$value = '<a href="http://maps.google.com/?q='.urlencode($url_value).'" target=_blank">'.$atts['before'].$value.$atts['after'].'</a>';
							} else {
								$value = '<a href="http://maps.google.com/?q='.urlencode($url_value).'" target=_blank">'.$value.'</a>';
							}
						} else {
							if ($atts['innercontent']) {
								$value = $atts['before'].$value.$atts['after'];
							}							
						}

						$value = '<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">'.$value.'</span>';
						if (strlen($atts['class']) > 0) {
							$value = '<div class="'.$atts['class'].'">'.$value.'</div>';
						}
					break;

				case 'town':
				case 'town2':
					$value = '<span itemprop="addressLocality">'.$this->value( 'town' ).'</span> ';
					if ($atts['type'] == 'town2') {
						$value = '<span itemprop="addressLocality">'.$this->value( 'town2' ).'</span> ';
					}
					$value = '<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">'.$value.'</span>';
						if (strlen($atts['class']) > 0) {
							$value = '<div class="'.$atts['class'].'">'.$value.'</div>';
						}
					break;

					case 'state':
					case 'state2':	
						$value = '<span itemprop="addressRegion">'.$this->value( 'state' ).'</span> ';
						if ($atts['type'] == 'state2') {
							$value = '<span itemprop="addressRegion">'.$this->value( 'state2' ).'</span> ';
						}
						$value = '<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">'.$value.'</span>';
						if (strlen($atts['class']) > 0) {
							$value = '<div class="'.$atts['class'].'">'.$value.'</div>';
						}
						break;

					case 'postcode':
					case 'postcode2':
						$value = '<span itemprop="postalCode">'.$this->value( 'postcode' ).'</span> ';
						if ($atts['type'] == 'state2') {
							$value = '<span itemprop="postalCode">'.$this->value( 'postcode2' ).'</span> ';
						}	
						$value = '<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">'.$value.'</span>';
						if (strlen($atts['class']) > 0) {
							$value = '<div class="'.$atts['class'].'">'.$value.'</div>';
						}
					break;


				case 'email':
				case 'email2':
					if ($atts['displaytext'] == '') {
						$atts['displaytext'] = $value;
					}
					if ($atts['innercontent']) {
						$value = '<a class="'.$atts['class'].'" href="mailto:'.$value.'"><span itemprop="email">'.$atts['before'].$atts['displaytext'].$atts['after'].'</span></a>';
					} else {
						$value = '<a class="'.$atts['class'].'" href="mailto:'.$value.'"><span itemprop="email">'.$atts['displaytext'].'</span></a>';
					}
					break;

				case 'hours': 
						// Grab the open and close times fro the entire week
						$times = array(
							array(1,"Mo", $this->value( "open_mon" ), $this->value( "close_mon" )),
							array(2,"Tu", $this->value( "open_tue" ), $this->value( "close_tue" )),
							array(3,"We", $this->value( "open_wed" ), $this->value( "close_wed" )),
							array(4,"Th", $this->value( "open_thu" ), $this->value( "close_thu" )),
							array(5,"Fr", $this->value( "open_fri" ), $this->value( "close_fri" )),
							array(6,"Sa", $this->value( "open_sat" ), $this->value( "close_sat" )),
							array(7,"Su", $this->value( "open_sun" ), $this->value( "close_sun" )),
						);


						// Get rid of any closed days
						for ($idx = 6; $idx >= 0; --$idx) {
							if ( $times[$idx][2] == $times[$idx][3] ) {
								unset($times[$idx]);
							}
						}

						// Create the Microdata markup
						$meta_content = '';
						for ($idx = 0; $idx < count($times); $idx++) {
								$meta_content .= '<meta itemprop="openingHours" content="' . $times[$idx][1] . ' ' . formatHours($times[$idx][2]) . '-' . formatHours($times[$idx][3]) . '"/>';
						}

						if (!$atts['nolink']) {
							// Now sort according to open and close times
							usort($times,"cmp_times");

							// Group days of the same open/close times together
							$compact_times = array();
							$marker_start = $times[0][0]; $marker_end = -1;

							for ($idx = 1; $idx <= count($times); $idx++) {
								if ( ( $times[$idx-1][2] != $times[$idx][2] ) || ( $times[$idx-1][3] != $times[$idx][3] ) ) {
									$new_row = array($marker_start, $times[$idx-1][0], $times[$idx-1][2], $times[$idx-1][3]);
									array_push($compact_times, $new_row);
									$marker_start = $times[$idx][0];

									if ($idx == (count($times)-1) ) {
										$new_row = array($marker_start, $times[$idx][0], $times[$idx][2], $times[$idx][3]);
										array_push($compact_times, $new_row);	
									}
								}
							}
						} else {
							$compact_times = $times;
						}

						// Sort according to Day Of the Week
						usort($compact_times,"cmp_days");

						$value = "";
						$dowMap = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');

						$para_tag = '<p>';
						if (strlen($atts['class']) > 0) {
							$para_tag = '<p class="'.$atts['class'].'">';
						}

						for ($idx = 0; $idx < count($compact_times); $idx++) {
							$value .= $para_tag . $dowMap[$compact_times[$idx][0]-1];
							if (!$atts['nolink']) {
								if ($compact_times[$idx][0] != $compact_times[$idx][1]) {
									$value .= ' - ' . $dowMap[$compact_times[$idx][1]-1];
								}
							}

							$open = date("g:ia", mktime(abs($compact_times[$idx][2] / 100), abs($compact_times[$idx][2] % 100), 0, 1, 1, 2000) );
							$close = date("g:ia", mktime(abs($compact_times[$idx][3] / 100), abs($compact_times[$idx][3] % 100), 0, 1, 1, 2000) );
							$value .= ': ' . $open . ' - ' . $close . '</p>';
						}
						$value .= $meta_content;

					break;
			}

			if ($atts['innercontent']) {
				$detail = $value;
			} else {
				$detail = $atts['before'] . $value . $atts['after'];
			}
		
			if ( $atts['echo'] ) {
				echo $detail;
			} else {
				return $detail;
			}
		}


		public function value( $type = false ) {
			$retVal = null;

			if ( ($type != false)  && array_key_exists($type, $this->options) ) {
				if ( ('address' == $type) || ('postal' == $type) ) {
					$retVal = nl2br( $this->options[$type] );
				} else {
					$retVal = $this->options[$type];
				}
			}
			return $retVal;
		}

		// Entry point for the shortcode
		public function bl_shortcode( $args ) {

			$atts = shortcode_atts( array(
				'type' => false,
				'include' => false,
				'class' => '',
				'displaytext' => '',
				'nolink' => false,
			), $args );

			$retHtml = contact_detail( $atts['type'], false, false, false, false, $atts['class'], $atts['displaytext'], $atts['nolink'] );
			return $retHtml;
		}

		// Insert a block of JSON-LD structured data into the page
		public function insert_json_ld( $att ) {
			$retHtml = '<!-- BL Contact JSON feed --><script type="application/ld+json">{';

				$retHtml .= '"@context": "http://schema.org",';
				$retHtml .= '"@type": "' . $this->value('seo_business_type') . '",';
				$retHtml .= '"url": "' . get_bloginfo('url') . '",';
				$retHtml .= '"address": {';
					$retHtml .= '"@type": "PostalAddress",';
					$retHtml .= '"addressLocality": "' . $this->value('town') . '",';
					$retHtml .= '"addressRegion": "' . $this->value('state') . '",';
					$retHtml .= '"postalCode":"' . $this->value('postcode') . '",';
					$retHtml .= '"streetAddress": "' . $this->value('address') . '"';
				$retHtml .= '},';
				$retHtml .= '"description": "' . get_bloginfo('description') . '",';
				$retHtml .= '"name": "' . get_bloginfo('name') . '",';
				$retHtml .= '"telephone": "' . $this->value('phone') . '"';
				
				$lat = $this->value('lat');
				$lng = $this->value('lng');
				if ( (strlen($lat) > 0) && (strlen($lng) > 0) ) {
					$retHtml .= ',"geo": {';
						$retHtml .= '"@type": "GeoCoordinates",';
						$retHtml .= '"latitude": "' . $lat . '",';
						$retHtml .= '"longitude": "' . $lat . '"';
					$retHtml .= '}';
				}

				$social_urls = "";
				if (stripos($this->value('facebook'),'https://facebook.com') !== false) {
					$social_urls .= '"' . $this->value('facebook') . '",';
				}
				if (stripos($this->value('twitter'),'https://twitter.com') !== false) {
					$social_urls .= '"' . $this->value('twitter') . '",';
				}
				if (stripos($this->value('instagram'),'https://instagram.com') !== false) {
					$social_urls .= '"' . $this->value('instagram') . '",';
				}
				if (stripos($this->value('linkedin'),'https://linkedin.com') !== false) {
					$social_urls .= '"' . $this->value('linkedin') . '",';
				}
				if (stripos($this->value('pinterest'),'https://pinterest.com') !== false) {
					$social_urls .= '"' . $this->value('pinterest') . '",';
				}
				if (stripos($this->value('youtube'),'https://youtube.com') !== false) {
					$social_urls .= '"' . $this->value('youtube') . '",';
				}
				if ($this->endsWith($social_urls,",")) {
					$social_urls = substr($social_urls,0, strlen($social_urls)-1);
				}
				if (strlen($social_urls) > 0) {
					$retHtml .= ',"sameAs" : [ ' . $social_urls . ']';
				}


				// Opening Hours
				$openHours = "";
				if ($this->value('open_mon') != $this->value('close_mon') ) {
					$openHours .= '"Mo ' . formatHours($this->value('open_mon')) . '-' . formatHours($this->value('close_mon')) . '",';
				}
				if ($this->value('open_tue') != $this->value('close_tue') ) {
					$openHours .= '"Tu ' . formatHours($this->value('open_tue')) . '-' . formatHours($this->value('close_tue')) . '",';
				}
				if ($this->value('open_wed') != $this->value('close_wed') ) {
					$openHours .= '"We ' . formatHours($this->value('open_wed')) . '-' . formatHours($this->value('close_wed')) . '",';
				}
				if ($this->value('open_thu') != $this->value('close_thu') ) {
					$openHours .= '"Th ' . formatHours($this->value('open_thu')) . '-' . formatHours($this->value('close_thu')) . '",';
				}
				if ($this->value('open_fri') != $this->value('close_fri') ) {
					$openHours .= '"Fr ' . formatHours($this->value('open_fri')) . '-' . formatHours($this->value('close_fri')) . '",';
				}
				if ($this->value('open_sat') != $this->value('close_sat') ) {
					$openHours .= '"Sa ' . formatHours($this->value('open_sat')) . '-' . formatHours($this->value('close_sat')) . '",';
				}
				if ($this->value('open_sun') != $this->value('close_sun') ) {
					$openHours .= '"Su ' . formatHours($this->value('open_sun')) . '-' . formatHours($this->value('close_sun')) . '",';
				}
				if ($this->endsWith($openHours,",")) {
					$openHours = substr($openHours,0, strlen($openHours)-1);
				}

				if (strlen($openHours) > 0) {
					$retHtml .= ',"openingHours" : [ ' . $openHours . ']';
				}


			$retHtml .= '}</script>';
			return $retHtml;
		}

		// Echo the JSON into the footer
		public function echo_json_ld( $att ) {
			echo ( $this->insert_json_ld( $att ) );
		}


		public function bl_enqueue_leaflet() {
			$url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__));
			wp_enqueue_style( 'bl-leaflet-style', $url . '/leaflet/leaflet.css' );
			wp_enqueue_script( 'bl-leaflet-script', $url . '/leaflet/leaflet.js', array('jquery'), "1.0", false );
		}


		public function bl_show_open_street_map( $atts ) {
			$map_atts = shortcode_atts( array(
						'lat' => '',
						'lng' => '',
						'title' => '',
						'pin' => '',
						'zoom' => 15,
						'addr_number' => 1,
				), $atts );
			

			$lat = $map_atts['lat'];
			$lng = $map_atts['lng'];
			$zoom = $map_atts['zoom'];
			$colour = $map_atts['pin_colour'];
			$title = $map_atts['title'];
		
			
			if ($lat == '' || $lng == '') {
				// Grab the settings directly from the database
				$options = get_option('contact');
		
				if ( $map_atts['addr_number'] == 2 ) {
					$lat = $options['lat2'];
					$lng = $options['lng2'];
					$zoom = $options['zoom2'];	
					$pin_colour = $options['pin_colour2'];
				} else {
					$lat = $options['lat'];
					$lng = $options['lng'];
					$zoom = $options['zoom'];
					$pin_colour = $options['pin_colour'];
				}

				if ($zoom == '') {
					$zoom = '15';
				}
		
				if ( !$this->startsWith($pin_colour, '#') ) {
					$pin_colour = '#'.$pin_colour;
				}
				if ( !preg_match('/^#[a-f0-9]{6}$/i', $pin_colour) ) {
					$pin_colour = '#000000';
				}
		
				$title = $options['address'].' '.$contactDetails->options['town'].' '.$contactDetails->options['state'].' '.$contactDetails->options['postcode'];

			} 
			
			ob_start();
			$randId = "map-".rand();
			?>
				<div id="<?php echo($randId); ?>" class="blmap" style="min-height:200px;min-width:200px;"></div>
			
				<script type="text/javascript">
					var $jq = jQuery.noConflict();
					$jq( document ).ready( function() {
			
		
						function hexToRGB(h) {
							let r = 0, g = 0, b = 0;
		
							// 3 digits
							if (h.length == 4) {
								r = "0x" + h[1] + h[1];
								g = "0x" + h[2] + h[2];
								b = "0x" + h[3] + h[3];
		
							// 6 digits
							} else if (h.length == 7) {
								r = "0x" + h[1] + h[2];
								g = "0x" + h[3] + h[4];
								b = "0x" + h[5] + h[6];
							}
		
							return "rgb("+ +r + "," + +g + "," + +b + ")";
						}
		
						function mapInit( mapId, lat, lng, place_title, zoom_level, pin_color ) {
							var rgb_color = hexToRGB(pin_color);
							var svg_pin = '<svg version="1.1" id="mapmarker" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 365 560" enable-background="new 0 0 365 560" xml:space="preserve"><g><path class="fill_color" style="fill:' + rgb_color + ';" d="M182.9,551.7c0,0.1,0.2,0.3,0.2,0.3S358.3,283,358.3,194.6c0-130.1-88.8-186.7-175.4-186.9 C96.3,7.9,7.5,64.5,7.5,194.6c0,88.4,175.3,357.4,175.3,357.4S182.9,551.7,182.9,551.7z M122.2,187.2c0-33.6,27.2-60.8,60.8-60.8 c33.6,0,60.8,27.2,60.8,60.8S216.5,248,182.9,248C149.4,248,122.2,220.8,122.2,187.2z"/></g></svg>';
							var pin_url = encodeURI('data:image/svg+xml,' + svg_pin);

							var map = L.map('<?php echo($randId); ?>').setView([lat,lng], zoom_level);
		
							L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
								attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
								}).addTo(map);
		
							var customIcon = L.icon({
								iconUrl: pin_url,
								iconSize:[50,50]
								});
		
							L.marker([lat,lng], {icon: customIcon}).addTo(map);
						}
			
						mapInit("<?php echo($randId); ?>", <?php echo($lat); ?>, <?php echo($lng); ?>, "<?php echo($title); ?>", <?php echo($zoom); ?>, "<?php echo($pin_colour); ?>");
					});
				</script>
			<?php
			
			$retHtml = ob_get_contents();
			ob_end_clean();
			
			return $retHtml;
			}
	}
}

function formatHours($time_value, $hours12 = false) {
	$format_params = "H:i";
	if ($hours12) {
		$format_params = "g:ia";
	}
	$formatted = date($format_params, mktime(abs($time_value / 100), abs($time_value % 100), 0, 1, 1, 2000) );

	return $formatted;
}

function cmp_times($a, $b) {
	if ($a[2] == $b[2]) {
		// The opening hour is the same
		if ($a[3] == $b[3]) {
			// The closing hour is the same, so sort by day of week
			return ($a[0] < $b[0]) ? -1 : 1;
		} else {
			// Sort by closing hour
			return ($a[3] < $b[3]) ? -1 : 1;
		}
	}
	// Sort by opening hour
	return ($a[2] < $b[2]) ? -1 : 1;
}

function cmp_days($a, $b) {
	if ($a[0] == $b[0]) {
		// The opening hour is the same
		if ($a[2] == $b[2]) {
			// The closing hour is the same, so sort by day of week
			return ($a[3] < $b[3]) ? -1 : 1;
		} else {
			// Sort by closing hour
			return ($a[2] < $b[2]) ? -1 : 1;
		}
	}
	// Sort by opening hour
	return ($a[0] < $b[0]) ? -1 : 1;
}


$contactDetails = new BLContactDetails();

if ( isset( $contactDetails ) ) {
	function contact_detail( $t = false, $b = '', $a = '', $i = false, $e = false, $c = '', $d = '', $n = false ){
		$retHtml = apply_filters( 'contact_detail', array(
			'type' => $t,
			'before' => $b,
			'after' => $a,
			'innercontent' => $i,
			'echo' => $e,
			'class' => $c,
			'displaytext' => $d,
			'nolink' => $n,
		) );
		return $retHtml;
	}
}
