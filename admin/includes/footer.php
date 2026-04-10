
<script>
// Flash hide
setTimeout(() => document.querySelectorAll('.alert').forEach(a => a.style.transition='opacity .5s'), 2500);
setTimeout(() => document.querySelectorAll('.alert').forEach(a => a.style.opacity='0'), 3000);

// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm || 'Emin misiniz?')) e.preventDefault();
  });
});

// Image preview
document.querySelectorAll('input[type=file][data-preview]').forEach(inp => {
  inp.addEventListener('change', function() {
    const prev = document.getElementById(this.dataset.preview);
    if (prev && this.files[0]) {
      prev.src = URL.createObjectURL(this.files[0]);
      prev.style.display = 'block';
    }
  });
});
</script>
</body></html>
