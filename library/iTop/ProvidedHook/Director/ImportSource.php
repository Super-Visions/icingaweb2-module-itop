<?php

namespace Icinga\Module\iTop\ProvidedHook\Director;

use Icinga\Module\Director\Hook\ImportSourceHook;
use Icinga\Module\Director\Web\Form\QuickForm;

class ImportSource extends ImportSourceHook
{

	/**
	 * Returns an array containing importable objects
	 *
	 * @return array
	 */
	public function fetchData()
	{
		// TODO: Implement fetchData() method.
	}

	/**
	 * Returns a list of all available columns
	 *
	 * @return array
	 */
	public function listColumns()
	{
		// TODO: Implement listColumns() method.
	}

	/**
	 * Add iTop specific fields to the form
	 *
	 * @param  QuickForm $form QuickForm that should be extended
	 * @return QuickForm
	 */
	public static function addSettingsFormFields(QuickForm $form)
	{
		// TODO: Add iTop resource element

		// TODO: Load queries from iTop

		$form->addElement('select', 'query', array(
			'label' => $form->translate('Query'),
			'multiOptions' => array(
				1 => 'Query 1',
				2 => 'Query 2',
				null => $form->translate('Custom…'),
			),
			'required' => false,
		));

		$form->addElement('radio', 'no_localize', array(
			'label' => $form->translate('Localize'),
			'multiOptions' => array($form->translate('Yes'), $form->translate('No')),
			'required' => true,
		));

		return $form;
	}
}
