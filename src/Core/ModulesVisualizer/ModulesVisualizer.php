<?php

namespace ModulesPress\Core\ModulesVisualizer;

use ModulesPress\Core\AttributesScanner\AttributesScanner;
use ModulesPress\Foundation\Module\Attributes\Module;



final class ModulesVisualizer
{
    public $dependencies = [];

    public function __construct(
        private bool $enable,
        private bool $show_providers = true,
        private bool $separate_module_providers_container = false,
        private string $output_file = CM_DIR_PATH . "/modules.graph.dot"
    ) {}

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function shouldShowProviders(): bool
    {
        return $this->show_providers;
    }

    public function generateModulesGraph()
    {
        $provider_style = "shape=ellipse, style=filled, fillcolor=\"#d0f3ff\", fontcolor=\"#000\"";
        $module_style = "shape=box, style=filled, fillcolor=\"#7bff8e\", fontcolor=\"#000\"";

        $dot = "digraph G {\n";

        foreach ($this->dependencies as $module => $imports) {
            $module = basename(str_replace('\\', '/', $module));
            $dot .= "  \"$module\" [$module_style];\n";
            foreach ($imports as $import) {
                $style = $this->get_style($import, $module_style, $provider_style);
                $import = basename(str_replace('\\', '/', $import));
                $dot .= "  \"$import\" [$style];\n";
            }
        }

        if ($this->separate_module_providers_container) {
            foreach ($this->dependencies as $module => $imports) {

                $module = basename(str_replace('\\', '/', $module));

                $dot .= "  subgraph cluster_$module {\n";
                $dot .= "    label = \"$module\";\n";
                $dot .= "    style = filled;\n";
                $dot .= "    fillcolor = \"#aee1ff;\"\n";

                foreach ($imports as $import) {
                    if (class_exists($import) && !AttributesScanner::haveAttribute(Module::class, $import)) {
                        $style = $this->get_style($import, $module_style, $provider_style);
                        $import = basename(str_replace('\\', '/', $import));
                        $dot .= "  \"$import\" [$style];\n";
                    }
                }

                $dot .= "  }\n";
            }
        }

        foreach ($this->dependencies as $module => $imports) {
            $module = basename(str_replace('\\', '/', $module));
            foreach ($imports as $import) {
                $import = basename(str_replace('\\', '/', $import));
                $dot .= "  \"$module\" -> \"$import\" ;\n";
            }
        }

        $dot .= "}\n";

        file_put_contents($this->output_file, $dot);
    }

    function get_style($class, $module_style, $provider_style)
    {
        if (class_exists($class) && AttributesScanner::haveAttribute(Module::class, $class)) {
            return $module_style;
        } else {
            return $provider_style;
        }
    }
}
