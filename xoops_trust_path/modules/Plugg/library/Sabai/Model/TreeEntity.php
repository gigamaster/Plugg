<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Model
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

require_once 'Sabai/Model/Entity.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Model
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
abstract class Sabai_Model_TreeEntity extends Sabai_Model_Entity
{

    public $left;
    public $right;
    protected $_parentsCount; // Sabai_Model_EntityCollection_Decorator_ParentEntitiesCount
    protected $_descendantsCount; // Sabai_Model_EntityCollection_Decorator_DescendantEntitiesCount
    protected $_childrenCount; // Sabai_Model_EntityCollection_Decorator_ChildEntitiesCount

    /**
     * Constructor
     *
     * @param string $name
     * @param Sabai_Model $model
     * @return Sabai_Model_TreeEntity
     */
    protected function __construct($name, Sabai_Model $model)
    {
        parent::__construct($name, $model);
    }

    public function _fetchChildren()
    {
        if (!isset($this->_objects['Children'])) {
            $this->_objects['Children'] = $this->_getRepository()->fetchByParent($this->id);
        }

        return $this->_objects['Children'];
    }

    /**
     * Retrieves all child entities of this entity
     *
     * @return Sabai_Model_EntityCollection
     */
    public function getDescendants()
    {
        if (!isset($this->_objects['Descendants'])) {
            $this->_objects['Descendants'] = $this->_getRepository()->fetchDescendantsByParent($this->id);
        }

        return $this->_objects['Descendants'];
    }

    /**
     * Retrieves all child entities of this entity
     *
     * @return Sabai_Model_EntityCollection
     */
    public function getDescendantsAsTree()
    {
        if (!isset($this->_objects['DescendantsTree'])) {
            $this->_objects['DescendantsTree'] = $this->_getRepository()->fetchDescendantsAsTreeByParent($this->id);
        }

        return $this->_objects['DescendantsTree'];
    }

    public function setParentsCount($count)
    {
        $this->_parentsCount = $count;
    }

    public function setChildrenCount($count)
    {
        $this->_childrenCount = $count;
    }

    public function setDescendantsCount($count)
    {
        $this->_descendantsCount = $count;
    }

    /**
     * Gets the number of all first-level child entities
     *
     * @return int
     */
    public function getChildrenCount()
    {
        if (!isset($this->_childrenCount)) {
            $this->_childrenCount = $this->_getRepository()->countByParent($this->id);
        }

        return $this->_childrenCount;
    }

    /**
     * Gets the number of all (or first-level) child entities
     *
     * @return int
     */
    public function getDescendantsCount()
    {
        if (!isset($this->_descendantsCount)) {
            $this->_descendantsCount = $this->_getRepository()->countDescendantsByParent($this->id);
        }

        return $this->_descendantsCount;
    }

    /**
     * Retrieves all parent entities of this entity
     *
     * @return Sabai_Model_EntityCollection
     */
    public function getParents()
    {
        if (!isset($this->_objects['Parents'])) {
            $this->_objects['Parents'] = $this->_getRepository()->fetchParents($this->id);
        }

        return $this->_objects['Parents'];
    }

    /**
     * Gets the number of all parent entities for this entity
     *
     * @return int
     */
    public function getParentsCount()
    {
        if (!isset($this->_parentsCount)) {
            $this->_parentsCount = $this->_getRepository()->countParents($this->id);
        }

        return $this->_parentsCount;
    }

    /**
     * Creates a new child entity
     *
     * @return mixed Sabai_Model_TreeEntity on success, false on failure
     */
    public function createChild()
    {
        if (!$this->id) {
            trigger_error(
                sprintf('Cannot create a new child entity for a non-existent %s entity', $this->getName()),
                E_USER_WARNING
            );

            return false;
        }
        $child = $this->_model->create($this->getName());
        $child->assignParent($this);

        return $child;
    }

    /**
     * Checks if the entity is a leaf node in the tree
     *
     * @return bool
     */
    public function isLeaf()
    {
        return ($this->left + 1) == $this->right;
    }
}