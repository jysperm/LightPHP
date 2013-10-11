<?php

trait lpPlugableHandler
{
    /** @var lpPLugin $plugin */
    protected $plugin;

    private function setPlugin(lpPLugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param string $name
     * @return lpPDOModel
     */
    protected function model($name)
    {
        return lpFactory::get($this->plugin->className("{$name}Model"));
    }

    protected function render($template, $values = [])
    {
        return lpCompiledTemplate::outputFile($this->plugin->file("template/{$template}.php"), $values);
    }
}