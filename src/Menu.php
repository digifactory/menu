<?php

namespace Spatie\Menu;

use ReflectionFunction;
use ReflectionParameter;
use Spatie\Menu\Items\Link;
use Spatie\Menu\Items\RawHtml;
use Spatie\Menu\Traits\Collection;
use Spatie\Menu\Traits\HtmlElement;
use function Spatie\Menu\callable_parameter_types;

class Menu
{
    use HtmlElement, Collection;

    private function __construct(Item ...$items)
    {
        $this->items = $items;
    }

    /**
     * @param array $items
     *
     * @return static
     */
    public static function create(array $items = [])
    {
        return new static(...$items);
    }

    /**
     * @param string $text
     * @param string $url
     *
     * @return static
     */
    public function addLink(string $text, string $url)
    {
        return $this->addItem(Link::create($text, $url));
    }

    /**
     * @param string $html
     *
     * @return static
     */
    public function addHtml(string $html)
    {
        return $this->addItem(RawHtml::create($html));
    }

    /**
     * @param callable $callable
     *
     * @return static
     */
    public function manipulate(callable $callable)
    {
        $type = $this->getTypeToManipulate($callable);

        foreach($this->items as $item) {

            if ($type && ! $item instanceof $type) {
                continue;
            }

            $callable($item);
        }

        return $this;
    }

    /**
     * @param callable $callable
     *
     * @return static
     */
    public function setActive(callable $callable)
    {
        $type = $this->getTypeToManipulate($callable);

        foreach($this->items as $item) {

            if ($type && ! $item instanceof $type) {
                continue;
            }

            if ($callable($item)) {
                $item->setActive();
            }
        }

        return $this;
    }

    /**
     * @param callable $callable
     *
     * @return string|null
     */
    protected function getTypeToManipulate(callable $callable)
    {
        $reflection = new ReflectionFunction($callable);

        $parameterTypes = array_map(function (ReflectionParameter $parameter) {
            return $parameter->getClass() ? $parameter->getClass()->name : null;
        }, $reflection->getParameters());

        return $parameterTypes[0] ?? null;
    }

    /**
     * @return string
     */
    public function render() : string
    {
        return $this->renderHtml('ul', $this->mapAndJoin(function (Item $item) {
            return $item->render();
        }));
    }
}