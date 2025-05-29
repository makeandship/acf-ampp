(function($) {
  function initialize_field($el) {
    //$el.doStuff();
  }

  if (acf) {
    var AMPP = acf.Field.extend({
      type: "ampp",
      select2: false,

      wait: "load",

      events: {
        removeField: "onRemove"
      },

      $input: function() {
        return this.$("select");
      },

      initialize: function() {
        // vars
        var $select = this.$input();

        // inherit data
        this.inherit($select);

        // select2
        if (this.get("ui")) {
          // populate ajax_data (allowing custom attribute to already exist)
          var ajaxAction = this.get("ajax_action");
          if (!ajaxAction) {
            ajaxAction = "acf/fields/" + this.get("type") + "/query";
          }

          // select2
          this.select2 = acf.newSelect2($select, {
            field: this,
            ajax: this.get("ajax"),
            multiple: this.get("multiple"),
            placeholder: this.get("placeholder"),
            allowNull: this.get("allow_null"),
            ajaxAction: ajaxAction,
            ajaxData: function ajaxData(args) {
              // context for the lookup
              var fieldKey = args.field_key;
              if (fieldKey) {
                var wrapper = $("div.acf-" + fieldKey.replace("_", "-"));
                var parent = wrapper.parents("div.acf-postbox");

                // contextualise this lookup
                var vtmId = $('div[data-type="vtm"] select', parent).val();
                var vmpId = $('div[data-type="vmp"] select', parent).val();
                var vmppId = $('div[data-type="vmpp"] select', parent).val();
                var ampId = $('div[data-type="amp"] select', parent).val();

                // capture the value of the parent VTM and set
                if (vtmId) {
                  args["vtm_id"] = vtmId;
                }
                if (vmpId) {
                  args["vmp_id"] = vmpId;
                }
                if (vmppId) {
                  args["vmpp_id"] = vmppId;
                }
                if (ampId) {
                  args["amp_id"] = ampId;
                }
              }

              return args;
            }
          });
        }
      },

      onRemove: function() {
        if (this.select2) {
          this.select2.destroy();
        }
      }
    });

    acf.registerFieldType(AMPP);
  }
  if (typeof acf.add_action !== "undefined" && true === false) {
    /*acf.fields.medicine_pack = acf.fields.select.extend({

      type: 'medicine_pack',
      minimumInputLength: 1,
        quietMillis: 100 //or 100 or 10

    });*/

    /*
    *  ready append (ACF5)
    *
    *  These are 2 events which are fired during the page load
    *  ready = on page load similar to $(document).ready()
    *  append = on new DOM elements appended via repeater field
    *
    *  @type  event
    *  @date  20/07/13
    *
    *  @param $el (jQuery selection) the jQuery element which contains the ACF fields
    *  @return  n/a
    */

    /*acf.add_action('ready append', function( $el ){

      // search $el for fields of type 'FIELD_NAME'
      acf.get_fields({ type : 'medicine_pack'}, $el).each(function(){

        initialize_field( $(this) );

      });

    });*/

    acf.add_filter("prepare_for_ajax", function(args) {
      // context for the lookup
      var fieldKey = args.field_key;
      if (fieldKey) {
        var wrapper = $("div.acf-" + fieldKey.replace("_", "-"));
        var parent = wrapper.parents("div.acf-postbox");

        // contextualise this lookup
        var vtmId = $('div[data-type="vtm"] input', parent).val();
        var vmpId = $('div[data-type="vmp"] input', parent).val();
        var vmppId = $('div[data-type="vmpp"] input', parent).val();
        var ampId = $('div[data-type="amp"] input', parent).val();

        // capture the value of the parent VTM and set
        if (vtmId) {
          args["vtm_id"] = vtmId;
        }
        if (vmpId) {
          args["vmp_id"] = vmpId;
        }
        if (vmppId) {
          args["vmpp_id"] = vmppId;
        }
        if (ampId) {
          args["amp_id"] = ampId;
        }
      }

      return args;
    });
  } else {
    /*
    *  acf/setup_fields (ACF4)
    *
    *  This event is triggered when ACF adds any new elements to the DOM.
    *
    *  @type  function
    *  @since 1.0.0
    *  @date  01/01/12
    *
    *  @param event   e: an event object. This can be ignored
    *  @param Element   postbox: An element which contains the new HTML
    *
    *  @return  n/a
    */

    $(document).on("acf/setup_fields", function(e, postbox) {
      $(postbox)
        .find('.field[data-field_type="ampp"]')
        .each(function() {
          initialize_field($(this));
        });
    });
  }
})(jQuery);
