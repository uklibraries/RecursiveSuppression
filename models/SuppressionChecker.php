<?php
/**
 * Recursive Suppression
 *
 * @copyright 2015 Michael Slone <m.slone@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Omeka\Plugins\Recursive Suppression */

class SuppressionChecker
{
    public function __construct($item)
    {
        $this->_item = $item;
        $this->_itemType = $item->getItemType()->name;
        switch($this->_itemType) {
        case "collections":
            $this->_suppressionField = 'Collection Suppressed';
            break;
        case "series":
            $this->_suppressionField = 'Series Suppressed';
            break;
        case "interviews":
            $this->_suppressionField = 'Interview Suppressed';
            break;
        }
        $this->_suppressionCount = count(metadata($this->_item, array('Item Type Metadata', $this->_suppressionField), array('no_filter' => true, 'all' => true)));
    }

    public function parents()
    {
        $subjects = get_db()->getTable('ItemRelationsRelation')->findBySubjectItemId($this->_item->id);
        $results = array();
        foreach ($subjects as $subject) {
            if ($subject->getPropertyText() !== "Is Part Of") {
                continue;
            }
            if (!($superItem = get_record_by_id('item', $subject->object_item_id))) {
                continue;
            }
            $results[] = $superItem;
        }
        return $results;
    }

    public function suppression()
    {
        $raw_suppression = metadata($this->_item, array('Item Type Metadata', $this->_suppressionField), array('no_filter' => true));
        $raw_suppression = str_replace('&quot;', '"', $raw_suppression);
        $suppression = json_decode($raw_suppression, true);
        return $suppression;
    }

    public function hasSuppressingAncestor()
    {
        $parents = $this->parents();
        if (count($parents) === 0) {
            return false;
        }
        else {
            foreach ($parents as $parent) {
                $parentChecker = new SuppressionChecker($parent);
                $parentSuppression = $parentChecker->suppression();
                if (($parentChecker->_suppressionCount > 1)    ||
                    ($parentSuppression['recursive'])         ||
                    ($parentChecker->hasSuppressingAncestor()) )  {
                    return true;
                }
            }
            return false;
        }
    }

    public function exportable()
    {
        $exportable = false;
        if ($this->_suppressionCount > 1) {
            $exportable = false;
        }
        else {
            $suppression = $this->suppression();
            if ($suppression['description']) {
                $exportable = false;
            }
            else {
                if ($this->hasSuppressingAncestor()) {
                    $exportable = false;
                }
                else {
                    $exportable = true;
                }
            }
        }
        return $exportable;
    }

    private $_item;
    private $_itemType;
    private $_suppressionCount;
    private $_suppressionField;
}
