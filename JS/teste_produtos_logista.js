function listarcategorias(nomeid){

    
(async() => {
    //
    const sel  = document.querySelector(nomeid); 
   
    try {
        //criando variável que guarda os dados vindo do php, que estão no metodo listar
        const r  = await fetch("../PHP/cadastro_categorias.php?listar=1");
        // Se o retorno do php vier falso significa que não foi possivel listar os dados 
       if(!r.ok) throw new Error ("Falha ao listar categorias!");

        /* SE vier dados do php, ele joga as informações dentro do campo html em formato de texto 
        innerHTML - inserir dados em elementos html
        */
       
        sel.innerHTML = await r.text ();
     
    } catch (e) { 

        //se dê erro na listagem, aparece Erro ao carregar dentro do campo html 
        sel.innerHTML = "<option disable> Erro ao carregar </option>";
   
          
    }  
  
} )(); 

}

listarcategorias("#pCategoria");
listarcategorias("#prodCategoria");
