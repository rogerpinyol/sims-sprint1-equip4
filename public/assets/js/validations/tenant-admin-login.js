(function(){
  var form = document.getElementById('sa-login-form');
  if(!form) return;
  var email = form.querySelector('input[name="email"]');
  var pwd = form.querySelector('input[name="password"]');
  var emailErr = document.getElementById('email-error');
  var pwdErr = document.getElementById('password-error');
  function ve(){
    if(!email.value || !email.checkValidity()) { emailErr.textContent='Enter a valid email.'; emailErr.classList.remove('hidden'); return false; }
    emailErr.textContent=''; emailErr.classList.add('hidden'); return true;
  }
  function vp(){
    if(!pwd.value){ pwdErr.textContent='Password required.'; pwdErr.classList.remove('hidden'); return false; }
    pwdErr.textContent=''; pwdErr.classList.add('hidden'); return true;
  }
  email.addEventListener('input', ve); email.addEventListener('blur', ve);
  pwd.addEventListener('input', vp); pwd.addEventListener('blur', vp);
  form.addEventListener('submit', function(e){ if(!(ve() & vp())) e.preventDefault(); });
})();