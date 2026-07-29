document.addEventListener('DOMContentLoaded', function () {
  const search = document.getElementById('searchGlobal');
  const deleteLinks = document.querySelectorAll('.excluir');

  deleteLinks.forEach(function (link) {
    link.addEventListener('click', function (event) {
      if (!confirm('Tem certeza que deseja excluir este registro?')) {
        event.preventDefault();
      }
    });
  });

  if (search) {
    search.addEventListener('input', function () {
      const texto = this.value.toLowerCase().trim();
      const linhas = document.querySelectorAll('tbody tr');

      linhas.forEach(function (linha) {
        const conteudo = (linha.getAttribute('data-search') || linha.textContent || '').toLowerCase();
        linha.style.display = conteudo.includes(texto) ? '' : 'none';
      });
    });
  }
});
