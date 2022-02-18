;(function ($) {
  /**
   * My first idea wa to use Backbone to build the interface
   * I think the Members profile page as they don't accept
   * action_variables in url, it's a good place to do so.
   * As it's quite long to build, this first version of the
   * plugin will use query vars like ?key=id
   */

  $('#cw_class-attendees-prefs :checkbox').on('click', function () {
    if ($(this).hasClass('none-resets-cb') && $(this).prop('checked')) {
      $(this)
        .parents('.edited')
        .first()
        .find(':checkbox')
        .each(function () {
          $(this).prop('checked', false)
        })

      $(this).prop('checked', true)
    } else {
      $('.none-resets-cb').prop('checked', false)
    }
  })

  $('#cw_class-edit-privacy').on('click', function () {
    if ($(this).prop('checked')) {
      $('#cw_class-edit-activity')
        .prop('checked', false)
        .prop('disabled', true)
    } else {
      $('#cw_class-edit-activity').prop('disabled', false)
    }
  })

  $('#cw_class-edit-notify').on('click', function () {
    if ($(this).prop('checked')) {
      $('#cw_class-custom-message').prop('disabled', false)
    } else {
      $('#cw_class-custom-message').prop('disabled', true)
    }
  })

  $('#cw_class-list li.private a').on('click', function (e) {
    if ($(this).prop('href').indexOf('#noaccess') != -1) {
      e.preventDefault()

      alert(cw_class_vars.noaccess)
      return
    }
  })

  $('.delete-cw_class').on('click', function (e) {
    if (false == confirm(cw_class_vars.confirm)) {
      e.preventDefault()
      return
    }
  })

  $(document).ready(function () {
    var setDate = $(location).attr('hash')

    if ('undefined' != typeof setDate && setDate) {
      $(setDate).parent('tr').css({
        border: 'solid 2px #298cba',
      })
    }
  })
})(jQuery)
