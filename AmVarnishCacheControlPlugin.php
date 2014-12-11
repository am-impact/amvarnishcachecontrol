<?php
namespace Craft;

class AmVarnishCacheControlPlugin extends BasePlugin
{
	public function init()
	{
		craft()->on('entries.saveEntry', function(Event $event) {
		    craft()->amVarnishCacheControl->purgeCache();
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
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('amvarnishcachecontrol/settings', array(
			'settings' => $this->getSettings()
		));
	}
}