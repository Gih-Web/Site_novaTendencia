// ===================== CARROSSEL DE BANNERS ===================== //
(function () {
  const esc = s => (s ?? "").toString().replace(/[&<>"']/g, c => (
    {"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c]
  ));

  const placeholder = (w = 1200, h = 400, txt = "SEM IMAGEM") =>
    "data:image/svg+xml;base64," + btoa(
      `<svg xmlns="http://www.w3.org/2000/svg" width="${w}" height="${h}">
        <rect width="100%" height="100%" fill="#e9ecef"/>
        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
              font-family="Arial, sans-serif" font-size="28" fill="#6c757d">${txt}</text>
      </svg>`
    );

  const hojeYMD = new Date().toISOString().slice(0,10);
  const dentroDaValidade = d => (!d ? true : d >= hojeYMD);

  function resolveImagemSrc(b) {
    if (!b) return placeholder();
    if (b.imagem && typeof b.imagem === "string") return "data:image/jpeg;base64," + b.imagem;
    return placeholder();
  }

  function renderErro(container, titulo, detalhesHtml) {
    container.innerHTML = `
      <div class="carousel-item active">
        <div class="p-3">
          <div class="alert alert-danger mb-2"><strong>${esc(titulo)}</strong></div>
          <div class="alert alert-light border small" style="white-space:pre-wrap">${detalhesHtml}</div>
        </div>
      </div>`;
    const ind = document.getElementById("banners-indicators");
    if (ind) ind.innerHTML = "";
  }

  function renderCarrossel(container, indicators, banners) {
    if (!Array.isArray(banners) || !banners.length) {
      renderErro(container, "Nenhum banner disponível.", "O servidor respondeu com sucesso, porém a lista veio vazia.");
      return;
    }

    const itemsHtml = banners.map((b, i) => {
      const active = i === 0 ? "active" : "";
      const src = resolveImagemSrc(b);
      const desc = (b.descricao ?? "Banner").toString();
      const link = b.link ? String(b.link) : null;

      const imgTag = `<img src="${src}" class="d-block w-100" alt="${esc(desc)}" loading="lazy" style="object-fit:cover; height:400px;">`;
      return `<div class="carousel-item ${active}">${link ? `<a href="${esc(link)}" target="_blank" rel="noopener noreferrer">${imgTag}</a>` : imgTag}</div>`;
    }).join("");

    container.innerHTML = itemsHtml;

    if (indicators) {
      const indicatorsHtml = banners.map((_, i) =>
        `<button type="button" data-bs-target="#carouselBanners" data-bs-slide-to="${i}" class="${i===0?"active":""}" aria-label="Slide ${i+1}"></button>`
      ).join("");
      indicators.innerHTML = indicatorsHtml;
    }
  }

  async function fetchBanners(url) {
    try {
      const res = await fetch(url, { headers: { "Accept": "application/json" } });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();
      if (!data.ok || !Array.isArray(data.banners)) throw new Error("Formato de resposta inválido");
      return data.banners;
    } catch (err) {
      return { erro: err.message };
    }
  }

  async function listarBannersCarrossel({
    containerSelector = "#banners-home",
    indicatorsSelector = "#banners-indicators",
    url = "../PHP/banners.php?listar=1",
    apenasValidos = true
  } = {}) {
    const container = document.querySelector(containerSelector);
    const indicators = document.querySelector(indicatorsSelector);
    if (!container) return;

    container.innerHTML = `<div class="carousel-item active"><div class="p-3 text-muted">Carregando banners…</div></div>`;
    if (indicators) indicators.innerHTML = "";

    const banners = await fetchBanners(url);
    if (banners.erro) {
      renderErro(container, "Não foi possível carregar os banners.", banners.erro);
      return;
    }

    let lista = banners.slice();
    if (apenasValidos) lista = lista.filter(b => dentroDaValidade(b.data_validade));

    renderCarrossel(container, indicators, lista);
  }

  document.addEventListener("DOMContentLoaded", () => {
    listarBannersCarrossel({
      url: "../PHP/banners.php?listar=1",
      apenasValidos: true
    });
  });
})();
