<?php

namespace plokko\MenuBuilder;

use Auth;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use plokko\MenuBuilder\Contracts\MenuInterface;
use plokko\MenuBuilder\Traits\MenuCallbackTrait;

/**
 * @method $this icon(string $value) Set item icon
 * @method $this color(string $value) Set item text color
 * @method $this class(string $value) Set menu item CSS class
 *
 */
class MenuItem implements MenuInterface, Arrayable, JsonSerializable
{
    use MenuCallbackTrait;

    protected
        /**@var MenuBuilder* */
        $root,
        /**@var MenuInterface* */
        $parent,
        $name,
        $label = null,
        $url = null,
        $visibility = true,
        $conditions = [],
        $attr = [],
        /** @var MenuItem[] */
        $items = [];

    function __construct(MenuBuilder $root, MenuInterface $parent, $name)
    {
        $this->parent = $parent;
        $this->name = $name;
        $this->root = $root;
    }

    public function subItem($name, $forceTwoLevels = true): MenuItem
    {
        if ($forceTwoLevels && $this->parent instanceof MenuItem) {
            return $this->parent->subItem($name);
        } else {
            if (!isset($this->items[$name]))
                $this->items[$name] = new MenuItem($this->root, $this, $name);
            return $this->items[$name];
        }
    }

    /**
     * @param string $key
     * @param array $replace
     * @return $this
     */
    public function _label($key, array $replace = [])
    {
        return $this->label(trans($key, $replace));
    }

    /**
     * @param string $value
     * @return $this
     */
    public function label($value)
    {
        $this->label = $value;
        return $this;
    }

    /**
     * @param $name
     * @param $parameters
     * @param $absolute
     * @return $this
     */
    function route($name, $parameters = [], $absolute = true)
    {
        return $this->url(route($name, $parameters, $absolute));
    }

    /**
     * @param string $value
     * @return $this
     */
    public function url($value)
    {
        $this->url = $value;
        return $this;
    }

    /**
     * @param boolean|Closure $visible
     * @return $this
     */
    function visible($visible)
    {
        $this->visibility = $visible;
        return $this;
    }

    function __call($name, $arguments)
    {
        if (in_array($name, ['color', 'icon', 'class'])) {
            return $this->attr($name, $arguments[0]);
        }
        return $this;
    }

    /**
     * Set an attribute value
     * @param string $name
     * @param null|mixed $value
     * @return $this
     */
    function attr($name, $value)
    {
        if ($value === null)
            unset($this->attr[$name]);
        else
            $this->attr[$name] = $value;
        return $this;
    }

    /**
     * @return array|null
     * @internal
     */
    function _toMenuItem(array $opt = [], $level = 0)
    {
        if (!$this->isVisible()) {
            return null;
        }

        $data = array_merge($this->attr, [
            'url' => $this->url,
            'name' => empty($this->label) ?
                (empty($opt['label']) ? $this->root->__($this->name) : $opt['label']) :
                $this->label,
        ]);

        if (count($this->items) > 0) {
            $data['items'] = [];
            foreach ($this->items as $item) {
                //$opt = [];
                $e = $item->_toMenuItem($opt, $level + 1);
                if ($e) {
                    $data['items'][] = $e;
                }
            }
        }

        return $data;
    }

    /**
     * @return bool
     */
    function isVisible()
    {
        if (!empty($this->conditions['guest'])) {
            if ($this->conditions['guest'] xor Auth::guest()) {
                return false;
            }
        }
        if (!$this->checkRoles()) {
            return false;
        }

        foreach (['hasAnyPermissions', 'hasAllPermissions'] as $method) {
            if (
                !empty($this->conditions[$method])
                && !Auth::user()->$method(...$this->conditions[$method])
            ) {
                return false;
            }
        }

        if (!empty($this->conditions['hasRoleOrPermission'])) {
            if (! Auth::user()->hasAnyRole($this->conditions['hasRoleOrPermission']) && ! Auth::user()->hasAnyPermission($this->conditions['hasRoleOrPermission'])) {
                return false;
            }
        }

        if ($this->visibility instanceof Closure) {
            return $this->visibility($this);
        }

        if (!$this->_checkPolicy($this->name)) {
            return false;
        }

        return $this->visibility;
    }

    /**
     * Set visibility if user is logged
     * @param null|boolean $visible true if ONLY visible for guest, false if visible only if logged, NULL if always visible
     * @return $this
     */
    function guest($visible = true)
    {
        $this->conditions['guest'] = $visible;
        return $this;
    }

    protected function checkRoles()
    {
        if (empty($this->conditions['roles'])) {
            return true;
        }
        return MenuBuilder::_checkRoles($this->conditions['roles']);
    }

    /**
     * @param string|string[] $roles
     * @return $this
     */
    function hasAllRoles($roles)
    {
        if (!is_array($roles))
            $roles = func_num_args();
        return $this->hasRoles([$roles]);
    }

    /**
     * Set item visibility by user roles
     * example:
     *  - hasRole('role1') - visible only if user has 'role1'
     *  - hasRole('role1','role2') or hasRole(['role1','role2']) - visible only if user has 'role1' OR 'role2'
     *  - hasRole([['role1','role2'],]) - visible only if user has 'role1' AND 'role2'
     *  - hasRole(['roleX',['role1','role2'],['roleA','roleB']]) - visible only if user has 'roleX' OR ('role1' AND 'role2') OR ('roleA' AND 'roleB')
     * @param string|array $roles
     * @return $this
     */
    function hasRoles($roles)
    {
        if (!is_array($roles))
            $roles = func_get_args();
        $this->conditions['roles'] = $roles;
        return $this;
    }

    /**
     * Close item definition and return parent
     * @return MenuInterface
     */
    function end()
    {
        return $this->parent;
    }

    /**
     * Sets an array of proprieties for the menu item
     * @param array $values Key=>value of proprieties to set
     * @return $this
     */
    public function apply(array $values): MenuItem
    {
        foreach ($values as $k => $v) {
            $this->$k($v);
        }
        return $this;
    }

    public function hasAllPermissions($permission, $guardName = null): MenuItem
    {
        $this->conditions['hasAllPermissions'] = func_get_args();
        return $this;
    }

    public function hasAnyPermissions($permission, $guardName = null): MenuItem
    {
        $this->conditions['hasAnyPermissions'] = func_get_args();
        return $this;
    }

    /**
     * @param string|array $roleOrPermission
     * @return $this
     */
    public function hasRoleOrPermission($roleOrPermission): MenuItem
    {
        $rolesOrPermissions = is_array($roleOrPermission)
            ? $roleOrPermission
            : explode('|', $roleOrPermission);

        $this->conditions['hasRoleOrPermission'] = $rolesOrPermissions;

        return $this;
    }

    public function can($abilities, $arguments = []): MenuItem
    {
        $this->conditions['can']  = func_get_args();
        return $this;
    }
}
