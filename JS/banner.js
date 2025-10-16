async function listarBanners() {
    const tbody = document.querySelector("#bannersTableBody");
    if (!tbody) return;

    try {
        const res = await fetch("../PHP/cadastro_banners.php?acao=listar");
        const dados = await res.json();
        if (dados.status !== "ok") throw new Error(dados.mensagem);

        tbody.innerHTML = "";
        dados.data.forEach(banner => {
            const tr = document.createElement("tr");
            const imgSrc = banner.imagem ? `data:image/jpeg;base64,${btoa(String.fromCharCode(...new Uint8Array(banner.imagem.data || [])))}` : '';
            tr.innerHTML = `
                <td>${imgSrc ? `<img src="${imgSrc}" class="mini-banner rounded" width="80">` : '-'}</td>
                <td>${banner.descricao}</td>
                <td>${banner.link || '-'}</td>
                <td>${banner.categoria_nome || '-'}</td>
                <td>${banner.data_validade || '-'}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-secondary">Editar</button>
                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

    } catch (erro) {
        console.error(erro);
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Erro ao carregar banners.</td></tr>`;
    }
}

async function cadastrarBanner(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const res = await fetch("../PHP/cadastro_banners.php", {
            method: "POST",
            body: formData
        });
        const dados = await res.json();
        if (dados.status !== "ok") throw new Error(dados.mensagem);

        alert(dados.mensagem);
        form.reset();
        listarBanners();

    } catch (erro) {
        alert("Erro: " + erro.message);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    listarBanners();
    const form = document.querySelector("#formBanner");
    if (form) form.addEventListener("submit", cadastrarBanner);
});
