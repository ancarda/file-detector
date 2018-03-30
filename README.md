# ancarda/file-detector

_MIME type detector for partial file streams of unlimited length_

[![Latest Stable Version](https://poser.pugx.org/ancarda/file-detector/v/stable)](https://packagist.org/packages/ancarda/file-detector)
[![Total Downloads](https://poser.pugx.org/ancarda/file-detector/downloads)](https://packagist.org/packages/ancarda/file-detector)
[![License](https://poser.pugx.org/ancarda/file-detector/license)](https://choosealicense.com/licenses/mit/)
[![Build Status](https://travis-ci.org/ancarda/file-detector.svg?branch=master)](https://travis-ci.org/ancarda/file-detector)
[![Coverage Status](https://coveralls.io/repos/github/ancarda/file-detector/badge.svg?branch=master)](https://coveralls.io/github/ancarda/file-detector?branch=master)

file-detector is a library for PHP 7.0+ that accepts a file stream of any length, even if it's a partial file type, and provides a MIME detector for it.

The detector class can be used with any framework and has no dependencies. This library may be installed via composer with the following command:

	composer require ancarda/file-detector

The detector can then be initalized and used, like so. For example:

```php
<?php

$resource = fopen('example.jpg', 'r');
$detector = new \Ancarda\File\Detector;
$mimetype = $detector->determineMimeType($resource);

//$mimetype is now 'image/jpg'
```
