<?php
namespace Craft;

class AmVarnishCacheControlService extends BaseApplicationComponent
{
	private $settings;

	public function __construct()
	{
		$this->settings = craft()->plugins->getPlugin('amvarnishcachecontrol')->getSettings();
	}

	private function _isPageCacheable()
	{
		$uncacheables = !empty($this->settings['excludefromCache']) ? explode("\n", $this->settings['excludefromCache']) : array();
		$path = craft()->request->getPath();

		if (count($uncacheables) > 0)
		{
			foreach ($uncacheables as $uncacheable)
			{
				if ($this->startsWith($path, $uncacheable))
				{
					return false;
				}
			}
		}

		return true;
	}

	private function startsWith($haystack, $needle)
	{
    	// search backwards starting from haystack length characters from the end
    	return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
	}

	/**
	 * Sends the headers to control the cache.
	 */
	public function loadHeaders()
	{		
		if ($this->_isPageCacheable())
		{
			$ttl = is_numeric($this->settings['standardTtl']) ? $this->settings['standardTtl'] : 86400;
			$expiry = time() + $ttl;

			header('Cache-Control: max-age=' . $ttl);
			header('Pragma: cache');
			header('Expires: ' . date('D, d M Y H:i:s', $expiry) . ' GMT');
		}
	}

	public function purgeCache()
	{
		$curl = curl_init($this->settings['varnishServer']);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST,' PURGE');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if (curl_exec($curl) === false)
		{
			echo curl_error($curl);
		}
		curl_close($curl);
	}
}