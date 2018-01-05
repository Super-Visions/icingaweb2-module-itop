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
		$form->getElement($name)->setDescription($form->translate('Select which iTop instance to connect to. The content of this list depends on your configuration in "resources.ini"'));
	}

	/**
	 * @param string $resourceName
	 * @return array
	 */
	public static function enumQueries($resourceName)
	{
		$itop = new RestApiClient($resourceName);

		$response = $itop->doRestCall('core/get', array(
			'class' => 'QueryOQL',
			'key' => 'SELECT QueryOQL WHERE fields != ""',
			'output_fields' => 'name',
		));

		$queries = array();
		foreach($response->objects as $object)
		{
			$queries[$object->key] = $object->fields->name;
		}

		return $queries;
	}
}
