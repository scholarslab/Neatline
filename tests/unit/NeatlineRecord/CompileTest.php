<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=76; */

/**
 * Tests for `compile()` on NeatlineRecord.
 *
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class Neatline_NeatlineRecordTest_Compile
    extends Neatline_Test_AppTestCase
{


    /**
     * [item:<id>].
     * @group compile
     */
    public function testItemId()
    {

        // ----------------------------------------------------------------
        // Create 3 items, each with a different title.

        $item1 = insert_item(array(), array(
            'Dublin Core' => array (
                'Title' => array(
                    array('text' => 'title1', 'html' => false)
                )
            )
        ));

        $item2 = insert_item(array(), array(
            'Dublin Core' => array (
                'Title' => array(
                    array('text' => 'title2', 'html' => false)
                )
            )
        ));

        $item3 = insert_item(array(), array(
            'Dublin Core' => array (
                'Title' => array(
                    array('text' => 'title3', 'html' => false)
                )
            )
        ));

        // ----------------------------------------------------------------
        // Generate the full element text output for the items.

        get_view()->setScriptPath(VIEW_SCRIPTS_DIR);
        $texts1 = all_element_texts($item1);
        $texts2 = all_element_texts($item2);
        $texts3 = all_element_texts($item3);

        // ----------------------------------------------------------------
        // Form the raw and compiled values.

        $raw = <<<EOD
[item:$item1->id]
abc
[item:$item2->id]
def
[item:$item3->id]
EOD;

        $compiled = <<<EOD
$texts1
abc
$texts2
def
$texts3
EOD;

        // ----------------------------------------------------------------
        // Set the uncompiled source to `title` and `body`.

        $record = $this->__record();
        $record->title = $raw;
        $record->body  = $raw;
        $record->compile();

        // `_title` and `_body` should be compiled.
        $this->assertEquals($record->_title, $compiled);
        $this->assertEquals($record->_body,  $compiled);

    }


    /**
     * [item:<id>:<field>].
     * @group compile
     */
    public function testItemIdField()
    {

        // ----------------------------------------------------------------
        // Create an item with a title, description, and subject.

        $item = insert_item(array(), array(
            'Dublin Core' => array (
                'Title' => array(
                    array('text' => 'title', 'html' => false)
                ),
                'Subject' => array(
                    array('text' => 'subject', 'html' => false)
                ),
                'Description' => array(
                    array('text' => 'description', 'html' => false)
                )
            )
        ));

        // ----------------------------------------------------------------
        // Form the raw and compiled values.

        $raw = <<<EOD
[item:$item->id:"Title"]
abc
[item:$item->id:"Subject"]
def
[item:$item->id:"Description"]
EOD;

        $compiled = <<<EOD
title
abc
subject
def
description
EOD;

        // ----------------------------------------------------------------
        // Set the uncompiled source to `title` and `body`.

        $record = $this->__record();
        $record->title = $raw;
        $record->body  = $raw;
        $record->compile();

        // `_title` and `_body` should be compiled.
        $this->assertEquals($record->_title, $compiled);
        $this->assertEquals($record->_body,  $compiled);

    }


    /**
     * [item:<id>:files].
     * @group compile
     */
    public function testItemIdFiles()
    {

        // ----------------------------------------------------------------
        // Create 3 items, each with a different file.

        $item1 = insert_item();
        insert_files_for_item($item1, 'Filesystem', array(
            NL_DIR . '/tests/mocks/file1.txt'
        ));

        $item2 = insert_item();
        insert_files_for_item($item2, 'Filesystem', array(
            NL_DIR . '/tests/mocks/file2.txt'
        ));

        $item3 = insert_item();
        insert_files_for_item($item3, 'Filesystem', array(
            NL_DIR . '/tests/mocks/file3.txt'
        ));

        // ----------------------------------------------------------------
        // Generate the files markup output for the items.

        get_view()->setScriptPath(VIEW_SCRIPTS_DIR);
        $files1 = files_for_item(array(), array(), $item1);
        $files2 = files_for_item(array(), array(), $item2);
        $files3 = files_for_item(array(), array(), $item3);

        // ----------------------------------------------------------------
        // Form the raw and compiled values.

        $raw = <<<EOD
[item:$item1->id:files]
abc
[item:$item2->id:files]
def
[item:$item3->id:files]
EOD;

        $compiled = <<<EOD
$files1
abc
$files2
def
$files3
EOD;

        // ----------------------------------------------------------------
        // Set the uncompiled source to `title` and `body`.

        $record = $this->__record();
        $record->title = $raw;
        $record->body  = $raw;
        $record->compile();

        // `_title` and `_body` should be compiled.
        $this->assertEquals($record->_title, $compiled);
        $this->assertEquals($record->_body,  $compiled);

    }


}
