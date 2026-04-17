const produtos = [
  { id: 1, nome: "Mouse", preco: 25.9 },
  { id: 2, nome: "Teclado", preco: 89.9 },
  { id: 3, nome: "Caderno", preco: 18.0 },
  { id: 4, nome: "Fone de ouvido", preco: 59.9 },
  { id: 5, nome: "Caneta", preco: 4.5 }
];

let carrinho = [];
let filtroAtual = "todos";

const listaProdutos = document.querySelector("#listaProdutos");
const listaCarrinho = document.querySelector("#listaCarrinho");
const totalCompra = document.querySelector("#totalCompra");
const filtroProdutos = document.querySelector("#filtroProdutos");
const limparCarrinhoBtn = document.querySelector("#limparCarrinho");

// Quando a página carregar, já monta os negócios na tela
document.addEventListener("DOMContentLoaded", () => {
  carregarCarrinho();
  listarProdutos();
  atualizarCarrinho();
});

// Atualiza a lista de produtos quando o filtro muda
filtroProdutos.addEventListener("change", (evento) => {
  filtroAtual = evento.target.value;
  listarProdutos();
});

// Limpa o carrinho óbvio
limparCarrinhoBtn.addEventListener("click", () => {
  carrinho = [];
  salvarCarrinho();
  atualizarCarrinho();
});

// Mostra os produtos na tela e mantém o filtro escolhido
function listarProdutos() {
  listaProdutos.innerHTML = "";

  const produtosFiltrados = filtrarProdutos(produtos, filtroAtual);

  produtosFiltrados.forEach((produto) => {
    const div = document.createElement("div");
    div.className = "produto";

    div.innerHTML = `
      <p><strong>${produto.nome}</strong></p>
      <p>Preço: R$ ${produto.preco.toFixed(2)}</p>
    `;

    const botao = document.createElement("button");
    botao.textContent = "Adicionar ao Carrinho";
    botao.addEventListener("click", () => adicionarAoCarrinho(produto.id));

    div.appendChild(botao);
    listaProdutos.appendChild(div);
  });
}

// Adiciona o produto no carrinho e aumenta a quantidade se ele já existir
function adicionarAoCarrinho(idProduto) {
  const produto = produtos.find((p) => p.id === idProduto);
  const itemExistente = carrinho.find((item) => item.id === idProduto);

  if (itemExistente) {
    itemExistente.quantidade++;
  } else {
    carrinho.push({
      id: produto.id,
      nome: produto.nome,
      preco: produto.preco,
      quantidade: 1
    });
  }

  salvarCarrinho();
  atualizarCarrinho();
}

// Remove um item do carrinho, diminuindo a quantidade ou tirando de vez
function removerDoCarrinho(idProduto) {
  const item = carrinho.find((p) => p.id === idProduto);

  if (item) {
    if (item.quantidade > 1) {
      item.quantidade--;
    } else {
      carrinho = carrinho.filter((p) => p.id !== idProduto);
    }
  }

  salvarCarrinho();
  atualizarCarrinho();
}

// Atualiza o que aparece no carrinho e calcula o preço
function atualizarCarrinho() {
  listaCarrinho.innerHTML = "";

  if (carrinho.length === 0) {
    listaCarrinho.innerHTML = "<p>Carrinho vazio</p>";
    totalCompra.innerHTML = "<strong>Total:</strong> R$ 0,00";
    return;
  }

  let total = 0;

  carrinho.forEach((item) => {
    const totalItem = item.preco * item.quantidade;
    total += totalItem;

    const div = document.createElement("div");
    div.className = "item-carrinho";

    div.innerHTML = `
      <p><strong>${item.nome}</strong></p>
      <p>Quantidade: ${item.quantidade}</p>
      <p>Preço total: R$ ${totalItem.toFixed(2)}</p>
    `;

    const botao = document.createElement("button");
    botao.className = "remover";
    botao.textContent = "Remover";
    botao.addEventListener("click", () => removerDoCarrinho(item.id));

    div.appendChild(botao);
    listaCarrinho.appendChild(div);
  });

  totalCompra.innerHTML = `<strong>Total:</strong> R$ ${total.toFixed(2)}`;
}

// Guarda o carrinho no navegador para quando voltar
function salvarCarrinho() {
  localStorage.setItem("carrinho", JSON.stringify(carrinho));
}

// Recupera o carrinho salvo antes
function carregarCarrinho() {
  const dados = localStorage.getItem("carrinho");

  if (dados) {
    carrinho = JSON.parse(dados);
  }
}

// Aplica o filtro escolhido no select usando switch
function filtrarProdutos(lista, filtro) {
  switch (filtro) {
    case "ate50":
      return lista.filter((produto) => produto.preco <= 50);
    case "acima50":
      return lista.filter((produto) => produto.preco > 50);
    default:
      return lista;
  }
}
