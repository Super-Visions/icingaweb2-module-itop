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
			'multiOptions' => $form->optionalEnum(array(
				1 => 'Query 1',
				2 => 'Query 2',
			), $form->translate('Custom…')),
			'class' => 'autosubmit',
			'required' => false,
		));

		// Add additional fields when selected custom query
		$query = $form->getSentValue('query', $form->getValue('query'));
		if(empty($query))
		{
			$form->addElement('textarea', 'expression', array(
				'label' => $form->translate('Expression'),
				'rows' => 10,
				'required' => true,
			));

			$form->addElement('text', 'fields', array(
				'label' => $form->translate('Fields'),
				'required' => true,
			));
		}

		$form->addElement('radio', 'no_localize', array(
			'label' => $form->translate('Localize'),
			'multiOptions' => array($form->translate('Yes'), $form->translate('No')),
			'required' => true,
		));

		return $form;
	}
}
