(function ($, window, Drupal) {
  /**
   * Defines block condition summary behaviour.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.blockSettingsSummaryNodes = {
    attach: function (context) {
      if (jQuery.fn.drupalSetSummary !== undefined) {
        $('[data-drupal-selector="edit-visibility-node-condition"]').drupalSetSummary(function (context) {
          let inputValue = $(context)
            .find('input[data-drupal-selector="edit-visibility-node-condition-nodes"]')
            .val();
          return (inputValue) ? Drupal.t('Restricted to certain nodes') : Drupal.t('Not restricted');
        });
      }
    }
  };
})(jQuery, window, Drupal);
