# Pimcore Poll Plugin #

## Introduction ##

Need to publish poll on your pimcore powered website? I think you just found
a ready-made solution :)

## Installation ##

Just like other Pimcore plugins:

*   clone plugin files into `/plugin/Poll` directory
*   navigate to `Extras -> Extensions -> Manage Extensions` in admin panel
*   enable & install plugin
*   reload admin interface

If everything went smoothly you will find `Poll` options in `Extras` menu.

After you created and published a poll you can place it on website using simple snippet:

*   Create new empty snippet in `Documents` and set controller/action on `settings` tab:

![Poll website snippet](https://raw.github.com/rafalgalka/pimcore-poll-plugin/develop/docs/screenshots/website_snippet.png)

*   Create `SnippetController` class with empty `pollAction` method in `/website/controllers/`:

```php
class SnippetController extends Website_Controller_Action
{
    public function pollAction()
    {
    }
}
```

*   Put this piece of code into `/website/views/scripts/snippet/poll.php`

```php
<?php if (Poll_Plugin::isInstalled() && Poll_Question::hasCurrent()): ?>
    <div class="page-header">
        <h3><?=$this->input('page-header')?></h3>
    </div>
    <?=$this->action('current', 'frontend', 'Poll', array(
        'omitJquery' => true,   // if you already using jQuery in your project
        'omitJqueryUi' => true, // same as above with jQuery UI
        'omitStyles' => true,   // if you already using custom jQuery UI skin
    ))?>
<?php endif; ?>
```

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

You can find some screenshots [here](https://github.com/rafalgalka/pimcore-poll-plugin/tree/develop/docs/screenshots)

## Todo ##
*   At this version it's possible to publish single poll at a time.
    I have plan to extend plugin with placeholder snippet that will allow
    you to choose one of the active polls.

## Notes ##

### Pimcore plugin menu ###
If you merge [this commit](http://bit.ly/sIROeN) into your project Poll options
will appear in separate `Plugins` menu in main pimcore toolbar:
![Pimcore Plugins menu](https://raw.github.com/rafalgalka/pimcore-poll-plugin/develop/docs/screenshots/admin_menu_plugins.png)
