<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=76; */

/**
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class NeatlinePlugin_Migration_Test extends NeatlinePlugin_Migration_TestBase
{

    /**
     * This tests that old tables are renamed.
     *
     * @return void
     * @author Eric Rochester
     **/
    public function testRenameOldTables()
    {
        $tables = $this->db->listTables();
        $prefix = "{$this->db->prefix}neatline_";
        $this->assertContains("{$prefix}exhibits_migrate",     $tables);
        $this->assertContains("{$prefix}data_records_migrate", $tables);
        $this->assertContains("{$prefix}base_layers_migrate",  $tables);
    }

    /**
     * This tests that new tables are created.
     *
     * @return void
     * @author Eric Rochester
     **/
    public function testCreateNewTables()
    {
        $tables = $this->db->listTables();
        $prefix = "{$this->db->prefix}neatline_";
        $this->assertContains("{$prefix}exhibits", $tables);
        $this->assertContains("{$prefix}records",  $tables);
    }

    /**
     * Tests whether the fixtures are working.
     *
     * @return void
     * @author Eric Rochester
     **/
    public function testFixtureLoading()
    {
        $db     = $this->db;
        $prefix = "{$db->prefix}neatline_";

        $sql    = "SELECT COUNT(*) FROM {$prefix}exhibits_migrate;";
        $counts = $db->query($sql);
        foreach ($counts as $c) {
            $this->assertEquals(0, $c);
        }

        $sql    = "SELECT COUNT(*) FROM {$prefix}data_records_migrate;";
        $counts = $db->query($sql);
        foreach ($counts as $c) {
            $this->assertEquals(0, $c);
        }
    }

}
