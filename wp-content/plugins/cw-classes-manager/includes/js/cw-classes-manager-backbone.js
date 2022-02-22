var cls = cls || {}

/**
 * CW Class Editor
 */
;(function ($) {
  var media

  cls.media = media = {}

  cls.strings = _wpMediaViewsL10n.cw_class_strings

  media.ClassSettings = {
    id: 'cls_settings',
    fields: _wpMediaViewsL10n.cw_class_fields,
    datestrings: _wpMediaViewsL10n.cw_class_date_strings,
  }

  _.extend(media, { model: {}, view: {}, controller: {}, frames: {} })

  rendezvousField = cls.media.model.rendezvousField = Backbone.Model.extend({
    defaults: {
      id: 0,
      type: '',
      placeholder: '',
      label: '',
      value: '',
      tab: '',
      class: '',
    },
  })

  rendezvousDay = cls.media.model.rendezvousDay = Backbone.Model.extend({
    defaults: {
      id: 0,
      day: '',
      hour1: 0,
      hour2: 0,
      hour3: 0,
    },
  })

  rendezvousUser = cls.media.model.rendezvousUser = Backbone.Model.extend({
    defaults: {
      id: 0,
      avatar: '',
      name: '',
    },
  })

  rendezvousUsers = cls.media.model.rendezvousUsers =
    Backbone.Collection.extend({
      model: rendezvousUser,

      initialize: function () {
        this.options = { current_page: 1, total_page: 0 }
      },

      sync: function (method, model, options) {
        if ('read' === method) {
          options = options || {}
          options.context = this
          options.data = _.extend(options.data || {}, {
            action: 'cw_class_get_users',
            query: {
              page: this.options.current_page,
              search_terms: media.frame().state().get('search'),
              group_id: wp.media.view.settings.group_id,
              member_type: media.frame().state().get('member_type'),
            },
            _wpnonce: wp.media.view.settings.nonce.rendezvous,
          })

          return wp.ajax.send(options)
        }
      },

      parse: function (resp, xhr) {
        if (!_.isArray(resp.items)) resp.items = [resp.items]

        _.each(resp.items, function (value, index) {
          if (_.isNull(value)) return

          resp.items[index].id = value.id
          resp.items[index].avatar = value.avatar
          resp.items[index].name = value.name
        })

        if (!_.isUndefined(resp.meta)) {
          this.options.current_page = resp.meta.current_page
          this.options.total_page = resp.meta.total_page
        }

        return resp.items
      },
    })

  rendezvousDays = cls.media.model.rendezvousDays = Backbone.Collection.extend({
    model: rendezvousDay,
  })

  rendezvousFields = cls.media.model.rendezvousFields =
    Backbone.Collection.extend({
      model: rendezvousField,

      initialize: function () {
        var settings = cls.media.ClassSettings,
          _this = this

        _.each(settings.fields, function (field, id) {
          _this.add(field)
        })
      },
    })

  media.view.RendezVousField = wp.media.View.extend({
    className: 'cls-fields',
    tagName: 'li',
    template: wp.media.template('what'),

    initialize: function () {
      // trick to eventually display a warning to user once displayed
      if (this.model.get('id') == 'utcoffset') {
        var utc = new Date().getTimezoneOffset()
        utc = Number(utc / 60) * -1
        this.model.set('value', utc)
      }
    },

    render: function () {
      this.$el.html(this.template(this.model.toJSON()))
      return this
    },
  })

  media.view.RendezVousFields = wp.media.View.extend({
    className: 'list-cls-fields',
    tagName: 'ul',

    render: function () {
      var fields = this.collection.models,
        activeTab = this.options.tab.id,
        activeTpl = this.options.tab.tpl,
        _this = this

      _.each(fields, function (field) {
        if (field.get('tab') == activeTab) {
          var modelItem = _.extend(field.attributes, { tpl: activeTpl })
          var item = new media.view.RendezVousField({
            model: new Backbone.Model(modelItem),
          })

          _this.$el.append(item.render().$el)
        }
      })
    },
  })

  media.view.RendezVousDay = wp.media.View.extend({
    className: 'cls-days',
    tagName: 'li',
    template: wp.media.template('when'),

    render: function () {
      this.$el.html(this.template(this.model.toJSON()))
      return this
    },
  })

  media.view.RendezVousDays = wp.media.View.extend({
    className: 'list-cls-days',
    tagName: 'ul',

    initialize: function () {
      if (this.views.length) this.views.remove()

      this.displayDays()
      this.collection.on('add', this.displayDays, this)
      this.collection.on('remove', this.removeDays, this)
    },

    displayDays: function (model) {
      var _this = this

      if (_.isUndefined(this.days)) this.days = []

      if (_.isUndefined(this.collection) || this.collection.length == 0) {
        content = new media.view.RendezVousDay({
          controller: this.controller,
          model: new Backbone.Model({ day: 'Use the Calendar', intro: 1 }),
        })

        this.days[0] = content
        this.views.add(content)
      } else if (model) {
        if (!_.isUndefined(this.days[0])) this.days[0].remove()

        content = new media.view.RendezVousDay({
          controller: this.controller,
          model: model,
        })
        if (_.isUndefined(this.days[content.model.id])) {
          this.days[content.model.id] = content
          this.views.add(content)
        }
      } else {
        if (!_.isUndefined(this.days[0])) this.days[0].remove()

        _.each(this.collection.models, function (model) {
          content = new media.view.RendezVousDay({
            controller: this.controller,
            model: model,
          })

          _this.days[content.model.id] = content
          _this.views.add(content)
        })
      }
    },

    removeDays: function (model, models, options) {
      this.days[model.id].remove()
      var check = _.values(this.views._views)

      if (0 == check[0].length && !_.isUndefined(this.days[0])) {
        this.views.add(this.days[0])
      }
    },
  })

  media.view.RendezVousCalendar = wp.media.View.extend({
    className: 'cls-calendar',
    tagName: 'div',

    render: function () {
      _this = this
      this.$el.datepicker({
        dayNames: media.ClassSettings.datestrings.daynames,
        monthNames: media.ClassSettings.datestrings.monthnames,
        dayNamesMin: media.ClassSettings.datestrings.daynamesmin,
        dateFormat: media.ClassSettings.datestrings.format,
        firstDay: media.ClassSettings.datestrings.firstday,
        onSelect: function (dateText, inst) {
          var date = new Date(
            inst.selectedYear,
            inst.selectedMonth,
            inst.selectedDay,
            0,
            0,
            0,
            0
          )

          if (!_this.collection.get(date.getTime())) {
            _this.collection.add({
              id: date.getTime(),
              day:
                media.ClassSettings.datestrings.daynames[date.getDay()] +
                ' ' +
                dateText,
              mysql:
                inst.selectedYear +
                '-' +
                Number(inst.selectedMonth + 1) +
                '-' +
                inst.selectedDay,
            })
          } else {
            alert(media.ClassSettings.datestrings.alert)
          }
        },
      })
    },
  })

  media.view.RendezVousUser = wp.media.View.extend({
    className: 'cw_class-users attachment',
    tagName: 'li',
    template: wp.media.template('cw_class'),

    render: function () {
      this.$el.html(this.template(this.model.toJSON()))
      return this
    },
  })

  media.view.RendezVousUsers = wp.media.View.extend({
    className: 'list-cw_class-users',
    tagName: 'ul',

    events: {
      'click .attachment-preview': 'toggleSelectionHandler',
      'click .cw_class-users .check': 'removeSelectionHandler',
    },

    initialize: function () {
      if (this.views.length) this.views.remove()

      this.collection.reset()
      this.collection.fetch()
      this.collection.on('add error', this.displayUsers, this)

      this.controller.state().on('change:search', this.updateContent, this)
      this.controller.state().on('change:member_type', this.updateContent, this)
      this.controller.state().on('change:pagination', this.updateContent, this)

      this.getSelection().on('reset', this.clearItems, this)
    },

    updateContent: function (model) {
      this.removeContent()
      this.collection.reset()
      this.collection.fetch()
    },

    removeContent: function () {
      _.each(
        this.users,
        function (key) {
          if (!_.isUndefined(key)) {
            key.remove()
          }
        },
        this
      )

      this.users = []
    },

    displayUsers: function (model) {
      var _this = this,
        selection = this.getSelection()

      if (_.isUndefined(this.users)) this.users = []

      if (
        _.isUndefined(this.collection) ||
        this.collection.length == 0 ||
        !model
      ) {
        content = new media.view.RendezVousUser({
          controller: this.controller,
          model: new Backbone.Model({ notfound: 1 }),
        })

        if (!this.users[0]) {
          this.users[0] = content
          this.views.add(content)
        }
      } else if (model) {
        content = new media.view.RendezVousUser({
          controller: this.controller,
          model: model,
        })
        if (_.isUndefined(this.users[content.model.id])) {
          this.users[content.model.id] = content
          this.views.add(content)
        }
      } else {
        _.each(this.collection.models, function (model) {
          content = new media.view.RendezVousUser({
            controller: this.controller,
            model: model,
          })

          _this.users[content.model.id] = content
          _this.views.add(content)
        })
      }

      selection.each(function (model) {
        var id = '#user-' + model.get('id')
        this.$el.find(id).parent('li').addClass('selected details')
      }, this)

      //search bug
      if ($('.cw_class-users').length && $('.cw_class-users').length >= 2)
        $('.cw_class-users').find('#cw_class-error').parent().remove()
    },

    removeSelectionHandler: function (event) {
      var target = jQuery('#' + event.currentTarget.id)
      var id = target.attr('data-id')

      this.removeFromSelection(target, id)

      event.preventDefault()
    },

    toggleSelectionHandler: function (event) {
      if (event.target.href) return

      var target = jQuery('#' + event.currentTarget.id)
      var id = target.attr('data-id')

      if (this.getSelection().get(id)) this.removeFromSelection(target, id)
      else this.addToSelection(target, id)
    },

    addToSelection: function (target, id) {
      target.closest('.cw_class-users').addClass('selected details')

      this.getSelection().add(this.collection._byId[id])

      this.controller.state().props.trigger('change:selection')
    },

    removeFromSelection: function (target, id) {
      target.closest('.cw_class-users').removeClass('selected details')

      this.getSelection().remove(this.collection._byId[id])

      this.controller.state().props.trigger('change:selection')
    },

    getSelection: function () {
      return this.controller.state().props.get('_all').get('selection')
    },

    clearItems: function () {
      this.$el.find('.cw_class-users').removeClass('selected details')
    },
  })

  media.view.RendezVous = wp.media.View.extend({
    className: 'cw_class-frame',
    tagName: 'div',

    events: {
      'blur .cls-input-what': 'storeWhatInput',
      'click .cls-check-what': 'storeWhatInput',
      'change .cls-select-what': 'storeWhatInput',
      'blur .cls-input-when': 'storeWhenInput',
      'click .trashday': 'trashDay',
    },

    initialize: function () {
      var activeTab = this.options.tab.id,
        activeTpl = this.options.tab.tpl

      this.createSteps()

      if (activeTab == 'what') this.createSidebar()

      if (activeTab == 'who') this.createUserSidebar()
    },

    removeContent: function () {
      _.each(
        ['attachments', 'uploader'],
        function (key) {
          if (this[key]) {
            this[key].remove()
            delete this[key]
          }
        },
        this
      )
    },

    createSteps: function () {
      var content
      var contentWhen

      switch (this.options.tab.id) {
        case 'what':
          content = this.clsfields = new media.view.RendezVousFields({
            controller: this.controller,
            collection: this.model.get('clsfields'),
            model: this.model,
            tab: this.options.tab,
          })
          contentWhen = this.clsdays = new media.view.RendezVousDays({
            controller: this.controller,
            collection: this.model.get('clsdays'),
            model: this.model,
            tab: this.options.tab,
          })
          break

        case 'when':
          content = this.clsdays = new media.view.RendezVousDays({
            controller: this.controller,
            collection: this.model.get('clsdays'),
            model: this.model,
            tab: this.options.tab,
          })
          break

        case 'who':
          content = this.clsusers = new media.view.RendezVousUsers({
            controller: this.controller,
            collection: this.model.get('clsusers'),
            model: this.model,
            tab: this.options.tab,
          })
          break
      }

      this.views.add(content)
      contentWhen && this.views.add(contentWhen)
    },

    storeWhatInput: function (event) {
      var value

      if (event.target.type == 'checkbox') {
        value = event.target.checked ? 1 : 0
      } else {
        value = $(event.target).val()
      }

      // console.log('this.model.get("clsfields")', this.model.get("clsfields"))
      this.model.get('clsfields').get(event.target.id).set('value', value)
    },

    storeWhenInput: function (event) {
      var id, index, toparse

      toparse = event.target.id.split('-')
      id = Number(toparse[0])
      index = toparse[1]

      this.model.get('clsdays').get(id).set(index, $(event.target).val())
    },

    trashDay: function (event) {
      event.preventDefault()

      var toremove = $(event.target).data('id')
      model = this.model.get('clsdays').get(toremove)
      this.model.get('clsdays').remove(model)
    },

    createSidebar: function () {
      var options = this.options,
        selection = options.selection
      sidebar = this.sidebar = new wp.media.view.Sidebar({
        controller: this.controller,
      })

      this.views.add(sidebar)

      sidebar.set(
        'calendar',
        new media.view.RendezVousCalendar({
          controller: this.controller,
          collection: this.model.get('clsdays'),
          parents: this.views,
          priority: 80,
        })
      )
    },

    createUserSidebar: function () {
      this.sidebar = new media.view.RendezVousUsersPaginate({
        controller: this.controller,
      })

      this.views.add(this.sidebar)

      this.sidebar.set(
        'search',
        new media.view.RendezVousUsersSearch({
          controller: this.controller,
          collection: this.model.get('clsusers'),
          model: this.model,
          priority: 80,
        })
      )

      if (!_.isUndefined(wp.media.view.settings.clsMemberTypes)) {
        this.sidebar.set(
          'typeFilter',
          new media.view.RendezVousTypeFilter({
            controller: this.controller,
            collection: this.model.get('clsusers'),
            model: this.model,
            priority: -60,
          }).render()
        )
      }
    },
  })

  media.view.RendezVousUsersPaginate = wp.media.view.Toolbar.extend({
    initialize: function () {
      _this = this

      _.defaults(this.options, {
        event: 'pagination',
        close: false,
        items: {
          // See wp.media.view.Button
          next: {
            id: 'wm-next',
            style: 'secondary',
            text: cls.strings.clsNextBtn,
            priority: -60,
            click: function () {
              this.controller.state().nextPage()
            },
          },
          prev: {
            id: 'wm-prev',
            style: 'secondary',
            text: cls.strings.clsPrevBtn,
            priority: -80,
            click: function () {
              this.controller.state().prevPage()
            },
          },
        },
      })

      wp.media.view.Toolbar.prototype.initialize.apply(this, arguments)

      this.controller.state().get('clsusers').on('sync', this.refresh, this)
    },

    refresh: function () {
      var hasmore = (hasprev = false),
        total = this.controller.state().get('clsusers').options.total_page,
        current = this.controller.state().get('clsusers').options.current_page

      if (this.controller.state().get('content') == 'who' && total > 0) {
        hasmore = Number(total) - Number(current) > 0 ? true : false
        hasprev = Number(current) - 1 > 0 ? true : false
      }

      this.get('next').model.set('disabled', !hasmore)
      this.get('prev').model.set('disabled', !hasprev)
    },
  })

  media.view.RendezVousUsersSearch = wp.media.view.Search.extend({
    attributes: {
      type: 'search',
      placeholder: cls.strings.clsSrcPlaceHolder,
    },

    events: {
      keyup: 'searchUser',
    },

    initialize: function () {
      wp.media.view.Search.prototype.initialize.apply(this, arguments)
    },

    searchUser: function (event) {
      if (event.keyCode != 13) return

      if (event.target.value) this.model.set('search', event.target.value)
      else this.model.unset('search')

      this.collection.options.current_page = 1

      this.model.trigger('change:search')
    },
  })

  media.view.RendezVousTypeFilter = wp.media.view.AttachmentFilters.extend({
    id: 'member-type-filters',

    createFilters: function () {
      var filters = {}
      _.each(
        wp.media.view.settings.clsMemberTypes || {},
        function (value, index) {
          filters[index] = {
            text: value.text,
            props: {
              type: value.type,
            },
          }
        }
      )
      filters.all = {
        text: wp.media.view.settings.clsMemberTypesAll,
        props: {
          type: false,
        },
        priority: 10,
      }
      this.filters = filters
    },

    change: function () {
      var filter = this.filters[this.el.value]
      if (filter) {
        this.model.set('member_type', filter.props.type)
      } else {
        this.model.unset('member_type')
      }

      this.collection.options.current_page = 1

      this.model.trigger('change:member_type')
    },

    select: function () {
      var model = this.model,
        value = 'all',
        props = model.toJSON()

      _.find(this.filters, function (filter, id) {
        var equal = _.all(filter.props, function (prop, key) {
          return (
            prop ===
            (_.isUndefined(props.member_type) ? null : props.member_type)
          )
        })

        if (equal) {
          return (value = id)
        }
      })

      this.$el.val(value)
    },
  })

  media.view.RendezVousUserSelection = wp.media.View.extend({
    tagName: 'div',
    className: 'user-selection',
    template: wp.media.template('user-selection'),

    events: {
      'click .clear-selection': 'clear',
    },

    initialize: function () {
      _.defaults(this.options, {
        editable: false,
        clearable: true,
      })

      this.collection.on('add remove reset', this.refresh, this)
      this.controller.on('content:activate', this.refresh, this)
    },

    refresh: function () {
      if (!this.$el.children().length) return

      var collection = this.collection,
        element = this.$el,
        html = ''

      if (!collection.length) {
        element.addClass('empty')
        element.find('.selection-view ul').html('')
        element.find('.selection-info .count').html('')
        return
      } else {
        element.removeClass('empty')
      }

      element
        .find('.selection-info .count')
        .html(cls.strings.invited.replace('%d', collection.length))

      collection.each(function (model) {
        var avatar = model.get('avatar')
        var user = model.get('id')
        var name = model.get('name')
        html +=
          '<li class="user-avatar"><img src="' +
          avatar +
          '" title="' +
          name +
          '"></li>'
        element.find('.selection-view ul').html(html)
      }, this)
    },

    clear: function (event) {
      event.preventDefault()
      this.collection.reset()
      this.controller.state().props.trigger('change:selection')
    },
  })

  media.controller.RendezVous = wp.media.controller.State.extend({
    defaults: {
      id: 'cw_class',
      menu: 'default',
      content: 'what',
      router: 'steps',
      toolbar: 'cls_insert',
      tabs: {
        what: {
          tpl: 'what',
          text: cls.strings.whatTab,
          priority: 20,
          id: 'what',
        },
        when: {
          tpl: 'when',
          text: cls.strings.whenTab,
          priority: 40,
          id: 'when',
        },
        // who: {
        // 	tpl:'who',
        // 	text : cls.strings.whoTab,
        // 	priority:60,
        // 	id:'who'
        // }
      },
    },

    initialize: function () {
      this.props = new Backbone.Collection()

      this.props.add(
        new Backbone.Model({
          id: '_all',
          selection: new Backbone.Collection(),
          mirror: new Backbone.Collection(),
        })
      )

      this.props.on('change:selection', this.observeChanges, this)

      if (!this.get('clsfields')) this.set('clsfields', new rendezvousFields())
      if (!this.get('clsdays')) this.set('clsdays', new rendezvousDays())

      if (!this.get('clsusers')) this.set('clsusers', new rendezvousUsers())

      this.get('clsusers').on('add', this.fillMirror, this)
    },

    activate: function () {
      var clsfields = this.get('clsfields')
      var clsdays = this.get('clsdays')
      var wmusers = this.get('clsusers')

      this.frame.on('content:render:what', this.manageWhatTab, this)
      this.frame.on('content:render:when', this.manageWhenTab, this)
      // this.frame.on( 'content:render:who', this.manageWhoTab, this );

      this.frame.on('close', this.resetAll, this)

      clsfields.on('change', this.observeChanges, this)
      clsdays.on('change', this.observeChanges, this)
    },

    fillMirror: function (model, collection, options) {
      var mirror = media.frame().state().props.get('_all').get('mirror')

      if (!mirror || !mirror.get(model.id)) mirror.add(model)
    },

    resetAll: function () {
      this.resetFields('clsfields')
      this.resetFields('clsdays')
    },

    resetFields: function (key) {
      const fields = this.get(key)

      for (const field in fields) {
        if (Object.hasOwnProperty.call(fields, field)) {
          fields[field].set('value', null)
        }
      }
    },

    manageWhoTab: function (who) {
      this.set('content', 'who')
      this.frame.toolbar.get().refresh()
    },

    nextPage: function () {
      this.get('clsusers').options.current_page += 1
      this.trigger('change:pagination')
    },

    prevPage: function () {
      this.get('clsusers').options.current_page -= 1
      this.trigger('change:pagination')
    },

    observeChanges: function (model) {
      this.frame.toolbar.get().refresh()
    },

    manageWhatTab: function (what) {
      this.set('content', 'what')
      this.frame.toolbar.get().refresh()
    },

    manageWhenTab: function (when) {
      this.set('content', 'when')
      this.frame.toolbar.get().refresh()
    },

    clsInsert: function () {
      var users, dates, fields, postdata

      users = _.pluck(this.props.get('_all').get('selection').models, 'id')
      dates = _.pluck(this.get('clsdays').models, 'attributes')
      fields = _.pluck(this.get('clsfields').models, 'attributes')

      postdata = {
        json: true,
        attendees: users,
        maydates: dates,
        desc: fields,
        nonce: wp.media.view.settings.nonce.rendezvous,
      }

      if (wp.media.view.settings.group_id) {
        postdata.group_id = wp.media.view.settings.group_id
      }

      wp.media
        .post('create_cw_class', postdata)
        .done(function (link) {
          window.location.href = link
        })
        .fail(function (error) {
          alert(error)
        })
    },
  })

  media.view.ToolbarRendezVous = wp.media.view.Toolbar.extend({
    initialize: function () {
      _this = this

      _.defaults(this.options, {
        event: 'inserter',
        close: false,
        items: {
          // See wp.media.view.Button
          inserter: {
            id: 'cls-button',
            style: 'primary',
            text: cls.strings.saveButton,
            priority: 80,
            click: function () {
              this.controller.state().clsInsert()
            },
          },
        },
      })

      wp.media.view.Toolbar.prototype.initialize.apply(this, arguments)

      this.set(
        'userSelection',
        new media.view.RendezVousUserSelection({
          controller: media.frame(),
          collection: this.controller
            .state()
            .props.get('_all')
            .get('selection'),
          priority: -40,
          editable: false,
        })
      )
    },

    refresh: function () {
      var disabled = true,
        fields = [],
        days = false

      _.each(this.controller.state().get('clsfields').models, function (model) {
        if (model.attributes.class == 'required') {
          if (model.attributes.value.length > 1) fields.push(model.id)
          else fields = []
        }
      })

      disabled = !fields.length > 0

      if (!disabled) {
        _.each(this.controller.state().get('clsdays').models, function (model) {
          if (
            (
              model.attributes.hour1 +
              model.attributes.hour2 +
              model.attributes.hour3
            ).length > 3
          )
            disabled = false
          days = true
        })
      }

      //
      // Below code checks for selected users. We disable it for CoreWeapons
      //

      // if ( ! disabled ) {
      // 	var selection = this.controller.state().props.get( '_all' ).get( 'selection' );

      // 	disabled = !selection.length;
      // }

      // ///////////////////

      this.get('inserter').model.set('disabled', disabled)
    },
  })

  media.view.stepsItem = wp.media.view.RouterItem.extend({
    // Attempts to fix tabs not appearing in certain themes
    tagName: 'a',
    className: 'media-menu-item faux-button',

    initialize: function () {
      wp.media.view.RouterItem.prototype.initialize.apply(this, arguments)
    },
  })

  media.view.stepsRouter = wp.media.view.Router.extend({
    ItemView: media.view.stepsItem,

    initialize: function () {
      wp.media.view.Router.prototype.initialize.apply(this, arguments)
    },
  })
  ;(media.buttonId = '#new-cw_class'),
    _.extend(media, {
      frame: function () {
        if (this._frame) return this._frame

        var view,
          _this = this,
          _tabs,
          states = [
            new media.controller.RendezVous({
              title: cls.strings.clsMainTitle,
              id: 'cw_class',
            }),
          ]

        this._frame = wp.media({
          className: 'media-frame',
          states: states,
          state: 'cw_class',
        })

        _.each(states, function (item) {
          if (item.id == 'cw_class') {
            _tabs = item.attributes.tabs

            for (tab in item.attributes.tabs) {
              _this._frame.on(
                'content:render:' + tab,
                _.bind(_this.clsContentRender, this, item.attributes.tabs[tab])
              )
            }
          }
        })

        this._frame.on('open', this.open)
        this._frame.on('close', this.close)

        this._frame.on('router:create:steps', this.createRouter, this)
        this._frame.on(
          'router:render:steps',
          _.bind(this.stepsRouter, this, _tabs)
        )
        this._frame.on(
          'toolbar:create:cls_insert',
          _.bind(this.clsToolbarCreate, this, _tabs)
        )

        return this._frame
      },

      createRouter: function (router) {
        router.view = new media.view.stepsRouter({
          controller: this._frame,
        })
      },

      // Routers
      stepsRouter: function (routerItems, view) {
        var tabs = {}

        for (var tab in routerItems) {
          tabs[tab] = {
            text: routerItems[tab].text,
            priority: routerItems[tab].priority,
          }
        }

        view.set(tabs)
      },

      clsContentRender: function (tab) {
        media.frame().content.set(
          new cls.media.view.RendezVous({
            controller: media.frame(),
            model: media.frame().state(),
            tab: tab,
          })
        )
      },

      clsToolbarCreate: function (tabs, toolbar) {
        toolbar.view = new cls.media.view.ToolbarRendezVous({
          controller: media.frame(),
          tab: tabs,
        })
      },

      open: function () {
        $('.media-modal').addClass('smaller')
      },

      close: function () {
        console.log('media.frame() =', media.frame())
        $('.media-modal').removeClass('smaller')
      },

      menuRender: function (view) {
        // view.unset( 'library-separator' );
        // view.unset( 'embed' );
        // view.unset( 'gallery' );
      },

      select: function () {
        var settings = wp.media.view.settings,
          selection = this.get('selection')

        $('.added').remove()
        media.set(selection)
      },

      init: function () {
        $(media.buttonId).on('click', function (e) {
          e.preventDefault()

          media.frame().open()
        })
      },
    })

  $(media.init)
})(jQuery)
