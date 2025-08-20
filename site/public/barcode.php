<?php

require_once "../vendor/autoload.php";

// Documentation: https://docs.zendframework.com/zend-barcode/
use Zend\Barcode\Barcode;

// SimpleImage v2.3: https://github.com/claviska/SimpleImage
require "sistema/System/Libs/SimpleImage.php";
use System\Libs\SimpleImage;

// Get the contents of a GD image resource (png) as a base64 string
function base64_resource($resource, $format) {
  if (!is_resource($resource) || get_resource_type($resource) !== 'gd') {
    return '';
  }
  // From: https://stackoverflow.com/a/22266437
  ob_start();
  switch ($format) {
    case 'jpg':
    case 'jpeg':
      imagejpeg($resource);
      break;
    case 'png':
      imagepng($resource);
    case 'gif':
      imagegif($resource);
  }
  $contents = ob_get_contents();
  ob_end_clean();
  return base64_encode($contents);
}

// Called filter_input then filter_var to add keys not present in the request
// From: https://stackoverflow.com/a/40005070
function validate($type, $rules = []) {
  return filter_var_array(
      filter_input_array(
          $type, $rules, true
      ) ?? array_keys($rules),
      $rules,
      true
  );
}

$optionRules = [
  'text' => FILTER_SANITIZE_STRING,

  'drawText' => [
    'filter' => FILTER_VALIDATE_BOOLEAN,
    'options' => ['default' => false],
  ],

  'fontSize' => [
    'filter' => FILTER_VALIDATE_FLOAT,
    'options' => ['default' => 10, 'min_range' => 0],
  ],

  'orientation' => [
    'filter' => FILTER_VALIDATE_INT,
    'options' => ['default' => 0, 'min_range' => 0, 'max_range' => 359],
  ],

  'factor' => [
    'filter' => FILTER_VALIDATE_FLOAT,
    'options' => ['default' => 1],
  ],

  'barHeight' => [
    'filter' => FILTER_VALIDATE_INT,
    'options' => ['default' => 50, 'min_range' => 0],
  ],

  'withQuietZones' => [
    'filter' => FILTER_VALIDATE_BOOLEAN,
    'options' => ['default' => false],
  ],

  'withChecksum' => [
    'filter' => FILTER_VALIDATE_BOOLEAN,
    'options' => ['default' => false],
  ],
];

$renderRules = [
  'height' => [
    'filter' => FILTER_VALIDATE_INT,
    'options' => ['default' => 0, 'min_range' => 0],
  ],

  'width' => [
    'filter' => FILTER_VALIDATE_INT,
    'options' => ['default' => 0, 'min_range' => 0],
  ],
];

$options = array_merge(validate(INPUT_GET, $optionRules), [
  'font' => 'webfonts/opensans-regular-webfont.woff',
  'withChecksum' => true,
  'withChecksumInText' => true,
  // 'withBorder' => false,
  // 'stretchText' => false,
]);

$renderOptions = array_merge(validate(INPUT_GET, $renderRules), [
  // 'topOffset' => 0,
  // 'leftOffset' => 0,
  // 'verticalPosition' => 'top',
  // 'horizontalPosition' => 'left',
  // 'automaticRenderError' => true,
]);

// Actually render barcode (set the Content-Type header and return the resource)
// Barcode::render('code128', 'pdf', $options, $renderOptions);

// Generate a GD resource of the image
$res = Barcode::draw('code128', 'image', $options);

// Get the base64 of the resource and load in the SimpleImage class
$img = new SimpleImage();
$img->load_base64(base64_resource($res, 'png'));

// Shrink by both the width and height, if requested
if ($renderOptions['width']) {
  $img->best_fit($renderOptions['width'], $img->get_height());
}
if ($renderOptions['height']) {
  $img->best_fit($img->get_width(), $renderOptions['height']);
}

// Actually render the image (set the Content-Type header and output the image)
$img->output();
