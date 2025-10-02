const toggleBtn = document.getElementById('toggle-btn');
const sidebar = document.getElementById('sidebar');
const salesBtn = document.getElementById('sales-btn');

toggleBtn.addEventListener('click', () => {
  sidebar.classList.toggle('active');
});

// Quando clicar no ícone de gráfico
salesBtn.addEventListener('click', () => {
  // Aqui você define a página para onde ele vai
  window.location.href = "relatorios.html";
});

// Por enquanto sem lógica extra, só exemplo
document.querySelector(".resumo-grafico").addEventListener("click", () => {
  alert("Você clicou no gráfico!");
});
