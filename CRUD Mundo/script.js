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

  // Tela de troca de senha, avisa se a nova senha e a confirmação são iguais.
  const novaSenha = document.getElementById('novaSenha');
  const confirmarSenha = document.getElementById('confirmarSenha');
  const avisoSenha = document.getElementById('avisoSenha');

  if (novaSenha && confirmarSenha && avisoSenha) {
    const verificarSenhas = function () {
      if (confirmarSenha.value === '') {
        avisoSenha.textContent = '';
        return;
      }

      if (novaSenha.value === confirmarSenha.value) {
        avisoSenha.textContent = 'As senhas coincidem.';
        avisoSenha.style.color = '#1a7a1a';
      } else {
        avisoSenha.textContent = 'As senhas não coincidem.';
        avisoSenha.style.color = '#b3261e';
      }
    };

    novaSenha.addEventListener('input', verificarSenhas);
    confirmarSenha.addEventListener('input', verificarSenhas);
  }
});
