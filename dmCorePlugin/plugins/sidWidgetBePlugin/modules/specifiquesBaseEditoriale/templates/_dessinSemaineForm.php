<?php

echo

$form->renderGlobalErrors(),
 _tag('div', $form['title']->renderRow() .
        $form['effect']->renderRow()
);


