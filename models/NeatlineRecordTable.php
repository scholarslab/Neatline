<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=76; */

/**
 * Table class for Neatline data records.
 *
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class NeatlineRecordTable extends Omeka_Db_Table
{


    /**
     * Update records in an exhibit according to the value-defined style
     * definitions in the `styles` YAML. For example, if `styles` is:
     *
     * tag:
     *  - vector_color: #ffffff
     *  - stroke_color
     *
     *  The vector color on records tagged with `tag` will be updated to
     *  #ffffff, but the stroke color will be unchanged since no explicit
     *  value is set in the YAML.
     *
     * @param NeatlineExhibit The exhibit to update.
     */
    public function applyStyles($exhibit)
    {

        $yaml   = Spyc::YAMLLoad($exhibit->styles);
        $valid  = neatline_getStyleCols();

        // Iterate tag definitions.
        foreach ($yaml as $tag => $styles) {

            if (!is_array($styles)) continue;

            // `WHERE`
            $where = array('exhibit_id = ?' => $exhibit->id);
            if ($tag != 'default') $where['tags REGEXP ?'] =
                '[[:<:]]'.$tag.'[[:>:]]';

            // `SET`
            $set = array();
            foreach ($styles as $style) {
                if (is_array($style)) {
                    foreach ($style as $s => $v) {
                        if (in_array($s, $valid)) $set[$s] = $v;
                    }
                }
            }

            // Update records.
            $this->update($this->getTableName(), $set, $where);

        }

    }


    /**
     * Select `coverage` as plain-text and order by creation date.
     *
     * @return Omeka_Db_Select The modified select.
     */
    public function getSelect()
    {

        $select = parent::getSelect();

        // Select `coverage` as plain-text.
        $select->columns(array('coverage' => new Zend_Db_Expr(
            'NULLIF(AsText(coverage), "POINT(0 0)")'
        )));

        // Order chronologically.
        $select->order('added DESC');

        return $select;

    }


    /**
     * Count the number of active records in an exhibit.
     *
     * @param NeatlineExhibit $exhibit The exhibit record.
     * @return int The number of active records.
     */
    public function countActiveRecordsByExhibit($exhibit)
    {
        return $this->count(array(
            'exhibit_id' => $exhibit->id,
            'map_active' => 1
        ));
    }


    /**
     * Construct data array for individual record.
     *
     * @param int $id The record id.
     * @return array The record data.
     */
    public function queryRecord($id)
    {
        return $this->fetchObject(
            $this->getSelect()->where('id=?', $id)
        )->buildJsonData();
    }


    /**
     * Construct records array for exhibit and editor.
     *
     * @param NeatlineExhibit $exhibit The exhibit record.
     * @param string $extent The viewport extent.
     * @param int $zoom The zoom level.
     * @return array The collection of records.
     */
    public function queryRecords(
        $exhibit,
        $extent = null,
        $zoom   = null,
        $limit  = null,
        $offset = 0
    )
    {

        $data = array('records' => array());
        $select = $this->getSelect();

        // Filter by exhibit.
        $select = $this->_filterByExhibit($select, $exhibit);

        // ** Zoom
        if (!is_null($zoom)) {
            $select = $this->_filterByZoom($select, $zoom);
        }

        // ** Extent
        if (!is_null($extent)) {
            $select = $this->_filterByExtent($select, $extent);
        }

        // ** Limit
        if (!is_null($limit)) {
            $select = $this->_limit($limit, $offset);
        }

        // Execute query.
        if ($records = $this->fetchObjects($select)) {
            foreach ($records as $record) {
                $data['records'][] = $record->buildJsonData();
            }
        }

        // Strip off LIMIT and columns.
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::COLUMNS);

        // Count the total result size.
        $data['count'] = $select->columns('COUNT(*)')->
            query()->fetchColumn();

        return $data;

    }


    /**
     * Filter by exhibit.
     *
     * @param Omeka_Db_Select $select The starting select.
     * @param NeatlineExhibit $exhibit The exhibit.
     * @return Omeka_Db_Select The filtered select.
     */
    public function _filterByExhibit($select, $exhibit)
    {
        return $select->where('exhibit_id = ?', $exhibit->id);
    }


    /**
     * Filter by zoom.
     *
     * @param Omeka_Db_Select $select The starting select.
     * @param integer $zoom The zoom level.
     * @return Omeka_Db_Select The filtered select.
     */
    public function _filterByZoom($select, $zoom)
    {
        $select->where("min_zoom IS NULL OR min_zoom<=?", $zoom);
        $select->where("max_zoom IS NULL OR max_zoom>=?", $zoom);
        return $select;
    }


    /**
     * Filter by extent.
     *
     * @param Omeka_Db_Select $select The starting select.
     * @param string $extent The extent, as a WKT polygon.
     * @return Omeka_Db_Select The filtered select.
     */
    public function _filterByExtent($select, $extent)
    {

        // Query for viewport intersection.
        $select->where(new Zend_Db_Expr("MBRIntersects(
            coverage, GeomFromText('$extent')
        )"));

        // Omit records at POINT(0 0).
        $select->where(new Zend_Db_Expr(
            "AsText(coverage) != 'POINT(0 0)'"
        ));

        return $select;

    }


    /**
     * Filter by tag.
     *
     * @param Omeka_Db_Select $select The starting select.
     * @param string $tag The tag.
     * @return Omeka_Db_Select The filtered select.
     */
    public function _filterByTag($select, $tag)
    {
        return $select->where(new Zend_Db_Expr(
            "tags REGEXP '[[:<:]]".$tag."[[:>:]]'"
        ));
    }


    /**
     * Paginate the query.
     *
     * @param int $offset The starting offset.
     * @param int $limit The number of records to select.
     * @return Omeka_Db_Select The filtered select.
     */
    public function _limit($limit, $offset)
    {
        return $select->limit($limit, $offset);
    }


}
