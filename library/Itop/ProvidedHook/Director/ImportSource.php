<?php

namespace Icinga\Module\Itop\ProvidedHook\Director;

use Icinga\Module\Director\Hook\ImportSourceHook;
use Icinga\Module\Director\Web\Form\QuickForm;
use Icinga\Module\Itop\RestApiClient;
use Icinga\Module\Itop\Util;

class ImportSource extends ImportSourceHook
{
	/**
	 * @var array
	 */
	protected $cachedData = array();

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'iTop Export';
	}

	/**
	 * Returns an array containing importable objects
	 *
	 * @return array
	 */
	public function fetchData()
	{
		if (empty($this->cachedData))
		{
			$itop = new RestApiClient($this->getSetting('resource'));

			$query = (int) $this->getSetting('query');
			$no_localize = (int) $this->getSetting('no_localize');

			if ($query > 0) $this->cachedData = $itop->doExport($query, $no_localize);
			else $this->cachedData = $itop->doExport($this->getSetting('expression'), $no_localize, $this->getSetting('fields'));
		}

		return $this->cachedData;
	}

	/**
	 * Returns a list of all available columns
	 *
	 * @return array
	 */
	public function listColumns()
	{
		return array_keys((array) current($this->fetchData()));
	}

	/**
	 * Add iTop specific fields to the form
	 *
	 * @param  QuickForm $form QuickForm that should be extended
	 * @return QuickForm
	 */
	public static function addSettingsFormFields(QuickForm $form)
	{
		Util::addItopResourceFormElement($form, 'resource', true);

		// Only show rest of form if a resource has been selected
		$resourceName = $form->getSentOrObjectSetting('resource');
		if(empty($resourceName)) return $form;

		// Load and present available queries
		$queries = Util::enumQueries($resourceName);
		$form->addElement('select', 'query', array(
			'label' => $form->translate('Query'),
			'multiOptions' => $form->optionalEnum($queries + array($form->translate('Custom…'))),
			'class' => 'autosubmit',
			'required' => true,
		));

		// Add additional fields when selected custom query
		$query = $form->getSentOrObjectSetting('query');
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
