<?php

use Nette\Application\UI,
	Nette\Security as NS;


class SignPresenter extends BasePresenter
{

	protected function createComponentSignInForm()
	{
		$form = new UI\Form;
		$form->addText('username', 'Meno:')
			->setRequired('Prosím zadaj meno.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Prosím zadaj heslo.');

		$form->addCheckbox('remember');

		$form->addSubmit('send', 'Prihlásiť');

		$form->onSuccess[] = callback($this, 'signInFormSubmitted');
		return $form;
	}

	public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
			if ($values->remember) {
				$this->getUser()->setExpiration('+ 14 days', FALSE);
			} else {
				$this->getUser()->setExpiration('+ 20 minutes', TRUE);
			}
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('List:view');

		} catch (NS\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Bol si odhlásený.', 'success');
		$this->redirect('in');
	}

}
