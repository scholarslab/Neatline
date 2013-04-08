
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=76; */

/**
 * Custom assertions.
 *
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


var _t = (function(_t) {


  /**
   * Assert the method of the last request.
   *
   * @param {String} method: The method.
   */
  _t.assertLastRequestMethod = function(method) {
    var request = this.getLastRequest();
    expect(request.method).toEqual(method);
  };


  /**
   * Assert the route of the last request.
   *
   * @param {String} route: The route.
   */
  _t.assertLastRequestRoute = function(route) {
    var request = this.getLastRequest();
    expect(_.string.startsWith(request.url, route)).toBeTruthy();
  };


  /**
   * Assert that the last request has a GET key/value.
   *
   * @param {String} key: The key.
   * @param {String} val: The value.
   */
  _t.assertLastRequestHasGetParameter = function(key, val) {
    var request = this.getLastRequest();
    if (val) expect(request.url).toContain(key+'='+val);
    else expect(request.url).toContain(key);
  };


  /**
   * Assert that the last request was a map refresh.
   */
  _t.assertMapRefreshed = function() {

    // Should issue GET request to records API.
    _t.assertLastRequestRoute(Neatline.global.records_api);
    _t.assertLastRequestMethod('GET');

    // Request should include map focus.
    _t.assertLastRequestHasGetParameter('extent');
    _t.assertLastRequestHasGetParameter('zoom');

    // Respond with updated collection.
    this.respondLast200(this.json.records.changed);

    // Record2 point should be changed.
    var record2Layer = _t.getVectorLayerByTitle('title2');
    expect(record2Layer.features[0].geometry.x).toEqual(7);
    expect(record2Layer.features[0].geometry.y).toEqual(8);

    // Record3 point should be removed.
    expect(this.getVectorLayerByTitle('title3')).toBeUndefined();
    this.assertVectorLayerCount(2);

  };


  /**
   * Assert the current viewport zoom and focus.
   *
   * @param {Number} lon: The focus longitude.
   * @param {Number} lat: The focus latitude.
   * @param {Number} zoom: The zoom.
   */
  _t.assertMapViewport = function(lon, lat, zoom) {
    expect(this.vw.MAP.map.getCenter().lon).toEqual(lon);
    expect(this.vw.MAP.map.getCenter().lat).toEqual(lat);
    expect(this.vw.MAP.map.getZoom()).toEqual(zoom);
  };


  /**
   * Assert the current viewport zoom and focus.
   *
   * @param {Number} lon: The focus longitude.
   * @param {Number} lat: The focus latitude.
   * @param {Number} zoom: The zoom.
   */
  _t.assertVectorLayerCount = function(count) {
    expect(this.vw.MAP.getVectorLayers().length).toEqual(count);
  };


  /**
   * Assert that the pagination `<<` link is enabled.
   */
  _t.assertPaginationPrevEnabled = function() {
    var prev = this.vw.RECORDS.$el.find('.pagination .prev');
    expect($(prev[0]).parent('li')).not.toHaveClass('disabled');
    expect($(prev[1]).parent('li')).not.toHaveClass('disabled');
  };


  /**
   * Assert that the pagination `<<` link is disabled.
   */
  _t.assertPaginationPrevDisabled = function() {
    var prev = this.vw.RECORDS.$el.find('.pagination .prev');
    expect($(prev[0]).parent('li')).toHaveClass('disabled');
    expect($(prev[1]).parent('li')).toHaveClass('disabled');
  };


  /**
   * Assert that the pagination `>>` link is enabled.
   */
  _t.assertPaginationNextEnabled = function() {
    var next = this.vw.RECORDS.$el.find('.pagination .next');
    expect($(next[0]).parent('li')).not.toHaveClass('disabled');
    expect($(next[1]).parent('li')).not.toHaveClass('disabled');
  };


  /**
   * Assert that the pagination `>>` link is disabled.
   */
  _t.assertPaginationNextDisabled = function() {
    var next = this.vw.RECORDS.$el.find('.pagination .next');
    expect($(next[0]).parent('li')).toHaveClass('disabled');
    expect($(next[1]).parent('li')).toHaveClass('disabled');
  };


  /**
   * Assert the `href` attribute on the pagination `<<` link.
   *
   * @param {String} route: The hash.
   */
  _t.assertPaginationPrevRoute = function(route) {
    var prev = this.vw.RECORDS.$el.find('.pagination .prev');
    expect($(prev[0])).toHaveAttr('href', route);
    expect($(prev[1])).toHaveAttr('href', route);
  };


  /**
   * Assert the `href` attribute on the pagination `>>` link.
   *
   * @param {String} route: The hash.
   */
  _t.assertPaginationNextRoute = function(route) {
    var next = this.vw.RECORDS.$el.find('.pagination .next');
    expect($(next[0])).toHaveAttr('href', route);
    expect($(next[1])).toHaveAttr('href', route);
  };


  /**
   * Assert the active record form tab.
   *
   * @param {String} slug: The tab slug.
   */
  _t.assertActiveTab = function(slug) {

    // Get tab and pane.
    var tab = this.vw.RECORD.$('a[href="#record-'+slug+'"]');
    var pane = this.vw.RECORD.$('#record-'+slug);

    // Tab should be active, pane visible.
    expect(tab.parent('li')).toHaveClass('active');
    expect(pane).toHaveClass('active');

  };


  return _t;


})(_t || {});
