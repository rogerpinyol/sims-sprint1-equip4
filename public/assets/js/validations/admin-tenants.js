document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('form.tenant-action').forEach(function(form){
    form.addEventListener('submit', function(e){
      var msg = form.getAttribute('data-confirm') || 'Are you sure?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });
});