<?php
namespace Craft;

class AmVarnishCacheControlVariable
{
    private $plugin;

    function __construct()
    {
        $this->plugin = craft()->plugins->getPlugin('amvarnishcachecontrol');
    }

	/**
     * Plugin Name
     * Make your plugin name available as a variable
     * in your templates as {{ craft.YourPlugin.name }}
     *
     * @return string
     */
    public function getName()
    {
        return $this->plugin->getName();
    }

    /**
     * Get plugin version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->plugin->getVersion();
    }

    public function loadHeaders()
    {
        return craft()->amVarnishCacheControl->loadHeaders();
    }
}