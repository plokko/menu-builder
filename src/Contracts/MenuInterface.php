<?php

namespace plokko\MenuBuilder\Contracts;

use Closure;
use plokko\MenuBuilder\MenuBuilder;
use plokko\MenuBuilder\MenuItem;

interface MenuInterface
{
    public function item($name): MenuItem;
    public function category($name): MenuItem;

    /**
     * Set root translation for menu item, if null translation will be disabled
     * @param string|null $transFile
     * @return MenuInterface
     */
    public function useTrans($transFile): MenuInterface;

    /**
     * Remove a menu item by name
     * @param string $name menu item name
     * @return MenuBuilder
     */
    public function removeItem($name): MenuBuilder;

    public function clear(): MenuBuilder;

    /**
     * Initialize the menu with an array of items
     * @param array $array Array of menu items
     * @return MenuBuilder
     */
    public function fromArray(array $array): MenuBuilder;

    /**
     * Apply function body only if condition is met
     * @param Closure|boolean $condition
     * @param Closure(MenuBuilder $menu) $fn
     * @return MenuInterface
     */
    public function when($condition, Closure $fn): MenuInterface;

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
    public function whenHasRole($roles, Closure $fn): MenuInterface;

    /**
     * Check user guard.
     *
     * @return $this
     */
    //public function can($abilities, $arguments = []): MenuItem;
}
