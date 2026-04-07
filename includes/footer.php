<?php // includes/footer.php ?>
<!-- end main-content -->

<!-- Toast -->
<div class="toast-container">
  <div id="liveToast" class="toast align-items-center text-white border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastMsg"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showToast(msg, type = 'success') {
  const el = document.getElementById('liveToast');
  el.className = `toast align-items-center text-white border-0 bg-${type === 'success' ? 'success' : 'danger'}`;
  document.getElementById('toastMsg').textContent = msg;
  new bootstrap.Toast(el, { delay: 3000 }).show();
}
</script>

<div class="container text-center fixed-bottom mw-100 z-n1" style="font-size:.8rem;color:#aaa;padding:6px 0;">
  &copy; <?= date('Y') ?> &nbsp;|&nbsp;
  Developed by <a href="../developer.php" style="color:#C8860A;text-decoration:none;font-weight:600;">A6h9</a>
</div>
</body>
</html>
