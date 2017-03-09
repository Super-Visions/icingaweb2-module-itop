<?php

namespace Icinga\Module\Itop;

use Icinga\Module\Director\Web\Form\QuickForm;

class Util extends \Icinga\Module\Director\Util
{
	/**
	 * @param QuickForm $form
	 * @param $name
	 * @param bool $autosubmit
	 */
	public static function addItopResourceFormElement(QuickForm $form, $name, $autosubmit = false)
	{
		static::addResourceFormElement($form, $name, 'iTop');
		if($autosubmit) $form->getElement($name)->setAttrib('class', 'autosubmit');
	}
}
