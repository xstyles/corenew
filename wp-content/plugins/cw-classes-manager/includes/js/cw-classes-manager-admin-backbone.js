window.wp = window.wp || {}
;(function ($) {
  /**
   * Credits @markjaquith
   *
   * Based on the plugin https://github.com/markjaquith/Showdown and the talk
   * Mark Jaquith did at WordCamp San Francisco 2014.
   */
  var cwcm_admin = {
    start: function () {
      this.terms = new this.Collections.Terms()
      this.setForm()
      this.terms.fetch()
      this.terms.on('add', this.inject, this)
    },

    setForm: function () {
      this.form = new this.Views.Form({ collection: this.terms })
      this.form.inject('.cw_class-form')
    },

    inject: function () {
      this.view = new this.Views.Terms({ collection: this.terms })
      this.view.inject('.cw_class-list-terms')
    },
  }

  // Extend wp.Backbone.View with .prepare() and .inject()
  cwcm_admin.View = wp.Backbone.View.extend({
    inject: function (selector) {
      this.render()
      $(selector).html(this.el)
      this.views.ready()
    },

    prepare: function () {
      if (!_.isUndefined(this.model) && _.isFunction(this.model.toJSON)) {
        return this.model.toJSON()
      } else {
        return {}
      }
    },
  })

  /* ------ */
  /* MODELS */
  /* ------ */

  cwcm_admin.Models = {}
  cwcm_admin.vars = cw_class_admin_vars

  cwcm_admin.Models.Term = Backbone.Model.extend({
    term: {},
  })

  /* ----------- */
  /* COLLECTIONS */
  /* ----------- */
  cwcm_admin.Collections = {}

  cwcm_admin.Collections.Terms = Backbone.Collection.extend({
    model: cwcm_admin.Models.Term,

    sync: function (method, model, options) {
      if ('read' === method) {
        options = options || {}
        options.context = this
        options.data = _.extend(options.data || {}, {
          action: 'cw_class_get_terms',
          nonce: cwcm_admin.vars.nonce,
        })

        return wp.ajax.send(options)
      }
    },

    parse: function (resp, xhr) {
      if (!_.isArray(resp)) {
        resp = [resp]
      }

      return resp
    },

    insertTerm: function (name, options) {
      model = this
      options = options || {}

      return wp.ajax
        .post('cw_class_insert_term', {
          cw_class_type_name: name,
          nonce: cwcm_admin.vars.nonce,
        })
        .done(function (resp, status, xhr) {
          model.add(model.parse(resp, xhr), options)
          model.trigger('termAdded', model, options)
        })
        .fail(function (resp, status, xhr) {
          options.term_name = name
          model.trigger('addingTermFailed', model, options)
        })
    },

    deleteTerm: function (term_id, options) {
      model = this
      options = options || {}

      return wp.ajax
        .post('cw_class_delete_term', {
          cw_class_type_id: term_id,
          nonce: cwcm_admin.vars.nonce,
        })
        .done(function (resp, status, xhr) {
          model.remove(model.get(term_id), options)
        })
        .fail(function (resp, status, xhr) {
          model.trigger('deletingTermFailed', model, options)
        })
    },

    updateTerm: function (term_id, options) {
      model = this
      options = options || {}

      return wp.ajax
        .post('cw_class_update_term', {
          cw_class_type_id: term_id,
          cw_class_type_name: options.name,
          nonce: cwcm_admin.vars.nonce,
        })
        .done(function (resp, status, xhr) {
          model.get(term_id).set('editing', 0)
          model.get(term_id).set('name', options.name)
        })
        .fail(function (resp, status, xhr) {
          model.trigger('updatingTermFailed', model, options)
        })
    },
  })

  /* ----- */
  /* VIEWS */
  /* ----- */
  cwcm_admin.Views = {}

  // Form to add new cw_class types
  cwcm_admin.Views.Form = cwcm_admin.View.extend({
    tagName: 'input',
    className: 'rdv-new-term regular-text',
    current_term: 0,

    attributes: {
      type: 'text',
      placeholder: cwcm_admin.vars.placeholder_default,
    },

    events: {
      keyup: 'saveTerm',
    },

    initialize: function () {
      this.collection.on('change', this.setTermToEdit, this)
    },

    saveTerm: function (event) {
      var type

      event.preventDefault()

      if (13 != event.keyCode || !$(event.target).val()) {
        return
      }

      type = $(event.target).val()
      $(event.target).val('')
      $(event.target).prop('disabled', true)
      $(event.target).prop('placeholder', cwcm_admin.vars.placeholder_saving)

      // Insert a term
      if (0 == this.current_term) {
        this.listenTo(this.collection, 'termAdded', this.termAdded)
        this.listenTo(
          this.collection,
          'addingTermFailed',
          this.addingTermFailed
        )

        this.collection.insertTerm(type)

        // Edit an existing term
      } else {
        this.collection.updateTerm(this.current_term, { name: type })
      }
    },

    termAdded: function (model, options) {
      var _this = this

      $(this.el).prop('placeholder', cwcm_admin.vars.placeholder_success)

      _.delay(function () {
        $(_this.el)
          .prop('placeholder', _this.attributes.placeholder)
          .prop('disabled', false)
      }, 1500)

      this.stopListening(this.collection, 'termAdded')
      this.stopListening(this.collection, 'addingTermFailed')
    },

    addingTermFailed: function (model, options) {
      var _this = this

      $(this.el).prop('placeholder', cwcm_admin.vars.placeholder_error)

      _.delay(function () {
        if (!_.isUndefined(options.term_name)) {
          $(_this.el).val(options.term_name)
        }
        $(_this.el)
          .prop('placeholder', _this.attributes.placeholder)
          .prop('disabled', false)
      }, 1500)

      this.stopListening(this.collection, 'termAdded')
      this.stopListening(this.collection, 'addingTermFailed')
    },

    setTermToEdit: function (model) {
      if (1 == model.get('editing')) {
        $(this.el).prop(
          'placeholder',
          cwcm_admin.vars.current_edited_type.replace('%s', model.get('name'))
        )
        this.current_term = model.get('id')
      } else {
        $(this.el).prop('placeholder', this.attributes.placeholder)
        this.current_term = 0

        if (true == $(this.el).prop('disabled')) {
          $(this.el).prop('disabled', false)
        }
      }
    },
  })

  // List of terms
  cwcm_admin.Views.Terms = cwcm_admin.View.extend({
    tagName: 'ul',
    className: 'cw_class-terms',

    initialize: function () {
      _.each(this.collection.models, this.addItemView, this)
    },

    addItemView: function (terms) {
      this.views.add(new cwcm_admin.Views.Term({ model: terms }))
    },
  })

  // Term item
  cwcm_admin.Views.Term = cwcm_admin.View.extend({
    tagName: 'li',
    className: 'cw_class-term postbox',
    template: wp.template('cw_class-term'),

    events: {
      'click .cw_class-delete-item': 'deleteTerm',
      'click .cw_class-edit-item': 'editTerm',
    },

    initialize: function () {
      this.model.on('remove', this.remove, this)
      this.model.on('change', this.toggleTermSelection, this)
    },

    deleteTerm: function (event) {
      var options = {}

      event.preventDefault()

      $(event.target).hide()
      options.link_delete = $(event.target)

      this.model.collection.deleteTerm($(event.target).data('term_id'), options)
      this.listenTo(
        this.model.collection,
        'deletingTermFailed',
        this.deletingTermFailed
      )
    },

    deletingTermFailed: function (model, options) {
      if (!_.isUndefined(options.link_delete)) {
        options.link_delete.show()
      }
      this.stopListening(cwcm_admin.terms)

      alert(cwcm_admin.vars.alert_notdeleted)
    },

    editTerm: function (event) {
      var id,
        current_term = 0

      event.preventDefault()

      id = $(event.target).data('term_id')

      _.each(this.model.collection.models, function (term) {
        if (
          !_.isUndefined(term.attributes.editing) &&
          1 == term.attributes.editing
        ) {
          current_term = term.attributes.id
        }
      })

      /* Edit one term at a time */
      if (0 == current_term) {
        this.model.set('editing', 1)
      } else if (id == current_term) {
        this.model.set('editing', 0)
      } else {
        return
      }
    },

    toggleTermSelection: function (model) {
      displayed_term = $(this.el).find('a').first().data('term_id')

      if (1 == model.get('editing') && displayed_term == model.get('id')) {
        $(this.el).addClass('rdv-select-term')
      } else {
        $(this.el).find('.cw_class-term-name').first().html(model.get('name'))
        $(this.el).removeClass('rdv-select-term')
      }
    },
  })

  cwcm_admin.start()
})(jQuery)
