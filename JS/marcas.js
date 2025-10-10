(async () => {
    const tbody = document.querySelector("#marcasTable tbody");

    try {
        const r = await fetch("../PHP/cadastro_marcas.php?listar=1");
        if (!r.ok) throw new Error("Falha ao listar marcas!");

        tbody.innerHTML = await r.text();
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center">Erro ao carregar marcas</td></tr>';
        console.error(e);
    }
})();
