// Função para mostrar o modal de mensagens
function showNotificationModal(message, type) {
    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    const modalIcon = document.getElementById('modalIcon');
    const modalMessage = document.getElementById('modalMessage');
    
    if(type === 'success') {
        modalIcon.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
        modalMessage.className = 'my-3 text-success';
    } else {
        modalIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger"></i>';
        modalMessage.className = 'my-3 text-danger';
    }
    
    modalMessage.textContent = message;
    modal.show();
    
    const timeout = type === 'success' ? 5000 : 10000;
    setTimeout(() => modal.hide(), timeout);
    
    modal._element.addEventListener('hidden.bs.modal', () => {
        if(type === 'success') {
            window.location.reload(); // Só recarrega em caso de sucesso
        }
    }, {once: true});
}

// Script para abrir o modal de visualização de reservas do hospede selecionado
document.addEventListener('DOMContentLoaded', function () {
    const botoesVerReservas = document.querySelectorAll('.ver-reservas-btn');
    botoesVerReservas.forEach(botao => {
        botao.addEventListener('click', function () {
        const idhotel = this.getAttribute('reservas');

        fetch('hoteis.php', {
            method: 'POST',
            headers: {
            'Content-Type': 'application/json'
            },
            body: JSON.stringify({ idhotel: idhotel })
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('corpo-tabela-reservas').innerHTML = data;
            const modal = new bootstrap.Modal(document.getElementById('modalReservas'));
            modal.show();
        })
        .catch(error => {
            console.error('Erro ao buscar reservas:', error);
            document.getElementById('corpo-tabela-reservas').innerHTML = '<tr><td colspan="8"> Erro ao carregar reservas. </td></tr>';
        })
        })
    })
})

// Função para extrair partes do endereço
function parseEndereco(enderecoCompleto) {
    if (!enderecoCompleto) return { tipo: '', nome: '', numero: '' };
    
    // Padrão: "Tipo Nome, Número"
    const regex = /^([A-Za-zÀ-ú]+\.?)\s(.+?),\s(\d+.*)$/;
    const match = enderecoCompleto.match(regex);
    
    if (match) {
        return {
            tipo: match[1],
            nome: match[2],
            numero: match[3]
        };
    }
    
    // Padrão alternativo caso o primeiro não funcione
    const parts = enderecoCompleto.split(/\s(.+?),\s(\d+.*)$/);
    return {
        tipo: parts[0] || '',
        nome: parts[1] || '',
        numero: parts[2] || ''
    };
}

// Função para limpar os campos de endereço
function limparEndereco() {
    document.getElementById("editVia").value = "";
    document.getElementById("editNVia").value = "";
    document.getElementById("editNumero").value = "";
    document.getElementById("editEndereco").value = "";
}

// Monitorar mudanças nos campos que devem limpar o endereço
function setupEnderecoListeners() {
    const campos = ['editRegiao', 'editEstado', 'editCidade'];
    
    campos.forEach(id => {
        document.getElementById(id).addEventListener('change', () => {
            limparEndereco();
        });
    });
}

// Script para abrir o modal de edição de ocupação
document.querySelectorAll(".btn-editar").forEach((btn) => {
    btn.addEventListener("click", function () {
        const id = this.getAttribute("editar");
    
        fetch("hoteis.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ action: "buscar_hotel", id: id }),
        })
        .then((res) => res.json())
        .then((data) => {
            if (data.error) {
                showNotificationModal(data.error, 'error');
                return;
            }

            // 1. Preencher campos básicos
            document.getElementById("editId").value = id;
            document.getElementById("editNome").value = data.nome;
            
            // 2. Mostrar todos os campos (remover display:none)
            document.getElementById("editEstado").closest('.col.mb-3').style.display = 'block';
            document.getElementById("editCidade").closest('.col.mb-3').style.display = 'block';
            document.getElementById("editVia").closest('.col.mb-3').style.display = 'block';
            
            // 3. Preencher região e estados
            const regiaoSelect = document.getElementById("editRegiao");
            regiaoSelect.value = data.regiao;
            
            // 4. Carregar todos os estados para a região
            const estadoSelect = document.getElementById("editEstado");
            estadoSelect.innerHTML = '<option value="" disabled> Escolha o Estado... </option>';
            
            if (dados[data.regiao]) {
                Object.keys(dados[data.regiao]).forEach(estado => {
                    const option = document.createElement('option');
                    option.value = estado;
                    option.textContent = estado;
                    estadoSelect.appendChild(option);
                });
                
                // 5. Preencher estado (convertendo sigla para nome)
                if (data.estado) {
                    const estadoNome = siglaParaNome(data.estado);
                    estadoSelect.value = estadoNome;
                    
                    // 6. Carregar todas as cidades para o estado
                    const cidadeSelect = document.getElementById("editCidade");
                    cidadeSelect.innerHTML = '<option value="" disabled> Escolha a cidade... </option>';
                    
                    if (dados[data.regiao][estadoNome]) {
                        dados[data.regiao][estadoNome].forEach(cidade => {
                            const option = document.createElement('option');
                            option.value = cidade;
                            option.textContent = cidade;
                            cidadeSelect.appendChild(option);
                        });
                        
                        // 7. Preencher cidade
                        if (data.cidade) {
                            cidadeSelect.value = data.cidade;
                        }
                    }
                }
            }
            
            // 8. Preencher endereço completo (se existir)
            if (data.endereço) {
                const enderecoParts = data.endereço.split(/ (.+?), (\d+)/);
                if (enderecoParts.length >= 3) {
                    document.getElementById("editVia").value = enderecoParts[0];
                    document.getElementById("editNVia").value = enderecoParts[1];
                    document.getElementById("editNumero").value = enderecoParts[2];
                    formatarEndereco();
                }
            }
            
            // 9. Configurar listeners para limpar campos subsequentes quando houver alteração
            const limparCamposSubsequentes = (elemento) => {
                if (elemento.id === 'editRegiao' && elemento.value !== elemento.dataset.original) {
                    document.getElementById("editEstado").value = '';
                    document.getElementById("editCidade").value = '';
                    limparEndereco();
                } else if (elemento.id === 'editEstado' && elemento.value !== elemento.dataset.original) {
                    document.getElementById("editCidade").value = '';
                    limparEndereco();
                } else if (elemento.id === 'editCidade' && elemento.value !== elemento.dataset.original) {
                    limparEndereco();
                }
            };
            
            // Configurar listeners e armazenar valores originais
            ['editRegiao', 'editEstado', 'editCidade'].forEach(id => {
                const elemento = document.getElementById(id);
                elemento.dataset.original = elemento.value; // Armazena valor original
                elemento.addEventListener('change', function() {
                    limparCamposSubsequentes(this);
                });
            });
        });
    });
});

// Modifique o evento de submit do formulário de edição
document.getElementById("formEditarHotel").addEventListener("submit", async function(e) {
    e.preventDefault();
    
    // Formatar endereço
    formatarEndereco();
    
    // Criar FormData
    const formData = new FormData(this);
    
    // Converter nome do estado para sigla
    const estadoNome = document.getElementById("editEstado").value;
    const estadoSigla = nomeParaSigla(estadoNome);
    formData.append('estadoSigla', estadoSigla);
    
    try {
        const response = await fetch('hoteis.php', {
            method: "POST",
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotificationModal("Hotel atualizado com sucesso!", 'success');
        } else {
            showNotificationModal(result.error || "Erro ao atualizar hotel", 'error');
        }
    } catch (error) {
        showNotificationModal("Erro na comunicação com o servidor", 'error');
    }
});

// Script para atualizar os dados do hóspede no modal de edição
document.getElementById("formEditarHotel").addEventListener("submit", function (e) {
    e.preventDefault();
  
    const form = e.target;
    const formData = new FormData(form);
  
    fetch("hoteis.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((response) => {
        const msgErro = document.getElementById("mensagemErro");
        if (response.success) {
          showNotificationModal("Hotel atualizado com sucesso!", 'success');
        } else {
          showNotificationModal(response.error || "Erro ao atualizar.", 'error');
        }
      });
  });

// 1) Captura todos os botões de remover
document.querySelectorAll(".btn-remover").forEach(btn => {
btn.addEventListener("click", () => {
    const id = btn.getAttribute("remover");

    // 2) Pergunta ao servidor se dá para remover
    fetch("hoteis.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ action: "verificar_remocao", id })
    })
    .then(res => res.json())
    .then(info => {
    const msgEl    = document.getElementById("removerMessage");
    const footerEl = document.getElementById("removerFooter");
    footerEl.innerHTML = ""; // limpa botões anteriores

    if (info.error) {
        // erro inesperado
        msgEl.textContent = info.error;
        footerEl.innerHTML = '<button class="btn btn-secondary" data-bs-dismiss="modal"> Fechar </button>';
    }
    else if (!info.canDelete) {
        // não pode remover — tem FKs
        msgEl.innerHTML = `
        <p>Não é possível remover este hotel.</p>
        <p>Há <strong>${info.count}</strong> registro(s) na tabela <strong>${info.table}</strong> que depende(m) deste hotel.</p>
        `;
        footerEl.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Entendido </button>';
    } else {
        // pode remover — confirmação
        msgEl.textContent = "Tem certeza que deseja remover este hotel?";
        footerEl.innerHTML = `
        <button id="confirmRemoveBtn" class="btn btn-danger"> Sim, remover </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Cancelar </button>
        `;

        // ao clicar em confirmar, chama a action de remoção
        document.getElementById("confirmRemoveBtn")
        .addEventListener("click", () => {
            fetch("hoteis.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ action: "remover_hotel", id })
            })
            .then(res => res.json())
            .then(resp => {
            showNotificationModal(resp.message, resp.status === 'success' ? 'success' : 'error');
            // fecha o modal de remoção:
            bootstrap.Modal.getInstance(document.getElementById("modalRemover")).hide();
            });
        });
    }

    // 3) Abre o modal de remoção
    new bootstrap.Modal(document.getElementById("modalRemover")).show();
    });
});
});


// Dados de exemplo para as regiões, estados e cidades
const dados = {
    "Norte": {
        "Acre": ["Cruzeiro do Sul", "Feijó", "Rio Branco", "Sena Madureira", "Tarauacá"],
        "Amapá": ["Laranjal do Jari", "Macapá", "Mazagão", "Oiapoque", "Santana"],
        "Amazonas": ["Coari", "Itacoatiara", "Manaus", "Manacapuru", "Parintins"],
        "Pará": ["Altamira", "Ananindeua", "Belém", "Marabá", "Santarém"],
        "Rondônia": ["Ariquemes", "Cacoal", "Ji-Paraná", "Porto Velho", "Vilhena"],
        "Roraima": ["Alto Alegre", "Boa Vista", "Caracaraí", "Pacaraima", "Rorainópolis"],
        "Tocantins": ["Araguaína", "Gurupi", "Palmas", "Paraíso do Tocantins", "Porto Nacional"]},

    "Nordeste": {
        "Alagoas": ["Arapiraca", "Maceió", "Palmeira dos Índios", "Penedo", "União dos Palmares"],
        "Bahia": ["Feira de Santana", "Ilhéus", "Porto Seguro", "Salvador", "Vitória da Conquista"],
        "Ceará": ["Crato", "Fortaleza", "Juazeiro do Norte", "Maracanaú", "Sobral"],
        "Maranhão": ["Bacabal", "Caxias", "Imperatriz", "São Luís", "Timon"],
        "Paraíba": ["Cajazeiras", "Campina Grande", "João Pessoa", "Patos", "Sousa"],
        "Pernambuco": ["Caruaru", "Jaboatão dos Guararapes", "Olinda", "Petrolina", "Recife"],
        "Piauí": ["Floriano", "Parnaíba", "Picos", "Piripiri", "Teresina"],
        "Rio Grande do Norte": ["Caicó", "Currais Novos", "Mossoró", "Natal", "Parnamirim"],
        "Sergipe": ["Aracaju", "Estância", "Itabaiana", "Lagarto", "Nossa Senhora do Socorro"]},

    "Centro-Oeste": {
        "Distrito Federal": ["Brasília", "Ceilândia", "Gama", "Samambaia", "Taguatinga"],
        "Goiás": ["Anápolis", "Aparecida de Goiânia", "Caldas Novas", "Goiânia", "Rio Verde"],
        "Mato Grosso": ["Cuiabá", "Rondonópolis", "Sinop", "Tangará da Serra", "Várzea Grande"],
        "Mato Grosso do Sul": ["Campo Grande", "Corumbá", "Dourados", "Ponta Porã", "Três Lagoas"]},

    "Sudeste": {
        "Espírito Santo": ["Cariacica", "Guarapari", "Serra", "Vila Velha", "Vitória"],
        "Minas Gerais": ["Belo Horizonte", "Contagem", "Juiz de Fora", "Ouro Preto", "Uberlândia"],
        "Rio de Janeiro": ["Angra dos Reis", "Duque de Caxias", "Niterói", "Petrópolis", "Rio de Janeiro"],
        "São Paulo": ["Campinas", "Ribeirão Preto", "Santos", "São José dos Campos", "São Paulo"]},

    "Sul": {
        "Paraná": ["Curitiba", "Foz do Iguaçu", "Londrina", "Maringá", "Ponta Grossa"],
        "Rio Grande do Sul": ["Caxias do Sul", "Gramado", "Pelotas", "Porto Alegre", "Santa Maria"],
        "Santa Catarina": ["Balneário Camboriú", "Blumenau", "Chapecó", "Florianópolis", "Joinville"]},

    "": {
        "Acre": ["Cruzeiro do Sul", "Feijó", "Rio Branco", "Sena Madureira", "Tarauacá"],
        "Alagoas": ["Arapiraca", "Maceió", "Palmeira dos Índios", "Penedo", "União dos Palmares"],
        "Amapá": ["Laranjal do Jari", "Macapá", "Mazagão", "Oiapoque", "Santana"],
        "Amazonas": ["Coari", "Itacoatiara", "Manaus", "Manacapuru", "Parintins"],
        "Bahia": ["Feira de Santana", "Ilhéus", "Porto Seguro", "Salvador", "Vitória da Conquista"],
        "Ceará": ["Crato", "Fortaleza", "Juazeiro do Norte", "Maracanaú", "Sobral"],
        "Distrito Federal": ["Brasília", "Ceilândia", "Gama", "Samambaia", "Taguatinga"],
        "Espírito Santo": ["Cariacica", "Guarapari", "Serra", "Vila Velha", "Vitória"],
        "Goiás": ["Anápolis", "Aparecida de Goiânia", "Caldas Novas", "Goiânia", "Rio Verde"],
        "Maranhão": ["Bacabal", "Caxias", "Imperatriz", "São Luís", "Timon"],
        "Mato Grosso": ["Cuiabá", "Rondonópolis", "Sinop", "Tangará da Serra", "Várzea Grande"],
        "Mato Grosso do Sul": ["Campo Grande", "Corumbá", "Dourados", "Ponta Porã", "Três Lagoas"],
        "Minas Gerais": ["Belo Horizonte", "Contagem", "Juiz de Fora", "Ouro Preto", "Uberlândia"],
        "Pará": ["Altamira", "Ananindeua", "Belém", "Marabá", "Santarém"],
        "Paraíba": ["Cajazeiras", "Campina Grande", "João Pessoa", "Patos", "Sousa"],
        "Paraná": ["Curitiba", "Foz do Iguaçu", "Londrina", "Maringá", "Ponta Grossa"],
        "Pernambuco": ["Caruaru", "Jaboatão dos Guararapes", "Olinda", "Petrolina", "Recife"],
        "Piauí": ["Floriano", "Parnaíba", "Picos", "Piripiri", "Teresina"],
        "Rio Grande do Norte": ["Caicó", "Currais Novos", "Mossoró", "Natal", "Parnamirim"],
        "Rio Grande do Sul": ["Caxias do Sul", "Gramado", "Pelotas", "Porto Alegre", "Santa Maria"],
        "Rio de Janeiro": ["Angra dos Reis", "Duque de Caxias", "Niterói", "Petrópolis", "Rio de Janeiro"],
        "Rondônia": ["Ariquemes", "Cacoal", "Ji-Paraná", "Porto Velho", "Vilhena"],
        "Roraima": ["Alto Alegre", "Boa Vista", "Caracaraí", "Pacaraima", "Rorainópolis"],
        "Santa Catarina": ["Balneário Camboriú", "Blumenau", "Chapecó", "Florianópolis", "Joinville"],
        "São Paulo": ["Campinas", "Ribeirão Preto", "Santos", "São José dos Campos", "São Paulo"],
        "Sergipe": ["Aracaju", "Estância", "Itabaiana", "Lagarto", "Nossa Senhora do Socorro"],
        "Tocantins": ["Araguaína", "Gurupi", "Palmas", "Paraíso do Tocantins", "Porto Nacional"]
    }
};

// Script para mostrar os estados e cidades de acordo com a região selecionada
const regiaoSelect = document.querySelector('select[name="regiao"]');
const estadoSelect = document.querySelector('select[name="estado"]');
const cidadeSelect = document.querySelector('select[name="cidade"]');
const enderecoInput = document.querySelector('input[name="endereco"]');
const regiaoFilter = document.getElementById('filterRegiao');
const estadoFilter = document.getElementById('filterEstado');

// Pegamos os <div> que envolvem os selects
const estadoDiv = estadoSelect.closest('.col.mb-3');
const cidadeDiv = cidadeSelect.closest('.col.mb-3');
const enderecoDiv = enderecoInput.closest('.col.mb-3');
const filterRegiaoDiv = regiaoSelect.closest('.filter-group');
const filterEstadoDiv = estadoFilter.closest('.filter-group');


// Configuração inicial dos selects
function initializeSelects() {
    // Cria opção padrão desabilitada para cada select
    estadoSelect.innerHTML = '<option value="" selected disabled>Escolha o Estado...</option>';
    cidadeSelect.innerHTML = '<option value="" selected disabled>Escolha a cidade...</option>';
    estadoFilter.innerHTML = '<option value="">Todos</option>';
    
    // Remove o required dos campos que estão ocultos
    estadoSelect.required = false;
    cidadeSelect.required = false;
    enderecoInput.required = false;
    
    // Esconde os campos dependentes
    estadoDiv.style.display = 'none';
    cidadeDiv.style.display = 'none';
    enderecoDiv.style.display = 'none';
}

initializeSelects();

// Quando selecionar a região
regiaoSelect.addEventListener('change', () => {
    const regiao = regiaoSelect.value;
    
    // Reinicia os selects dependentes
    cidadeSelect.innerHTML = '<option value="" selected disabled> Escolha a cidade... </option>';
    cidadeDiv.style.display = 'none';
    enderecoDiv.style.display = 'none';
    
    if (dados[regiao]) {
        estadoSelect.innerHTML = '<option value="" selected disabled> Escolha o Estado... </option>';
        
        Object.keys(dados[regiao]).sort().forEach(estado => {
            const opt = document.createElement('option');
            opt.value = estado;
            opt.textContent = estado;
            estadoSelect.appendChild(opt);
        });
        
        estadoDiv.style.display = 'block';
        estadoSelect.required = true;
    } else {
        estadoDiv.style.display = 'none';
        estadoSelect.required = false;
    }
});

// Quando selecionar o estado
estadoSelect.addEventListener('change', () => {
    const regiao = regiaoSelect.value;
    const estado = estadoSelect.value;
    
    cidadeSelect.innerHTML = '<option value="" selected disabled> Escolha a cidade... </option>';
    enderecoDiv.style.display = 'none';
    enderecoInput.required = false;

    if (dados[regiao] && dados[regiao][estado]) {
        dados[regiao][estado].sort().forEach(cidade => {
            const opt = document.createElement('option');
            opt.value = cidade;
            opt.textContent = cidade;
            cidadeSelect.appendChild(opt);
        });
        
        cidadeDiv.style.display = 'block';
        cidadeSelect.required = true;
    } else {
        cidadeDiv.style.display = 'none';
        cidadeSelect.required = false;
    }
});

// Quando selecionar a cidade
cidadeSelect.addEventListener('change', () => {
    if (cidadeSelect.value && cidadeSelect.value !== "") {
        enderecoDiv.style.display = 'block';
        enderecoInput.required = true;
    } else {
        enderecoDiv.style.display = 'none';
        enderecoInput.required = false;
    }
});

// Quando seleciona a região no filtro
regiaoFilter.addEventListener('change', () => {
    const regiao = regiaoFilter.value;

    if (dados[regiao]) {
        estadoFilter.innerHTML = '<option value="">Todos</option>';

        Object.keys(dados[regiao]).sort().forEach(estado => {
            // Usa o mapeamento para obter a sigla
            const sigla = estadosMap.nomesToSiglas[estado] || estado;
            const opt = document.createElement('option');
            opt.value = sigla;
            opt.textContent = estado;
            estadoFilter.appendChild(opt);
        });

        filterEstadoDiv.style.display = 'block';
    }
});

// Script para validar o formulário antes de enviar, garantindo que apareça a mensagem de erro do navegador caso o campo não esteja preenchido
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("formEditarHotel");

    form.addEventListener("submit", function(event) {
        // Verificação adicional para garantir que os campos visíveis sejam preenchidos
        const visibleInputs = Array.from(form.querySelectorAll("input, select")).filter(input => {
            return input.closest('.col.mb-3').style.display !== 'none';
        });

        let allFilled = true;

        visibleInputs.forEach(function(input) {
            if (input.required && !input.value) {
                // Dispara a validação nativa do navegador
                input.reportValidity();
                allFilled = false;
            }
        });

        if (!allFilled) {
            event.preventDefault();
        }
    });
});

// Script para formatar o endereço completo com base nos campos preenchidos
const tipoEndereco = document.getElementById('editVia');
const nomeEndereco = document.getElementById('editNVia');
const numeroEndereco = document.getElementById('editNumero');
const enderecoCompleto = document.getElementById('editEndereco');

// Função para formatar o endereço
function formatarEndereco() {
    const tipo = document.getElementById("editVia").value;
    const nome = document.getElementById("editNVia").value;
    const numero = document.getElementById("editNumero").value;
    
    if (tipo && nome && numero) {
        document.getElementById("editEndereco").value = `${tipo} ${nome}, ${numero}`;
    } else {
        document.getElementById("editEndereco").value = '';
    }
}

// Adiciona os event listeners
tipoEndereco.addEventListener('change', formatarEndereco);
nomeEndereco.addEventListener('input', formatarEndereco);
numeroEndereco.addEventListener('input', formatarEndereco);

// Mapeamento de estados para siglas
const estadosMap = {
    siglasToNomes: {
        'AC': 'Acre', 'AL': 'Alagoas', 'AP': 'Amapá', 'AM': 'Amazonas',
        'BA': 'Bahia', 'CE': 'Ceará', 'DF': 'Distrito Federal',
        'ES': 'Espírito Santo', 'GO': 'Goiás', 'MA': 'Maranhão',
        'MT': 'Mato Grosso', 'MS': 'Mato Grosso do Sul', 'MG': 'Minas Gerais',
        'PA': 'Pará', 'PB': 'Paraíba', 'PR': 'Paraná', 'PE': 'Pernambuco',
        'PI': 'Piauí', 'RJ': 'Rio de Janeiro', 'RN': 'Rio Grande do Norte',
        'RS': 'Rio Grande do Sul', 'RO': 'Rondônia', 'RR': 'Roraima',
        'SC': 'Santa Catarina', 'SP': 'São Paulo', 'SE': 'Sergipe',
        'TO': 'Tocantins'
    },
    nomesToSiglas: {
        'Acre': 'AC', 'Alagoas': 'AL', 'Amapá': 'AP', 'Amazonas': 'AM',
        'Bahia': 'BA', 'Ceará': 'CE', 'Distrito Federal': 'DF',
        'Espírito Santo': 'ES', 'Goiás': 'GO', 'Maranhão': 'MA',
        'Mato Grosso': 'MT', 'Mato Grosso do Sul': 'MS', 'Minas Gerais': 'MG',
        'Pará': 'PA', 'Paraíba': 'PB', 'Paraná': 'PR', 'Pernambuco': 'PE',
        'Piauí': 'PI', 'Rio de Janeiro': 'RJ', 'Rio Grande do Norte': 'RN',
        'Rio Grande do Sul': 'RS', 'Rondônia': 'RO', 'Roraima': 'RR',
        'Santa Catarina': 'SC', 'São Paulo': 'SP', 'Sergipe': 'SE',
        'Tocantins': 'TO'
    }
};

function siglaParaNome(sigla) {
    const estadosMap = {
        'AC': 'Acre', 'AL': 'Alagoas', 'AP': 'Amapá', 'AM': 'Amazonas',
        'BA': 'Bahia', 'CE': 'Ceará', 'DF': 'Distrito Federal',
        'ES': 'Espírito Santo', 'GO': 'Goiás', 'MA': 'Maranhão',
        'MT': 'Mato Grosso', 'MS': 'Mato Grosso do Sul', 'MG': 'Minas Gerais',
        'PA': 'Pará', 'PB': 'Paraíba', 'PR': 'Paraná', 'PE': 'Pernambuco',
        'PI': 'Piauí', 'RJ': 'Rio de Janeiro', 'RN': 'Rio Grande do Norte',
        'RS': 'Rio Grande do Sul', 'RO': 'Rondônia', 'RR': 'Roraima',
        'SC': 'Santa Catarina', 'SP': 'São Paulo', 'SE': 'Sergipe',
        'TO': 'Tocantins'
    };
    return estadosMap[sigla] || sigla;
}

// Função para converter sigla para nome completo
function siglaParaNome(sigla) {
    return estadosMap.siglasToNomes[sigla] || sigla;
}

// Função para converter nome completo para sigla
function nomeParaSigla(nome) {
    return estadosMap.nomesToSiglas[nome] || nome;
}

// Modifique o estado selecionado para a sigla correspondente antes de inserir no banco de dados
document.getElementById("formEditarHotel").addEventListener("submit", async function(e) {
    e.preventDefault();
    
    // Formatar endereço
    formatarEndereco();
    
    // Criar FormData
    const formData = new FormData(this);
    
    // Adicionar estadoSigla
    const estadoNome = document.getElementById("editEstado").value;
    const estadoSigla = estadosSiglas[estadoNome] || estadoNome;
    formData.append('estadoSigla', estadoSigla);
    
    try {
        const response = await fetch('hoteis.php', {
            method: "POST",
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotificationModal("Hotel atualizado com sucesso!", 'success');
        } else {
            showNotificationModal(result.error || "Erro ao atualizar hotel", 'error');
        }
    } catch (error) {
        showNotificationModal("Erro na comunicação com o servidor", 'error');
    }
});

// Script para ordenar a tabela em ordem crescente/decrescente
document.addEventListener('DOMContentLoaded', () => {
    const getCellValue = (tr, idx) => tr.children[idx].innerText.trim();
    const isNumeric = (val) => !isNaN(val) && val !== '';

    const comparer = (idx, asc) => (a, b) => {
        const valA = getCellValue(asc ? a : b, idx);
        const valB = getCellValue(asc ? b : a, idx);

        return isNumeric(valA) && isNumeric(valB)
        ? parseFloat(valA) - parseFloat(valB)
        : valA.localeCompare(valB, 'pt', { numeric: true, sensitivity: 'base' });
    };

    document.querySelectorAll('th.sortable').forEach(th => {
        let asc = true;
        th.style.cursor = 'pointer';
        th.addEventListener('click', () => {
            const table = th.closest('table');
            const tbody = table.querySelector('tbody');
            const index = Array.from(th.parentNode.children).indexOf(th);
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort(comparer(index, asc));
            asc = !asc;

            rows.forEach(row => tbody.appendChild(row));
        });
    });
});

// Script para ocultar o URL no status bar ao passar o mouse sobre links
document.addEventListener('DOMContentLoaded', function() {
    // Seleciona todos os elementos com links que não são vazios
    const allLinks = document.querySelectorAll('a[href]:not([href^="javascript:"])');
    
    allLinks.forEach(link => {
        // Armazena o href original como dataset
        if (!link.dataset.originalHref) {
            link.dataset.originalHref = link.href;
        }

        // Remove o href quando o mouse entra (previne status bar)
        link.addEventListener('mouseenter', function() {
            this.removeAttribute('href');
        });

        // Restaura o href quando o mouse sai
        link.addEventListener('mouseleave', function() {
            if (this.dataset.originalHref) {
                this.href = this.dataset.originalHref;
            }
        });

        // Garante que o clique funcione corretamente
        link.addEventListener('click', function(e) {
            if (!this.hasAttribute('href') && this.dataset.originalHref) {
                e.preventDefault();
                
                // Trata diferentes tipos de links
                if (this.dataset.originalHref.startsWith('#')) {
                    // Scroll suave para âncoras
                    document.querySelector(this.dataset.originalHref)?.scrollIntoView({ 
                        behavior: 'smooth' 
                    });
                } else if (this.target === '_blank') {
                    // Abre em nova aba se necessário
                    window.open(this.dataset.originalHref, '_blank');
                } else {
                    // Navegação normal
                    window.location.href = this.dataset.originalHref;
                }
            }
            // Se já tem href, o comportamento padrão é mantido
        });

        // Garante que o href seja restaurado se o elemento perder foco
        link.addEventListener('blur', function() {
            if (!this.hasAttribute('href') && this.dataset.originalHref) {
                this.href = this.dataset.originalHref;
            }
        });
    });
});

// Botão Voltar
document.getElementById('btnVoltar').addEventListener('click', () => {
    window.history.back();
});

// Sistema de Filtragem
function aplicarFiltros() {
    const filterId = document.getElementById('filterId').value.trim().toLowerCase();
    const filterNome = document.getElementById('filterNome').value.trim().toLowerCase();
    const filterRegiao = document.getElementById('filterRegiao').value.trim().toLowerCase();
    const filterEstado = document.getElementById('filterEstado').value.trim().toLowerCase();
    const filterCidade = document.getElementById('filterCidade').value.trim().toLowerCase();
    const filterEndereco = document.getElementById('filterEndereco').value.trim().toLowerCase();

    const rows = document.querySelectorAll('#tabela-hoteis tbody tr:not(#noResultsRow)');
    const noResultsRow = document.getElementById('noResultsRow');

    let visibleCount = 0;

    rows.forEach(row => {
    const id = row.cells[0].textContent.trim().toLowerCase();
    const nome = row.cells[1].textContent.trim().toLowerCase();
    const regiao = row.cells[2].textContent.trim().toLowerCase();
    const estado = row.cells[3].textContent.trim().toLowerCase();
    const cidade = row.cells[4].textContent.trim().toLowerCase();
    const endereco = row.cells[5].textContent.trim().toLowerCase();

    let show = true;

    if (filterId && id !== filterId) show = false;
    if (filterNome && !nome.includes(filterNome)) show = false;
    if (filterRegiao && regiao !== filterRegiao) show = false;
    if (filterEstado && estado !== filterEstado) show = false;
    if (filterCidade && cidade !== filterCidade) show = false;
    if (filterEndereco && !endereco.includes(filterEndereco)) show = false;

    row.style.display = show ? '' : 'none';

    if (show) visibleCount++;
    });

    noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
}

document.getElementById('btnApplyFilters').addEventListener('click', aplicarFiltros);

document.getElementById('btnClearFilters').addEventListener('click', () => {
    document.getElementById('filterId').value = '';
    document.getElementById('filterNome').value = '';
    document.getElementById('filterRegiao').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterCidade').value = '';
    document.getElementById('filterEndereco').value = '';
    
    const rows = document.querySelectorAll('#tabela-hoteis tbody tr');
    rows.forEach(row => row.style.display = '');

    const noResultsRow = document.getElementById('noResultsRow');
    if (noResultsRow) noResultsRow.style.display = 'none';
});

// Script para ordenar a tabela
const getCellValue = (tr, idx) => tr.children[idx].innerText.trim();
const isNumeric = (val) => !isNaN(val) && val !== '';

const comparer = (idx, asc) => (a, b) => {
    const valA = getCellValue(asc ? a : b, idx);
    const valB = getCellValue(asc ? b : a, idx);

    return isNumeric(valA) && isNumeric(valB)
    ? parseFloat(valA) - parseFloat(valB)
    : valA.localeCompare(valB, 'pt', { numeric: true, sensitivity: 'base' });
};

document.querySelectorAll('th.sortable').forEach(th => {
    let asc = true;
    th.style.cursor = 'pointer';
    th.addEventListener('click', () => {
        const table = th.closest('table');
        const tbody = table.querySelector('tbody');
        const index = Array.from(th.parentNode.children).indexOf(th);
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort(comparer(index, asc));
        asc = !asc;

        // Remove classes de ordenação antigas
        document.querySelectorAll('th.sortable').forEach(h => {
            h.classList.remove('asc', 'desc');
        });

        // Aplica nova classe ao cabeçalho clicado
        th.classList.toggle('asc', asc);
        th.classList.toggle('desc', !asc);

        // Reinsere linhas na nova ordem
        rows.forEach(row => tbody.appendChild(row));
    });
});

// IDs que devem aceitar apenas números
const ids = ['editNumero', 'filterId'];

// Aplica a restrição de apenas números no keypress
ids.forEach(id => {
    const input = document.getElementById(id);
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key < '0' || e.key > '9') {
                e.preventDefault();
            }
        });

        // Evita colar texto não numérico
        input.addEventListener('paste', function(e) {
            const pasteData = e.clipboardData.getData('text');
            if (!/^\d+$/.test(pasteData)) {
                e.preventDefault();
            }
        });
    }
});

// Script para garantir o fechamento dos modais
document.addEventListener('DOMContentLoaded', function() {
      // Configurações de animação
      const FADE_DURATION = 400;
      
      // Função para fechar completamente um modal com animação suave
      const closeModalSmoothly = (modalId) => {
          const modal = document.getElementById(modalId);
          if (!modal) return;
          
          // Encontra o backdrop correspondente
          const backdrop = document.querySelector('.modal-backdrop');
          
          // Inicia animação de fade out
          modal.classList.add('fade-out');
          if (backdrop) backdrop.classList.add('fade-out');
          
          // Aguarda a animação completar antes de remover os elementos
          setTimeout(() => {
              // Restaura o estado do body
              document.body.classList.remove('modal-open');
              document.body.style.overflow = '';
              document.body.style.paddingRight = '';
              
              // Remove o backdrop
              if (backdrop) backdrop.remove();
              
              // Remove as classes de animação
              modal.classList.remove('fade-out', 'show');
              modal.style.display = 'none';
              modal.removeAttribute('aria-modal');
              modal.removeAttribute('role');
              modal.setAttribute('aria-hidden', 'true');
              
              // Remove o backdrop do DOM após a animação
              const existingBackdrop = document.querySelector('.modal-backdrop');
              if (existingBackdrop) existingBackdrop.remove();
              
          }, FADE_DURATION);
      };

      // Adiciona evento para todos os botões de fechar modal
      document.querySelectorAll('button[data-bs-dismiss="modal"]').forEach(button => {
          button.addEventListener('click', function(e) {
              e.preventDefault();
              
              // Encontra o modal mais próximo
              const modal = this.closest('.modal');
              if (modal) {
                  closeModalSmoothly(modal.id);
              }
          });
      });

      // Adiciona evento para fechar ao pressionar ESC
      document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
              document.querySelectorAll('.modal.show').forEach(modal => {
                  closeModalSmoothly(modal.id);
              });
          }
      });
  });