document.addEventListener('DOMContentLoaded', function(){
  var form = document.getElementById('delete-account');
  var link = document.getElementById('delete-account-link');
  if (link && form) {
    link.addEventListener('click', function(e){
      e.preventDefault();
      Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  }

  // Profile form change detection
  var profileForm = document.querySelector('form[action="/profile"]');
  var saveButton = profileForm ? profileForm.querySelector('button[type="submit"]') : null;
  if (profileForm && saveButton) {
    // Store initial values
    var initialValues = {};
    var inputs = profileForm.querySelectorAll('input, textarea');
    inputs.forEach(function(input) {
      initialValues[input.name] = input.value;
    });

    // Detect changes
    inputs.forEach(function(input) {
      input.addEventListener('input', function() {
        var hasChanges = Array.from(inputs).some(function(input) {
          return input.value !== initialValues[input.name];
        });
        saveButton.disabled = !hasChanges;
      });
    });
  }
});