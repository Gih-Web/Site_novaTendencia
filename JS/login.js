document.addEventListener('DOMContentLoaded', () => {
  const form  = document.querySelector('form'); // seu form-login
  const emailEl = document.getElementById('email');
  const senhaEl = document.getElementById('senha');

  const showMsg = (msg) => {
    alert(msg); // mantém alert como no seu código
  };

  form.addEventListener('submit', (e) => {
    // valida campos antes de enviar
    const email = (emailEl.value || '').trim();
    const senha = (senhaEl.value || '').trim();

    if (!email || !senha) {
      e.preventDefault();
      showMsg('Preencha todos os campos.');
      return;
    }

    // valida e-mail
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      e.preventDefault();
      showMsg('E-mail inválido.');
      return;
    }

    // se passou, deixa o form submeter normalmente para o PHP
  });

  // Toggle senha
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.target);
      target.type = target.type === 'password' ? 'text' : 'password';
    });
  });

  // mostra mensagens vindas da URL
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("cadastro") === "ok") {
    showMsg("Cadastro realizado com sucesso! Faça login.");
  }
  if (urlParams.get("erro")) {
    showMsg(urlParams.get("erro"));
  }
});
