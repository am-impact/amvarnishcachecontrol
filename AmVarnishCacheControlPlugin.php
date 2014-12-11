<?php
namespace Craft;

class AmVarnishCacheControlPlugin extends BasePlugin
{
	public function init()
	{
		craft()->on('elements.saveElement', function(Event $event)
		{
			if (get_class($event->params['element']) == 'Craft\EntryModel')
			{
			    craft()->amVarnishCacheControl->purgeCacheForEntry($event->params['element']);
			}
		});
	}


	public function getName()
	{
		 return 'a&m impact Varnish cache control';
	}

	public function getVersion()
	{
		return '0.1';
	}

	public function getDeveloper()
	{
		return 'a&m impact';
	}

	public function getDeveloperUrl()
	{
		return 'http://www.am-impact.nl';
	}

	protected function defineSettings()
	{
		return array(
			'excludefromCache' => array(AttributeType::String),
			'standardTtl' => array(AttributeType::Number),
			'varnishServer' => array(AttributeType::String),
			'purgeGlobalOnSaveEntryFromSection' => array(AttributeType::Mixed),
		);
	}

	private function getSections()
	{
		$sections = array();
		$craft_sections = craft()->sections->getAllSections();

		foreach ($craft_sections as $section)
		{
			$sections[$section->id] = $section->name;
		}

		return $sections;
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('amvarnishcachecontrol/settings', array(
			'settings' => $this->getSettings(),
			'sections' => $this->getSections()
		));
	}
}