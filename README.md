# Pimcore Poll Plugin #

## Introduction ##

Need to publish poll on your pimcore powered website? I think you just found
a ready-made solution :)

## Installation ##

Just like other Pimcore plugins:
  * [download plugin by composer](https://www.pimcore.org/wiki/display/PIMCORE3/Extension+management+using+Composer)
    or use [Extension Manager](https://github.com/pimcore-extensions/manager)
  * navigate to `Extras -> Extensions` in admin panel
  * enable & install plugin
  * reload admin interface

If everything went smoothly you will find `Poll` options in `Extras` menu:

![Poll admin menu extras](https://raw.github.com/pimcore-extensions/poll/master/docs/screenshots/admin_menu_extras.png)

After you created and published a poll you can place it on website using snippet:

 * Create new Poll snippet:

![Poll website snippet](https://raw.github.com/pimcore-extensions/poll/master/docs/screenshots/website_snippet.png)

*   Place snippet definition in page view script

```php
<?=$this->snippet("snippet-box")?>
```

*   Go back to admin panel and drag & drop snippet into desired document
*   Thats it!

## Features ##

*   Date range in which the poll is published
*   Single (radio) or multi (checkbox) answers
*   Polls reports as pie charts
*   One vote per 24h limit (cookie based)

You can find some screenshots [here](https://github.com/pimcore-extensions/poll/tree/master/docs/screenshots)

## Todo ##
*   Current version allows to publish single poll at a time.
    I have plan to extend plugin with placeholder snippet that will allow
    you to choose one of the active polls.

## Changelog ##
 * 2015-02-21   1.0.2   composer.json, publish on [Packagist](https://packagist.org/packages/pimcore-extensions/poll),
   compatibility with pimcore >= 2.3.0
 * 2011-12-27   1.0.1   Added poll visits counter
 * 2011-12-24   1.0.0   First release
