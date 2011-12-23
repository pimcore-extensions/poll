<?php if($this->thanks): ?>
<span class="thanks"><?=$this->translate('Thank you for your vote')?></span>
<?php endif; ?>

<?php $sum = $this->question->sumResponses(); if(!$sum) $sum = 1; ?>
<?php foreach($this->question->getAnswers() as $answer): ?>
    <?php $percent = $answer->getResponses() / $sum * 100; ?>
    <div class="answer">
        <span><?=$answer->getTitle()?> (<?=$answer->getResponses()?>)</span>
        <div class="bar" data-percent="<?=round($percent, 1)?>"></div>
    </div>
<?php endforeach; ?>
