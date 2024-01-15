<?php

class PluginHandler
{
    /**
     * @var PluginContext
     */
    private PluginContext $defaultContext;
    private SplObjectStorage $plugins;

    public function __construct(PluginContext $context)
    {
        $this->setDefaultContext($context);
        $this->plugins = new SplObjectStorage();
    }

    public function setDefaultContext(PluginContext $context)
    {
        $this->defaultContext = $context;
    }

    public function register(PluginAbstract $plugin)
    {
        $this->plugins->offsetSet($plugin, true);
    }

    public function disable(PluginAbstract $plugin)
    {
        $this->plugins->offsetSet($plugin, false);
    }

    public function enable(PluginAbstract $plugin)
    {
        $this->plugins->offsetSet($plugin, true);
    }

    public function run()
    {
        /** @var PluginAbstract $plugin */
        foreach ($this->plugins as $plugin) {
            $pluginEnabled = $this->plugins[$plugin];

            if (!$pluginEnabled) {
                continue;
            }

            try {
                $plugin->setContext($this->getDefaultContext());
                $plugin->run();
            } catch (Exception $e) {
                $this->getDefaultContext()->getLog()->print('error', $e->getMessage());
                $this->getDefaultContext()->getLog()->print('error', $e->getTraceAsString());
            }
        }
    }

    private function getDefaultContext(): PluginContext
    {
        return $this->defaultContext;
    }
}