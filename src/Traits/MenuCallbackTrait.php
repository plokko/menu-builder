<?php

namespace plokko\MenuBuilder\Traits;

use Closure;
use plokko\MenuBuilder\Contracts\MenuInterface;
use plokko\MenuBuilder\MenuBuilder;
use plokko\MenuBuilder\MenuItem;

/**
 * @property-read MenuBuilder $parent
 */
trait MenuCallbackTrait
{
    public function item($name): MenuItem
    {
        return $this->parent->item($name);
    }

    public function removeItem($name): MenuBuilder
    {
        return $this->parent->removeItem($name);
    }

    public function clear(): MenuBuilder
    {
        return $this->parent->clear();
    }

    public function jsonSerialize()
    {
        return $this->parent->toArray();
    }

    public function toArray()
    {
        return $this->parent->toArray();
    }

    public function fromArray(array $array): MenuBuilder
    {
        return $this->parent->fromArray($array);
    }

    /**
     * @param string|null $trans_id
     * @return $this
     */
    function useTrans($trans_id = null): MenuInterface
    {
        $this->parent->useTrans($trans_id);
        return $this;
    }

    public function when($condition, Closure $fn): MenuInterface
    {
        $this->parent->when($condition, $fn);
        return $this;
    }

    public function whenHasRole($roles, Closure $fn): MenuInterface
    {
        $this->parent->whenHasRole($roles, $fn);
        return $this;
    }

}
