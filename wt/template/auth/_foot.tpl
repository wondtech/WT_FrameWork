    <div class="auth-credit">
      {$foot_made} <img width="14" height="14" src="/pub_wt/imgs/admin/wt.png" alt="WondTech">
      <a href="https://wondtech.com" target="_blank" rel="noopener">WondTech</a>
    </div>
  </div>
</main>

<script>
{literal}
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.pw-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var pw = btn.parentNode.querySelector('input');
      if (!pw) return;
      var show = pw.type === 'password';
      pw.type = show ? 'text' : 'password';
      var ic = btn.querySelector('i');
      if (ic) ic.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    });
  });
  var cr = document.getElementById('capRefresh');
  if (cr) cr.addEventListener('click', function () {
    fetch('/auth/captcha').then(function (r) { return r.json(); }).then(function (d) {
      var img = document.getElementById('capImg');
      if (img && d.img) img.src = 'data:image/png;base64,' + d.img;
    }).catch(function () {});
  });
});
{/literal}
</script>
</body>
</html>
