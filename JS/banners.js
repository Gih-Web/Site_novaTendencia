// ===================== Carregar categorias =====================
async function listarCategorias(selectId) {
    const selectCategoria = document.querySelector(selectId);
    try {
        const r = await fetch("../PHP/cadastro_banners.php?categorias=1");
        const cats = await r.json();
        selectCategoria.innerHTML = '<option value="">— Sem vínculo —</option>';
        cats.forEach(c => {
            const opt = document.createElement("option");
            opt.value = c.id;
            opt.textContent = c.nome;
            selectCategoria.appendChild(opt);
        });
    } catch(e) {
        console.error("Erro ao listar categorias", e);
    }
}

// ===================== Listar banners =====================
async function listarBanners(tbodyId) {
    const tabelaBanners = document.querySelector(tbodyId);
    try {
        const r = await fetch("../PHP/cadastro_banners.php?listar=1");
        const banners = await r.json();
        tabelaBanners.innerHTML = "";
        banners.forEach(b => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td><img src="data:image/jpeg;base64,${b.imagem}" class="mini-banner rounded" /></td>
                <td>${b.descricao}</td>
                <td>${b.link}</td>
                <td>${b.categoria_nome || "—"}</td>
                <td>${b.validade || "—"}</td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary btn-edit" data-id="${b.id}">Editar</button>
                        <button class="btn btn-outline-danger btn-del" data-id="${b.id}">Excluir</button>
                    </div>
                </td>
            `;
            tabelaBanners.appendChild(tr);
        });
        attachActions();
    } catch(e) {
        console.error("Erro ao listar banners", e);
    }
}

// ===================== Inicialização =====================
listarCategorias("#bannerCategoria");
listarBanners("#bannersTable tbody");
