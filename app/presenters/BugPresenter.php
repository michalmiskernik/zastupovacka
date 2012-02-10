<?php

use Nette\Application\UI,
  Nette\Mail\Message;

class BugPresenter extends BasePresenter {
  protected function createComponentReportForm() {
    $form = new UI\Form;

    $form->addTextarea('text', 'text:')->setRequired('prosím zadaj text');
    $form->addSubmit('send', 'poslať');

    $form->onSuccess[] = callback($this, 'processReportForm');

    return $form;
  }

  public function processReportForm(UI\Form $form) {
    $values = $form->getValues();

    $mail = new Message;
    $mail->setFrom('zastupovacka@smnd.sk')
      ->addTo('michal.miskernik@gmail.com')
      ->setSubject('bug report')
      ->setBody($values["text"])
      ->send();
    
    $this->redirect('thankyou');
  }
}
