<?php

namespace plokko\MenuBuilder;

use Auth;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use plokko\MenuBuilder\Contracts\MenuInterface;

class MenuBuilder implements MenuInterface, Arrayable, JsonSerializable
{
    protected
        /** @var MenuItem[] */
        $items = [],
        $trans = null;

    /**
     * Remove a menu item by name
     * @param string $name
     * @return $this
     */
    public function removeItem($name): MenuBuilder
    {
        unset($this->items[$name]);
        return $this;
    }

    /**
     * Removes ALL menu items
     * @return $this
     */
    public function clear(): MenuBuilder
    {
        $this->items = [];
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Convert to array
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->items as $name => $item) {
            $opt = [];
            if ($this->trans) {
                $opt['label'] = trans($this->trans . '.' . $name);
            }
            $e = $item->_toMenuItem($opt);
            if ($e) {
                $data[] = $e;
            }
        }
        return $data;
    }

    function useTrans($trans = null): MenuInterface
    {
        $this->trans = $trans;
        return $this;
    }

    /**
     * @param array $array Array of menu items where key are items name and value is an array of item values
     * @return $this
     */
    public function fromArray(array $array): MenuBuilder
    {
        foreach ($array as $key => $value) {
            $item = $this->item($key);
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $item->$k($v);
                }
            }
        }

        return $this;
    }

    /**
     * Adds or get a menu item
     * @param string $name
     * @return MenuItem
     */
    public function item($name): MenuItem
    {
        if (!isset($this->items[$name]))
            $this->items[$name] = new MenuItem($this, $name);
        return $this->items[$name];
    }

    /**
     * Apply function body only if user has specified roles
     * example:
     *  - hasRole('role1') - visible only if user has 'role1'
     *  - hasRole('role1','role2') or hasRole(['role1','role2']) - visible only if user has 'role1' OR 'role2'
     *  - hasRole([['role1','role2'],]) - visible only if user has 'role1' AND 'role2'
     *  - hasRole(['roleX',['role1','role2'],['roleA','roleB']]) - visible only if user has 'roleX' OR ('role1' AND 'role2') OR ('roleA' AND 'roleB')
     * @param string|array $roles
     * @param Closure(MenuBuilder $menu) $fn
     * @return MenuInterface
     */
    public function whenHasRole($roles, Closure $fn): MenuInterface
    {
        if (!is_array($roles))
            $roles = func_get_args();
        return $this->when(MenuBuilder::_checkRoles($roles), $fn);
    }

    /**
     * Apply function body only if condition is met
     * @param Closure|boolean $condition
     * @param Closure(MenuBuilder $menu) $fn
     * @return MenuInterface
     */
    public function when($condition, Closure $fn): MenuInterface
    {
        if (($condition instanceof Closure && $condition()) || $condition) {
            // Condition met
            $fn($this);
        }
        return $this;
    }

    /**
     * @private
     * @param array $roles
     * @return bool
     */
    public static function _checkRoles(array $roles): bool
    {
        if (empty($roles)) {
            return true;
        }
        $user = Auth::user();
        if (!$user)
            return false;
        foreach ($roles as $role) {
            if (is_array($role)) {
                if (!$user->hasAllRoles($role)) {
                    return true;
                }
            } elseif ($user->hasRole($role)) {
                return true;
            }
        }
        return false;
    }
}
