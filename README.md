# Bright Nucleus MIME Types Database

[![Latest Stable Version](https://poser.pugx.org/brightnucleus/mime-types/v/stable)](https://packagist.org/packages/brightnucleus/mime-types)
[![Total Downloads](https://poser.pugx.org/brightnucleus/mime-types/downloads)](https://packagist.org/packages/brightnucleus/mime-types)
[![Latest Unstable Version](https://poser.pugx.org/brightnucleus/mime-types/v/unstable)](https://packagist.org/packages/brightnucleus/mime-types)
[![License](https://poser.pugx.org/brightnucleus/mime-types/license)](https://packagist.org/packages/brightnucleus/mime-types)

This is a Composer plugin that provides an automated version of the MIME types as defined by the Apache HTTP Server.

The main advantage is that the downloaded database will be updated on each `composer install` and `composer update`.

## Table Of Contents

* [Attribution](#attribution)
* [Installation](#installation)
* [Basic Usage](#basic-usage)
* [Contributing](#contributing)
* [License](#license)

## Attribution

This package uses data from the Apache HTTP Server, licensed under the Apache License v2.0.

You can read a copy of this license at [http://svn.apache.org/repos/asf/httpd/httpd/trunk/LICENSE](http://svn.apache.org/repos/asf/httpd/httpd/trunk/LICENSE).

## Installation

The only thing you need to do to make this work is adding this package as a dependency to your project:

```BASH
composer require brightnucleus/mime-types
```

## Basic Usage

On each `composer install` or `composer update`, a check will be made to see whether there's a new version of the database available. If there is, that new version is downloaded.

Usage is pretty straight-forward. Just use one of the two provided static methods:

```PHP
<?php

use BrightNucleus\MimeTypes\MimeTypes;

// Get the extensions for a given MIME type.
$name = MimeTypes::getExtensionsForType( 'image/jpeg' ); // Returns array( 'jpeg', 'jpg', 'jpe' ).

// Get the MIME types for a given extension.
$code = MimeTypes::getTypesForExtension( 'jpg' ); // Returns array( 'image/jpeg' ).
```

## Contributing

All feedback / bug reports / pull requests are welcome.

## License

This code is released under the MIT license.

For the full copyright and license information, please view the [`LICENSE`](LICENSE) file distributed with this source code.
