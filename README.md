Image Tag Gallery
=============
Image Gallery with image source as tag <gallery></gallery> and texts.
Use bootstrap 4, lightbox and jquery.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist farawayslv/yii2-taggallery "*"
```

or add

```
"farawayslv/yii2-taggallery": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \farawayslv\taggallery\GalleryTag::widget([
    'title' => (string) Gallery title. Optional,
    'description' => (string) Gallery description. Optional,
    'content' => (string) Required parameter. Text content with <gallery>/path</gallery> tags
    'inRow' => (Integer) How many images in each row. optional,
    'isDeep' => (Bool) If "true" we get images not only source root directory, but and in all child           directories,
    'pageSize' => (Integer) How many images will be show on one page. Optional,
    'imageClass' => (String) Custom class for images. Optional,
    'imageWrapClass' => (String) Custom class for images containers. Optional,
    'pagerOptions' => (Array) Standart LinkPager options (see Yii2 docs). Optional
]); ?>```
