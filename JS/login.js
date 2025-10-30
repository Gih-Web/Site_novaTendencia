document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-login');
  if (!form) return;

  const emailEl = document.getElementById('email');
  const senhaEl = document.getElementById('senha');

  const showMsg = (msg) => {
    alert(msg); // pode trocar por toast Bootstrap se quiser
  };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = (emailEl.value || '').trim();
    const senha = (senhaEl.value || '').trim();

    if (!email || !senha) {
      showMsg('Preencha e-mail e senha.');
      return;
    }

    try {
      const resp = await fetch('../PHP/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, senha })
      });

      const data = await resp.json();

      if (data.ok) {
        showMsg('Login realizado com sucesso!');
        setTimeout(() => {
          window.location.href = data.redirect;
        }, 800);
      } else {
        showMsg(data.msg || 'Credenciais inválidas.');
      }
    } catch (err) {
      console.error(err);
      showMsg('Erro de conexão com o servidor.');
    }
  });

  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.target);
      target.type = target.type === 'password' ? 'text' : 'password';
    });
  });

  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("cadastro") === "ok") {
    showMsg("Cadastro realizado com sucesso! Faça login.");
  }
  if (urlParams.get("erro")) {
    showMsg(urlParams.get("erro"));
  }
});
