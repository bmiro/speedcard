<?php

function controller($args) {
  $card = t('7 piques');

  $tpl = new Template();
  $tpl->add('main', 'blocks/speedcard', array('card' => $card));
  $tpl->setTPL('page');
  return $tpl->render();
}
