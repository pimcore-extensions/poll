<?php if($this->question instanceof Poll_Question): ?>
    <?php if (!$this->getParam('omitJquery')): ?>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <?php endif; ?>
    <?php if (!$this->getParam('omitJqueryUi')): ?>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
    <?php endif; ?>
    <?php if (!$this->getParam('omitStyles')): ?>
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/black-tie/jquery-ui.css" type="text/css" charset="utf-8" />
    <?php endif; ?>
    <script type="text/javascript" src="/plugins/Poll/static/js/frontend/poll.js"></script>

    <div class="poll-container" data-voted="<?=$this->voted?>">
        <h3><?=$this->question->getTitle()?></h3>
        <img class="loader" style="display:none;" src="/plugins/Poll/static/img/ajax-loader.gif"/>
        <form action="/plugin/Poll/frontend/response" method="post">
            <?php $type = ($this->question->getMultiple()) ? 'checkbox' : 'radio'?>
            <div class="error invalid" style="display:none;">
            <?=($type == $this->question->getMultiple())
                ? $this->translate('Choose one (or more) options first')
                : $this->translate('Choose one option first')
            ?>
            </div>
            <div class="error request" style="display:none;">
            <?=$this->translate('Sorry, something went wrong. Refresh this page and try again.')?>
            </div>
            <ul class="inputs-list clearfix">
            <?php foreach($this->question->getAnswers() as $answer):?>
            <li class="answer">
                <label>
                    <input type="<?=$type?>" name="answer[]" value="<?=$answer->getId()?>" id="answer-<?=$answer->getId()?>" />
                    <span><?=$answer->getTitle()?></span>
                </label>
            </li>
            <?php endforeach; ?>
            </ul>
            <input type="hidden" name="question" value="<?=$this->question->getId()?>" />
            <button type="submit" class="btn">
                <?=$this->translate('Send vote')?>
            </button>
        </form>
        <div class="responses" style="display:none;"></div>
    </div>
<?php endif; ?>
