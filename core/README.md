## How to build

Run ```./build.sh``` script in **core** folder. It will generate new sources for **legacy** and **composer** folders.

## Making custom *.php_ files

  Extension ```*.php_``` is used with builder script for combining different versions of source code.
  This file format uses ```#ifdef``` insertions for dividing code into parts.

#### Syntax

  ``` php
  <?php
  #path PHP53
  #path PHP52

  #ifdef PHP52
  // php 52 compatible code
  #elsif
  // php 52 compatible code
  #endif
  ?>
  ```

  Both ```#path``` fields must be placed in second and third line of file after ```<php?```.
  They are required in order to define relative path for each version of php.
  If some files aren't used in some php version 'none' can be set.
  Files can contain simple php code without ```#ifdef``` insertions, such content will be rewritten without changes,
  BUT both ```#path``` must be defined.

#### Examples

  ``` php
  <?php
  #path PHP53 composer/lib/Pubnub/
  #path PHP52 legacy/
  ...
  #ifdef PHP53
  ...
  #elsif
  ...
  #endif
  ...
  ?>
  ```

  ``` php
  <?php
  #path PHP53 composer/tests/
  #path PHP52 legacy/
  ...
  #ifdef PHP52
  ...
  #endif
  ...
  ?>
  ```

  ``` php
  <?php
  #path PHP53 none
  #path PHP52 php/3.4/
  ...
  //simple php code
  ...
  ?>
  ```

  ``` php
  <?php
  #path PHP53 composer/
  #path PHP52 legacy/
  ...
  //simple php code
  ...
  ?>
  ```