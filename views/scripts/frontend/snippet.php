<?php if (Poll_Plugin::isInstalled() && Poll_Question::hasCurrent()): ?>
    <div class="page-header">
        <h3><?=$this->input('page-header')?></h3>
    </div>
    <?=$this->action('current', 'frontend', 'Poll', array(
        'omitJquery' => false,   // if you already using jQuery in your project
        'omitJqueryUi' => false, // same as above with jQuery UI
        'omitStyles' => false,   // if you already using custom jQuery UI skin
    ))?>
<?php endif; ?>
