# Gravity Wiz Faker [![Packagist Version](https://img.shields.io/packagist/v/gravitywiz/faker)](https://packagist.org/packages/gravitywiz/faker)

A class for generating fake entry data for Gravity Forms forms. Note, this class does not actually create the entries.

## Installation

Note: We recommend using [Jetpack Autoloader](https://github.com/Automattic/jetpack-autoloader) to help prevent conflicts.

```bash
composer require gravitywiz/faker
```

## Usage

```php
<?php
$gwiz_faker = new \GWiz_Faker();
$form = GFAPI::get_form( 123 );
$entries_to_generate = 10;

for ( $i = 0; $i < $entries_to_generate; $i++ ) {
	$entries[] = $gwiz_faker->generate_entry( $form );
}

// Add the entries to the database.
foreach ( $entries as $entry ) {
	GFAPI::add_entry( $entry );
}
```
