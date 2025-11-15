document.addEventListener('DOMContentLoaded', function(){
  var form = document.getElementById('delete-account');
  var link = document.getElementById('delete-account-link');
  if (link && form) {
    link.addEventListener('click', function(e){
      e.preventDefault();
      if (confirm('¿Seguro que quieres eliminar tu cuenta? Esta acción no se puede deshacer.')) {
        form.submit();
      }
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

    // Disable button initially
    saveButton.disabled = true;

    // Function to check for changes
    function checkChanges() {
      var changed = false;
      inputs.forEach(function(input) {
        if (input.value !== initialValues[input.name]) {
          changed = true;
        }
      });
      saveButton.disabled = !changed;
    }

    // Add event listeners to inputs
    inputs.forEach(function(input) {
      input.addEventListener('input', checkChanges);
      input.addEventListener('change', checkChanges);
    });
  }
});