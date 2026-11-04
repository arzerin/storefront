// $Id: pwcs_defaultify.js,v 1.0 2011/11/04 rasel kabir Exp $
/**
 * @name      jQuery.defaultify
 * @author    Scott Lewis
 * @url       http://iconify.it
 * @date      August 16, 2011
 *
 * Adds a default value to a field or fields.
 * The value will toggle on field
 * focus and blur.
 */
(function($) {
  $.fn.defaultify = function(defaultValue) {
    /**
     * Iterate through the collection of elements and
     * return the object to preserve method chaining
     */
    return this.each(function(i) {
      /**
       * Wrap the current element in an instance of jQuery
       */
      var $this = $(this);
      /**
       * Set the initial value
       */
      $this.val(defaultValue);
      /**
       * Do our kick-ass thing
       */
      $this.focus(function(e) {
        if ($.trim($this.val()) == defaultValue) $this.val("");
      });
      $this.blur(function(e) {
        if ($.trim($this.val()) == "") $this.val(defaultValue);
      });
    });
  };
})(jQuery);