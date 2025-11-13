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
});