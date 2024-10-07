<?php

defined( 'ABSPATH' ) || die();

class GWiz_Faker {
	/**
	 * @var \Faker\Generator
	 */
	public $faker;

	public function __construct() {
		$this->faker = Faker\Factory::create();
	}

	public function generate_entry( $form ) {
		$entry = array();

		foreach ( $form['fields'] as $field ) {
			if ( isset( $field->inputs ) && is_array( $field->inputs ) ) {
				foreach ( $field->inputs as $input ) {
					$entry[ $input['id'] ] = $this->generate_input_value( $form, $input['id'] );
				}

				continue;
			}

			$entry[ $field->id ] = $this->generate_input_value( $form, $field->id );
		}

		$entry['form_id'] = $form['id'];

		return $entry;
	}

	public function get_enabled_inputs( $field ) {
		$enabled_inputs = [];

		foreach ( $field->get_entry_inputs() as $input ) {
			if ( ! empty( $input['isHidden'] ) ) {
				continue;
			}

			// Convert the input ID with field ID (1.2 as example) to just 2
			$input_id = explode( '.', $input['id'] );
			$enabled_inputs[] = $input_id[1];
		}

		return $enabled_inputs;
	}

	public function is_input_enabled( $input_id, $field ) {
		$enabled_inputs = $this->get_enabled_inputs( $field );
		return in_array( $input_id, $enabled_inputs );
	}

	public function generate_input_value( $form, $field_id ) {
		if ( empty( $form ) || ! is_array( $form['fields'] ) ) {
			return '';
		}

		//If $field_id contains a . like 21.1, we might not be providing a value
		$base_field_id = $field_id;
		$dot_position  = strpos( $field_id, '.' );

		$input_id = -1;

		if ( $dot_position !== false ) {
			$base_field_id = substr( $field_id, 0, $dot_position );
			$input_id      = substr( $field_id, $dot_position + 1 );
		}

		foreach ( $form['fields'] as $field ) {
			if ( $field['id'] != $base_field_id ) {
				continue;
			}

			switch ( $field->get_input_type() ) {
				case 'address':
					if ( ! $this->is_input_enabled( $input_id, $field ) ) {
						return '';
					}

					switch ( $input_id ) {
						case '1':
							//street address
							return $this->faker->streetAddress();

						case '2':
							//street address line 2
							return $this->faker->secondaryAddress();

						case '3':
							//city
							return $this->faker->city();

						case '4':
							//state abbreviation
							return $this->faker->stateAbbr();

						case '5':
							//zip
							return $this->faker->postcode();

						case '6':
							// Always use US as the stuff faked above is using US locale
							return 'United States';
					}
					return;

				case 'checkbox':
					$inputs = $field->get_entry_inputs();

					if ( empty( $inputs ) ) {
						return '';
					}

					foreach ( $inputs as $input ) {
						// $input['id'] includes the field ID like "4.1" hence why we're using $field_id
						if ( $field_id == $input['id'] ) {
							return $this->faker->boolean() ? $input['label'] : '';
						}
					}

					return '';

				case 'consent':
					switch ( $input_id ) {
						case '1':
							return 'Checked';
						case '2':
							return $field->checkboxLabel;
						case '3':
							return $field->description;
					}
					return '';

				case 'date':
					return $this->faker->date( 'Y-m-d' );

				case 'email':
					return $this->faker->email();

				case 'fileupload':
					if ( $field->multipleFiles ) {
						return wp_json_encode( array(
							$this->faker->imageUrl(),
							$this->faker->imageUrl()
						) );
					} else {
						return $this->faker->imageUrl();
					}

				case 'post_image':
					return $this->faker->imageUrl();

				case 'hidden':
					return $this->faker->word();

				//ignore
				case 'html':
				case 'section':
					return '';

				case 'list':
					return 'item 1|item 2';

				case 'name':
					if ( ! $this->is_input_enabled( $input_id, $field ) ) {
						return '';
					}

					switch ( $input_id ) {
						case '2':
							// Prefix
							return $this->faker->title();

						case '3':
							// First Name
							return $this->faker->firstName();

						case '4':
							// Middle Name
							return $this->faker->firstName();

						case '6':
							// Last Name
							return $this->faker->lastName();

						case '8':
							// Suffix
							return $this->faker->suffix();
					}

					return '';

				case 'number':
					return $this->faker->randomNumber();

				case 'total':
					return $this->faker->randomFloat( 2, 0, 1000 );

				case 'phone':
					return $this->faker->numerify('(###) ###-####');

				case 'post_category':
					return 'Uncategorized:1';

				// Sentences
				case 'post_custom_field':
				case 'post_excerpt':
				case 'post_tags':
				case 'post_title':
				case 'text':
					return $this->faker->sentence();

				// Paragraphs
				case 'post_content':
				case 'textarea':
					return $this->faker->paragraph();

				case 'radio':
				case 'select':
					if (empty($field->choices)) {
						return '';
					}
					$random_choice = $this->faker->randomElement($field->choices);
					return $random_choice['text'];

				case 'time':
					return $this->faker->time( 'h:i a' ); //06:27 pm

				case 'website':
					return $this->faker->url();

				case 'uid':
					switch ( rgar( $field, 'gp-unique-id_type' ) ) {
						case 'alphanumeric':
							return $this->faker->regexify('[A-Za-z0-9]{6}'); // Generates a 6-character alphanumeric string
						case 'numeric':
							return $this->faker->numerify('######'); // Generates a 6-digit numeric string
						case 'sequential':
							// This is tricky with Faker, but you could generate a simple number sequence
							// using another mechanism if needed.
							static $sequential_id = 1;
							return str_pad($sequential_id++, 5, '0', STR_PAD_LEFT); // Sequential with leading zeros
					}
			}
		}

		return '';
	}
}
