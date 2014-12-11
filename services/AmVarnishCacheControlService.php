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

	private function getRequestUriFromUrl($url)
	{
		// Remove http(s) + host + optional port from url
		return preg_replace('/http(s|):\/\/' . $_SERVER['HTTP_HOST'] . '(:([0-9]+))*/', '', $url);
	}

	private function entryInSectionPurgeGlobally(EntryModel $entry)
	{
		return in_array($entry->section->id, $this->settings['purgeGlobalOnSaveEntryFromSection']);
	}

	public function purgeCacheForEntry(EntryModel $entry)
	{
		if (!empty($entry->url) && !$this->entryInSectionPurgeGlobally($entry))
		{
			$this->purgeCache($entry->url);
		}
		else
		{
			$this->purgeCache();
		}
	}

	public function purgeCache($url = null)
	{
		$uri = !empty($url) ? $this->getRequestUriFromUrl($url) : $this->getRequestUriFromUrl(craft()->siteUrl);

		$curl = curl_init($this->settings['varnishServer'] . $uri);
		$request_headers = array('Host: ' . $_SERVER['HTTP_HOST']);

		curl_setopt($curl, CURLOPT_CUSTOMREQUEST,'BAN');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

		curl_exec($curl);

		curl_close($curl);
	}
}