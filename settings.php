<div class="wrap bl_contacts">
	<h2><?php esc_html_e( $this->name ); ?></h2>
	<form method="post" action="options.php">
		<?php settings_fields( $this->tag . '_options' ); ?>
		<table class="form-table">
			<?php foreach ( $this->details as $key => $detail ) : ?>

			<?php $css_class = bl_contact_class($key); ?>
			<tr valign="top" <?php echo($css_class); ?> >
				<th>
					<label for="<?php esc_attr_e( $this->tag . '[' . $key . ']' ); ?>">
						<?php esc_html_e( is_array( $detail ) ? $detail['label'] : $detail, 'contact' ); ?>
					</label>
				</th>
				<td>
				<?php if ( isset( $detail['input'] ) && $detail['input'] == 'textarea' ) { ?>
					<textarea class="regular-text" cols="50" rows="5"
						id="<?php esc_attr_e( $this->tag . '[' . $key . ']' ); ?>"
						name="<?php esc_attr_e( $this->tag . '[' . $key . ']' ); ?>"><?php
							if ( array_key_exists( $key, $this->options ) ) {
								// v2016.05 - preserve lines breaks in multiline text boxes and allow some HTML
								$allowed = array(
									'span' => array(),
									'br' => array(),
									'em' => array(),
									'strong' => array(),
									'script' => array(),
									'script async src' => array(),
								);
								echo ( wp_kses ( $this->options[$key], $allowed ) ) ;
								}
						?></textarea>

				<?php } elseif ( isset( $detail['input'] ) && $detail['input'] == 'checkbox' ) { ?>
					<?php $value = 'checked';
						$checked = 0;
						if ( array_key_exists( $key, $this->options ) ) {
							if ( empty($this->options[$key]) ) {
								$this->options[$key] = 'checked';
							}
							$checked = $this->options[$key];
						}
					?>

					<input type="checkbox"
						id="<?php esc_attr_e( $this->tag . '[' . $key . ']' ); ?>"
						name="<?php esc_attr_e( $this->tag . '[' .$key . ']' ); ?>"
						value="<?php echo($checked); ?>" <?php echo($checked); ?> />


				<?php } elseif ( isset( $detail['input'] ) && $detail['input'] == 'select' ) { ?>
					<?php $saved_time = '0';
					if ( array_key_exists( $key, $this->options ) ) { 
							$saved_time = $this->options[$key];
					} ?>
					<select id="<?php esc_attr_e( $this->tag . '[' . $key . ']' ); ?>" name="<?php esc_attr_e( $this->tag . '[' .$key . ']' ); ?>">
						<?php $value = 0;
						while ($value <= 2330) {
							$hour = absint($value / 100);
							if ($hour > 12) {
								$hour -= 12;
							}
							$value_text = sprintf("%02d:%02d",$hour,($value % 100));
							if ($value == 0) {
								$value_text = 'Midnight';
							} elseif ($value == 1200) {
								$value_text = 'Noon';
							} elseif ($value < 1200) {
								$value_text .= ' AM';
							} else {
								$value_text .= ' PM';
							}
							?><option <?php if ($saved_time == $value ) { echo 'selected'; } ?> value="<?php echo $value; ?>"><?php echo $value_text; ?></option>
							<?php if (($value % 100) > 0) {
								$value += 70;
							} else {
								$value += 30;
							}
						} ?>
					
					</select>

				<?php } else { ?>
					<input type="text"
						id="<?php esc_attr_e( $this->tag . '[' . $key . ']' ); ?>"
						name="<?php esc_attr_e( $this->tag . '[' .$key . ']' ); ?>"
						class="regular-text"
						value="<?php if ( array_key_exists( $key, $this->options ) ) { esc_attr_e( $this->options[$key] ); } ?>" />

				<?php } ?>
				</td>
			</tr>

			<?php if ($key == 'seo_business_type') {
				echo ( '<tr valign="top" class="one-col"><td>&nbsp;</td><td><small><label><a href="https://schema.org/docs/full.html#CE.Organization" target="_blank">Click here for the full list of schema.org Organisations</a></label></small></td></tr>');
				echo ( '<tr style="clear:both;"><td class="full-width"><hr/><h3>Opening Hours</h3></td></tr>' );
			}
			if ($key == 'close_sun') {
				echo ( '<tr style="clear:both;"><td class="full-width"><hr/><h3>Extra Contact Details</h3></td></tr>' );
			}			
			?>

			<?php endforeach; ?>
		</table>
		<p class="submit">
			<input type="submit"
				name="Submit"
				class="button-primary"
				value="<?php esc_attr_e( 'Save Changes', 'contact' ); ?>" />
		</p>
	</form>
</div>


<?php
function bl_contact_class($type) {
	$retHtml = 'class="one-col"';

	switch ($type) {
		case 'phone':
		case 'fax':
		case 'email':
		case 'mobile':
		case 'pin_colour':
		case 'abn':
		case 'facebook':
		case 'twitter':
		case 'instagram':
		case 'linkedin':
		case 'pinterest':
		case 'youtube':
		case 'phone2':
		case 'fax2':
		case 'email2':
		case 'mobile2':
		case 'pin_colour2':
		case 'googleanalytics_code':
		case 'googlemapsapi_key':
			$retHtml = 'class="two-col"';
			break;

			case 'town':
			case 'state':
			case 'email':
			case 'postcode':
			case 'lat':
			case 'lng':
			case 'zoom':
			case 'town2':
			case 'state2':
			case 'email2':
			case 'postcode2':
			case 'lat2':
			case 'lng2':
			case 'zoom2':
			$retHtml = 'class="three-col"';
			break;
		
		case (preg_match('/open_.*/', $type) ? true : false):
		case (preg_match('/close_.*/', $type) ? true : false):
			$retHtml = 'class="four-col"';
			break;
	}

	return $retHtml;
}
?>