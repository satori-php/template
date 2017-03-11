<?php

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2017 Yuriy Davletshin
 * @license   MIT
 */

declare(strict_types=1);

namespace Satori\Template;

/**
 * Abstract template class
 */
abstract class AbstractTemplate
{
    /**
     * @var string Scopename of common variables.
     */
    const COMMON_NAME = 'common';

    /**
     * @var string Layout name.
     */
    const LAYOUT_NAME = 'layout';

    /**
     * @var string Head assets name.
     */
    const HEAD_ASSETS_NAME = 'head';

    /**
     * @var string End assets name.
     */
    const END_ASSETS_NAME = 'end';

    /**
     * @var string File extension of templates.
     */
    const FILE_EXTENSION = 'php';

    /**
     * @var string Suffix for blocks.
     */
    const BLOCK_PROPERTY_SUFFIX = 'Block';

    /**
     * @var string Suffix for variables.
     */
    const VARS_PROPERTY_SUFFIX = 'Vars';

    /**
     * @var string Path to templates.
     */
    protected $path;

    /**
     * @var array Additional parameters.
     */
    protected $params;

    /**
     * Constructor.
     *
     * @param string $path   The path for templates.
     * @param array  $params Additional parameters.
     */
    public function __construct(string $path, array $params)
    {
        $this->path = $path;
        $this->params = $params;
    }

    /**
     * Initializes a template.
     *
     * @param array $data The data.
     */
    abstract protected function init(array $data);

    /**
     * Renders a content.
     *
     * @param array $data The data.
     *
     * @return string Rendered content.
     */
    public function render(array $data = null): string
    {
        $this->init($data ?? []);

        return trim($this->make(static::LAYOUT_NAME)) . PHP_EOL;
    }

    /**
     * Renders head assets.
     *
     * @return string Rendered assets.
     */
    public function head(): string
    {
        $property = static::HEAD_ASSETS_NAME . static::BLOCK_PROPERTY_SUFFIX;

        return isset($this->$property) ? trim($this->make(static::HEAD_ASSETS_NAME)) . PHP_EOL : PHP_EOL;
    }

    /**
     * Renders end assets.
     *
     * @return string Rendered assets.
     */
    public function end(): string
    {
        $property = static::END_ASSETS_NAME . static::BLOCK_PROPERTY_SUFFIX;

        return isset($this->$property) ? trim($this->make(static::END_ASSETS_NAME)) . PHP_EOL : PHP_EOL;
    }

    /**
     * Renders a partition.
     *
     * @param string $block The partition name.
     * @param array  $vars  Partition variables.
     *
     * @return string Rendered content.
     */
    public function inset(string $block, array $vars = null): string
    {
        return trim($this->make($block, $vars)) . PHP_EOL;
    }

    /**
     * Renders a partition if condition is true.
     *
     * @param bool   $condition The condition.
     * @param string $block     The partition name.
     * @param array  $vars      Partition variables.
     *
     * @return string Rendered content.
     */
    public function insetIf(bool $condition, string $block, array $vars = null): string
    {
        return $condition ? trim($this->make($block, $vars)) . PHP_EOL : PHP_EOL;
    }

    /**
     * Renders a list.
     *
     * @param array  $collection The collection.
     * @param string $block      The partition name.
     * @param string $emptyBlock The partition name for empty collection.
     *
     * @return string Rendered items.
     */
    public function loop(array $collection, string $block, string $emptyBlock = null): string
    {
        if (empty($collection)) {
            return isset($emptyBlock) ? trim($this->make($emptyBlock)) . PHP_EOL : PHP_EOL;
        } else {
            $items = '';
            foreach ($collection as $key => $item) {
                $items .= rtrim($this->make($block, ['key' => $key, 'item' => $item]));
            }

            return ltrim($items) . PHP_EOL;
        }
    }

    /**
     * Makes a block.
     *
     * @param string $block The partition name.
     * @param array  $vars  Partition variables.
     *
     * @return string Rendered content.
     */
    private function make(string $block, array $vars = null): string
    {
        $commonVars = static::COMMON_NAME . static::VARS_PROPERTY_SUFFIX;
        $blockVars = $block . static::VARS_PROPERTY_SUFFIX;
        $allVars = [];
        if (isset($this->$commonVars) && is_array($this->$commonVars)) {
            $allVars = $this->$commonVars;
        }
        if (isset($this->$blockVars) && is_array($this->$blockVars)) {
            $allVars += $this->$blockVars;
        }
        if (isset($vars)) {
            $allVars += $vars;
        }
        $file = $this->path . $this->{$block . static::BLOCK_PROPERTY_SUFFIX} . '.' . static::FILE_EXTENSION;

        $localScope = function ($vars, $file) {
            ob_start();
            extract($vars);
            require $file;
            $_ = isset($_) ? str_pad('', $_) : '';

            return str_replace(PHP_EOL, PHP_EOL . $_, PHP_EOL . ob_get_clean());
        };

        return $localScope($allVars, $file);
    }
}
