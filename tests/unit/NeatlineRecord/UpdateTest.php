<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=76; */

/**
 * Tests for `update()` on NeatlineRecord.
 *
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class Neatline_NeatlineRecordTest_Update
    extends Neatline_Test_AppTestCase
{


    /**
     * update() should directly set all non-style values.
     *
     * @group tags
     */
    public function testUpdateNonStyleValues()
    {

        // Create record.
        $record = $this->__record();

        // Set:
        $record->slug               = 'slug';
        $record->title              = 'title';
        $record->body               = 'body';
        $record->tags               = 'tag1,tag2';
        $record->map_focus          = 'lat/lon';
        $record->map_zoom           = 1;
        $record->map_active         = 2;

        // Mock values.
        $values = array(

            // Local values:
            // -------------

            'id'                    => $record->id,
            'item_id'               => null,
            'slug'                  => 'slug2',
            'title'                 => 'title2',
            'body'                  => 'body2',
            'tags'                  => 'tag3,tag4',
            'map_focus'             => 'lat2/lon2',
            'coverage'              => 'POINT(1 1)',
            'map_zoom'              => 2,
            'map_active'            => 0,

            // Un-set local styles:
            // --------------------

            'vector_color'          => null,
            'stroke_color'          => null,
            'select_color'          => null,
            'vector_opacity'        => null,
            'select_opacity'        => null,
            'stroke_opacity'        => null,
            'image_opacity'         => null,
            'stroke_width'          => null,
            'point_radius'          => null,
            'point_image'           => null,
            'max_zoom'              => null,
            'min_zoom'              => null

        );

        // Update, get record.
        $record->update($values);
        $record = $this->_recordsTable->find($record->id);

        // Check new values.
        $this->assertEquals($record->slug, 'slug2');
        $this->assertEquals($record->title, 'title2');
        $this->assertEquals($record->body, 'body2');
        $this->assertEquals($record->tags, 'tag3,tag4');
        $this->assertEquals($record->map_focus, 'lat2/lon2');
        $this->assertEquals($record->map_zoom, 2);
        $this->assertEquals($record->map_active, 0);
        $this->assertNotNull($record->coverage);

    }


    /**
     * When non-null style values are passed to update() and the record
     * does not have an existing record-specific tag, a new tag should be
     * created and populated with values.
     *
     * @group tags
     */
    public function testUpdateCreateLocalTag()
    {

        // Create record.
        $record = $this->__record();

        // Mock values.
        $values = array(

            // Local values:
            // -------------

            'id'                    => $record->id,
            'item_id'               => null,
            'slug'                  => 'slug',
            'title'                 => 'title',
            'body'                  => 'body',
            'tags'                  => 'tag1,tag2',
            'coverage'              => 'POINT(1 1)',
            'map_active'            => 1,
            'map_focus'             => 'lat/lon',
            'map_zoom'              => 2,

            // Locally-set styles:
            // -------------------

            'vector_color'          => '#333333',
            'stroke_color'          => '#444444',
            'select_color'          => '#555555',
            'vector_opacity'        => 6,
            'select_opacity'        => 7,
            'stroke_opacity'        => 8,
            'image_opacity'         => 9,
            'stroke_width'          => 10,
            'point_radius'          => 11,
            'point_image'           => 'file.png',
            'max_zoom'              => 12,
            'min_zoom'              => 13,

        );

        // Starting tags count.
        $startCount = $this->_tagsTable->count();

        // Update.
        $record->update($values);

        // Check tags+1.
        $this->assertEquals($startCount+1, $this->_tagsTable->count());

        // Check new tag.
        $tag = $this->getLastTag();
        $this->assertEquals($record->tag_id,        $tag->id);
        $this->assertEquals($tag->vector_color,      '#333333');
        $this->assertEquals($tag->stroke_color,      '#444444');
        $this->assertEquals($tag->select_color,      '#555555');
        $this->assertEquals($tag->vector_opacity,    6);
        $this->assertEquals($tag->select_opacity,    7);
        $this->assertEquals($tag->stroke_opacity,    8);
        $this->assertEquals($tag->image_opacity,     9);
        $this->assertEquals($tag->stroke_width,      10);
        $this->assertEquals($tag->point_radius,      11);
        $this->assertEquals($tag->point_image,       'file.png');
        $this->assertEquals($tag->max_zoom,          12);
        $this->assertEquals($tag->min_zoom,          13);

    }


    /**
     * When non-null style values are passed to update() and the record
     * already has a record-specific tag, the tag should be updated with
     * the new values.
     *
     * @group tags
     */
    public function testUpdateUpdateLocalTag()
    {

        // Create record.
        $exhibit = $this->__exhibit();
        $record = $this->__record(null, $exhibit);

        // Create tag.
        $tag = new NeatlineTag($exhibit);
        $tag->vector_color =    '#111111';
        $tag->stroke_color =    '#222222';
        $tag->select_color =    '#333333';
        $tag->vector_opacity =  4;
        $tag->select_opacity =  5;
        $tag->stroke_opacity =  6;
        $tag->image_opacity =   7;
        $tag->stroke_width =    8;
        $tag->point_radius =    9;
        $tag->point_image =     'file.png';
        $tag->max_zoom =        10;
        $tag->min_zoom =        11;
        $tag->save();

        // Set tag reference.
        $record->tag_id = $tag->id;

        // Mock new values.
        $values = array(

            // Local values:
            // -------------

            'id'                    => $record->id,
            'item_id'               => null,
            'slug'                  => 'slug',
            'title'                 => 'title',
            'body'                  => 'body',
            'tags'                  => 'tag1,tag2',
            'coverage'              => 'POINT(1 1)',
            'map_active'            => 1,
            'map_focus'             => 'lat/lon',
            'map_zoom'              => 2,

            // Locally-set styles:
            // -------------------

            'vector_color'          => '#222222',
            'stroke_color'          => '#333333',
            'select_color'          => '#444444',
            'vector_opacity'        => 5,
            'select_opacity'        => 6,
            'stroke_opacity'        => 7,
            'image_opacity'         => 8,
            'stroke_width'          => 9,
            'point_radius'          => 10,
            'point_image'           => 'file2.png',
            'max_zoom'              => 11,
            'min_zoom'              => 12,

        );

        // Starting tags count.
        $startCount = $this->_tagsTable->count();

        // Update.
        $record->update($values);

        // Check tags+0.
        $this->assertEquals($startCount, $this->_tagsTable->count());

        // Check updated tag.
        $tag = $this->_tagsTable->find($tag->id);
        $this->assertEquals($tag->vector_color,      '#222222');
        $this->assertEquals($tag->stroke_color,      '#333333');
        $this->assertEquals($tag->select_color,      '#444444');
        $this->assertEquals($tag->vector_opacity,    5);
        $this->assertEquals($tag->select_opacity,    6);
        $this->assertEquals($tag->stroke_opacity,    7);
        $this->assertEquals($tag->image_opacity,     8);
        $this->assertEquals($tag->stroke_width,      9);
        $this->assertEquals($tag->point_radius,      10);
        $this->assertEquals($tag->point_image,       'file2.png');
        $this->assertEquals($tag->max_zoom,          11);
        $this->assertEquals($tag->min_zoom,          12);

    }


    /**
     * When a record-specific tag is updated, fields that had been set to
     * non-null values in the past but have now been "deactivated" in the
     * form should be set back to null on the tag.
     *
     * @group tags
     */
    public function testUpdateUpdateLocalTagNullValues()
    {

        // Create record.
        $exhibit = $this->__exhibit();
        $record = $this->__record(null, $exhibit);

        // Create tag.
        $tag = new NeatlineTag($exhibit);
        $tag->vector_color =    '#111111';
        $tag->stroke_color =    '#222222';
        $tag->select_color =    '#333333';
        $tag->vector_opacity =  4;
        $tag->select_opacity =  5;
        $tag->stroke_opacity =  6;
        $tag->image_opacity =   7;
        $tag->stroke_width =    8;
        $tag->point_radius =    9;
        $tag->point_image =     'file.png';
        $tag->max_zoom =        10;
        $tag->min_zoom =        11;
        $tag->save();

        // Set tag reference.
        $record->tag_id = $tag->id;

        // Mock new values.
        $values = array(

            // Local values:
            // -------------

            'id'                    => $record->id,
            'item_id'               => null,
            'slug'                  => 'slug',
            'title'                 => 'title',
            'body'                  => 'body',
            'tags'                  => 'tag1,tag2',
            'coverage'              => 'POINT(1 1)',
            'map_active'            => 1,
            'map_focus'             => 'lat/lon',
            'map_zoom'              => 2,

            // Locally-set styles:
            // -------------------

            'vector_color'          => '#222222',
            'stroke_color'          => null,
            'select_color'          => '#444444',
            'vector_opacity'        => null,
            'select_opacity'        => 6,
            'stroke_opacity'        => null,
            'image_opacity'         => 8,
            'stroke_width'          => null,
            'point_radius'          => 10,
            'point_image'           => null,
            'max_zoom'              => 11,
            'min_zoom'              => null,

        );

        // Starting tags count.
        $startCount = $this->_tagsTable->count();

        // Update.
        $record->update($values);

        // Check tags+0.
        $this->assertEquals($startCount, $this->_tagsTable->count());

        // Check updated tag.
        $tag = $this->_tagsTable->find($tag->id);
        $this->assertEquals($tag->vector_color,      '#222222');
        $this->assertNull($tag->stroke_color);
        $this->assertEquals($tag->select_color,      '#444444');
        $this->assertNull($tag->vector_opacity);
        $this->assertEquals($tag->select_opacity,    6);
        $this->assertNull($tag->stroke_opacity);
        $this->assertEquals($tag->image_opacity,     8);
        $this->assertNull($tag->stroke_width);
        $this->assertEquals($tag->point_radius,      10);
        $this->assertNull($tag->point_image);
        $this->assertEquals($tag->max_zoom,          11);
        $this->assertNull($tag->min_zoom);

    }


    /**
     * When a record that has a record-specific tag from previous updates
     * is updated with all-null local style values, the existing local tag
     * is no longer needed and should be removed and the `tag_id` on the
     * record should be set to null.
     *
     * @group tags
     */
    public function testUpdateLocalTagGarbageCollection()
    {

        // Create record.
        $exhibit = $this->__exhibit();
        $record = $this->__record(null, $exhibit);

        // Create tag.
        $tag = new NeatlineTag($exhibit);
        $tag->vector_color =    '#111111';
        $tag->stroke_color =    '#222222';
        $tag->select_color =    '#333333';
        $tag->vector_opacity =  4;
        $tag->select_opacity =  5;
        $tag->stroke_opacity =  6;
        $tag->image_opacity =   7;
        $tag->stroke_width =    8;
        $tag->point_radius =    9;
        $tag->point_image =     'file.png';
        $tag->max_zoom =        10;
        $tag->min_zoom =        11;
        $tag->save();

        // Set tag reference.
        $record->tag_id = $tag->id;

        // Mock new values.
        $values = array(

            // Local values:
            // -------------

            'id'                    => $record->id,
            'item_id'               => null,
            'slug'                  => 'slug',
            'title'                 => 'title',
            'body'                  => 'body',
            'tags'                  => 'tag1,tag2',
            'coverage'              => 'POINT(1 1)',
            'map_active'            => 1,
            'map_focus'             => 'lat/lon',
            'map_zoom'              => 2,

            // Locally-set styles:
            // -------------------

            'vector_color'          => null,
            'stroke_color'          => null,
            'select_color'          => null,
            'vector_opacity'        => null,
            'select_opacity'        => null,
            'stroke_opacity'        => null,
            'image_opacity'         => null,
            'stroke_width'          => null,
            'point_radius'          => null,
            'point_image'           => null,
            'max_zoom'              => null,
            'min_zoom'              => null,

        );

        // Starting tags count.
        $startCount = $this->_tagsTable->count();

        // Update.
        $record->update($values);

        // Check tags-1.
        $this->assertEquals($startCount-1, $this->_tagsTable->count());
        $this->assertNull($this->_tagsTable->find($tag->id));

        // Re-get the record, check for null `tag_id`.
        $record = $this->_recordsTable->find($record->id);
        $this->assertNull($record->tag_id);

    }


    /**
     * When a record is updated, the tags string should be used to update
     * all of the style tag references to point to the first tag in the
     * depth chart for which the field in question is defined.
     *
     * @group tags
     */
    public function testUpdateTagReferences()
    {

        // Create record.
        $exhibit = $this->__exhibit();
        $record = $this->__record(null, $exhibit);

        // Create 12 tags, each with a different field defined.
        // ----------------------------------------------------

        $tag1 = new NeatlineTag($exhibit);
        $tag1->vector_color = '#1';
        $tag1->tag = 't1';
        $tag1->save();

        $tag2 = new NeatlineTag($exhibit);
        $tag2->stroke_color = '#2';
        $tag2->tag = 't2';
        $tag2->save();

        $tag3 = new NeatlineTag($exhibit);
        $tag3->select_color = '#3';
        $tag3->tag = 't3';
        $tag3->save();

        $tag4 = new NeatlineTag($exhibit);
        $tag4->vector_opacity = 4;
        $tag4->tag = 't4';
        $tag4->save();

        $tag5 = new NeatlineTag($exhibit);
        $tag5->select_opacity = 5;
        $tag5->tag = 't5';
        $tag5->save();

        $tag6 = new NeatlineTag($exhibit);
        $tag6->stroke_opacity = 6;
        $tag6->tag = 't6';
        $tag6->save();

        $tag7 = new NeatlineTag($exhibit);
        $tag7->image_opacity = 7;
        $tag7->tag = 't7';
        $tag7->save();

        $tag8 = new NeatlineTag($exhibit);
        $tag8->stroke_width = 8;
        $tag8->tag = 't8';
        $tag8->save();

        $tag9 = new NeatlineTag($exhibit);
        $tag9->point_radius = 9;
        $tag9->tag = 't9';
        $tag9->save();

        $tag10 = new NeatlineTag($exhibit);
        $tag10->point_image = '10.png';
        $tag10->tag = 't10';
        $tag10->save();

        $tag11 = new NeatlineTag($exhibit);
        $tag11->max_zoom = 11;
        $tag11->tag = 't11';
        $tag11->save();

        $tag12 = new NeatlineTag($exhibit);
        $tag12->min_zoom = 12;
        $tag12->tag = 't12';
        $tag12->save();

        $tags = 't1,t2,t3,t4,t5,t6,t7,t8,t9,t10,t11,t12';

        // Mock values.
        $values = array(

            // Local values:
            // -------------

            'id'                    => $record->id,
            'item_id'               => null,
            'slug'                  => 'slug',
            'title'                 => 'title',
            'body'                  => 'body',
            'tags'                  => $tags,
            'coverage'              => 'POINT(1 1)',
            'map_active'            => 1,
            'map_focus'             => 'lat/lon',
            'map_zoom'              => 2,

            // Null local styles:
            // ------------------

            'vector_color'          => null,
            'stroke_color'          => null,
            'select_color'          => null,
            'vector_opacity'        => null,
            'select_opacity'        => null,
            'stroke_opacity'        => null,
            'image_opacity'         => null,
            'stroke_width'          => null,
            'point_radius'          => null,
            'point_image'           => null,
            'max_zoom'              => null,
            'min_zoom'              => null,

        );

        // Update.
        $record->update($values);

        // Re-get the record, check the references.
        $record = $this->_recordsTable->find($record->id);
        $this->assertEquals($record->vector_color, $tag1->id);
        $this->assertEquals($record->stroke_color, $tag2->id);
        $this->assertEquals($record->select_color, $tag3->id);
        $this->assertEquals($record->vector_opacity, $tag4->id);
        $this->assertEquals($record->select_opacity, $tag5->id);
        $this->assertEquals($record->stroke_opacity, $tag6->id);
        $this->assertEquals($record->image_opacity, $tag7->id);
        $this->assertEquals($record->stroke_width, $tag8->id);
        $this->assertEquals($record->point_radius, $tag9->id);
        $this->assertEquals($record->point_image, $tag10->id);
        $this->assertEquals($record->max_zoom, $tag11->id);
        $this->assertEquals($record->min_zoom, $tag12->id);

    }


}
