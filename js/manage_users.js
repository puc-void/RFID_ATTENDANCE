$(document).ready(function(){
  // Add / Register User
  $(document).on('click', '.user_add', function(){
    var user_id = $('#user_id').val();
    var name = $('#name').val();
    var number = $('#number').val();
    var phone = $('#phone').val();
    var email = $('#email').val();
    var gender = $(".gender:checked").val();
    var dev_uid = $('#dev_sel option:selected').val();

    if (!user_id) {
      if (typeof showToast === 'function') {
        showToast('Select a Card First', 'Click a Card UID button in the table to select a card before registering.', 'warning');
      }
      return;
    }

    $.ajax({
      url: 'manage_users_conf.php',
      type: 'POST',
      data: {
        'Add': 1,
        'user_id': user_id,
        'name': name,
        'number': number,
        'phone': phone,
        'email': email,
        'dev_uid': dev_uid,
        'gender': gender,
      },
      success: function(response){
        if (response == 1) {
          $('#user_id').val('');
          $('#name').val('');
          $('#number').val('');
          $('#phone').val('');
          $('#email').val('');
          $('#dev_sel').val('0');
          $('#selectedCardBadge').removeClass('active').text('No Card Selected');

          if (typeof showToast === 'function') {
            showToast('User Registered', 'New student has been enrolled and Telegram alert dispatched.', 'success');
          }
        } else {
          if (typeof showToast === 'function') {
            showToast('Registration Notice', response, 'danger');
          }
        }

        $.ajax({
          url: "manage_users_up.php"
        }).done(function(data) {
          $('#manage_users').html(data);
        });
      }
    });
  });

  // Update User
  $(document).on('click', '.user_upd', function(){
    var user_id = $('#user_id').val();
    var name = $('#name').val();
    var number = $('#number').val();
    var phone = $('#phone').val();
    var email = $('#email').val();
    var gender = $(".gender:checked").val();
    var dev_uid = $('#dev_sel option:selected').val();

    if (!user_id) {
      if (typeof showToast === 'function') {
        showToast('No User Selected', 'Please click a Card UID in the table first to update.', 'warning');
      }
      return;
    }

    $.ajax({
      url: 'manage_users_conf.php',
      type: 'POST',
      data: {
        'Update': 1,
        'user_id': user_id,
        'name': name,
        'number': number,
        'phone': phone,
        'email': email,
        'dev_uid': dev_uid,
        'gender': gender,
      },
      success: function(response){
        if (response == 1) {
          $('#user_id').val('');
          $('#name').val('');
          $('#number').val('');
          $('#phone').val('');
          $('#email').val('');
          $('#dev_sel').val('0');
          $('#selectedCardBadge').removeClass('active').text('No Card Selected');

          if (typeof showToast === 'function') {
            showToast('User Updated', 'Student information has been saved.', 'success');
          }
        } else {
          if (typeof showToast === 'function') {
            showToast('Update Error', response, 'danger');
          }
        }

        $.ajax({
          url: "manage_users_up.php"
        }).done(function(data) {
          $('#manage_users').html(data);
        });
      }
    });
  });

  // Delete User with shadcn Confirmation Dialog
  $(document).on('click', '.user_rmo', function(){
    var user_id = $('#user_id').val();
    var user_name = $('#name').val() || 'this user';

    if (!user_id) {
      if (typeof showToast === 'function') {
        showToast('No User Selected', 'Click a Card UID in the table first to remove a user.', 'warning');
      }
      return;
    }

    var performDelete = function() {
      $.ajax({
        url: 'manage_users_conf.php',
        type: 'POST',
        data: {
          'delete': 1,
          'user_id': user_id,
        },
        success: function(response){
          if (response == 1) {
            $('#user_id').val('');
            $('#name').val('');
            $('#number').val('');
            $('#phone').val('');
            $('#email').val('');
            $('#dev_sel').val('0');
            $('#selectedCardBadge').removeClass('active').text('No Card Selected');

            if (typeof showToast === 'function') {
              showToast('User Removed', 'The selected user and RFID card have been removed.', 'success');
            }
          } else {
            if (typeof showToast === 'function') {
              showToast('Delete Failed', response, 'danger');
            }
          }

          $.ajax({
            url: "manage_users_up.php"
          }).done(function(data) {
            $('#manage_users').html(data);
          });
        }
      });
    };

    if (typeof showConfirmModal === 'function') {
      showConfirmModal({
        title: 'Remove User & Card?',
        message: 'Are you sure you want to permanently remove <b>' + $('<div>').text(user_name).html() + '</b>? Their RFID card will no longer grant attendance access.',
        variant: 'danger',
        confirmText: 'Yes, Remove User',
        onConfirm: performDelete
      });
    } else {
      bootbox.confirm("Do you really want to delete this User?", function(result) {
        if (result) performDelete();
      });
    }
  });

  // Select Card Row
  $(document).on('click', '.select_btn', function(){
    var card_uid = $(this).attr("id");
    $.ajax({
      url: 'manage_users_conf.php',
      type: 'GET',
      data: {
        'select': 1,
        'card_uid': card_uid,
      },
      success: function(response){
        if (typeof showToast === 'function') {
          showToast('Card Selected', 'Loaded UID ' + card_uid + ' into the assignment form.', 'info', 2200);
        }

        $('#selectedCardBadge').addClass('active').text('UID: ' + card_uid);

        $.ajax({
          url: "manage_users_up.php"
        }).done(function(data) {
          $('#manage_users').html(data);
        });

        if (response && response.length > 0) {
          var u = response[0];
          $('#user_id').val(u.id);
          $('#name').val(u.username !== 'None' ? u.username : '');
          $('#number').val(u.serialnumber != 0 ? u.serialnumber : '');
          $('#phone').val((u.phone && u.phone !== 'None') ? u.phone : '');
          $('#email').val(u.email !== 'None' ? u.email : '');
          $('#dev_sel').val(u.device_uid === 'All' ? '0' : u.device_uid);

          if (u.gender === 'Female') {
            $('.form-style-5').find(':radio[name=gender][value="Female"]').prop('checked', true);
          } else {
            $('.form-style-5').find(':radio[name=gender][value="Male"]').prop('checked', true);
          }
        }
      }
    });
  });
});
