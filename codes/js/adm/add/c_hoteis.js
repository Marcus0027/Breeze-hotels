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
        "Santa Catarina": ["Balneário Camboriú", "Blumenau", "Chapecó", "Florianópolis", "Joinville"]}
};

// Script para mostrar os estados e cidades de acordo com a região selecionada
const regiaoSelect = document.querySelector('select[name="regiao"]');
const estadoSelect = document.querySelector('select[name="estado"]');
const cidadeSelect = document.querySelector('select[name="cidade"]');
const enderecoInput = document.querySelector('input[name="endereco"]');

// Pegamos os <div> que envolvem os selects
const estadoDiv = estadoSelect.closest('.col-md-6.mb-3');
const cidadeDiv = cidadeSelect.closest('.col-md-6.mb-3');
const enderecoDiv = enderecoInput.closest('.mb-3');

// Configuração inicial dos selects
function initializeSelects() {
    // Cria opção padrão desabilitada para cada select
    estadoSelect.innerHTML = '<option value="" selected disabled>Escolha o Estado...</option>';
    cidadeSelect.innerHTML = '<option value="" selected disabled>Escolha a cidade...</option>';
    
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
    cidadeSelect.innerHTML = '<option value="" selected disabled>Escolha a cidade...</option>';
    cidadeDiv.style.display = 'none';
    enderecoDiv.style.display = 'none';
    
    if (dados[regiao]) {
        estadoSelect.innerHTML = '<option value="" selected disabled>Escolha o Estado...</option>';
        
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
    
    cidadeSelect.innerHTML = '<option value="" selected disabled>Escolha a cidade...</option>';
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

// Script para validar o formulário antes de enviar, garantindo que apareça a mensagem de erro do navegador caso o campo não esteja preenchido
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("hotelForm");

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
const tipoEndereco = document.getElementById('tipoEndereco');
const nomeEndereco = document.getElementById('nomeEndereco');
const numeroEndereco = document.getElementById('numeroEndereco');
const enderecoCompleto = document.getElementById('enderecoCompleto');

// Função para formatar o endereço
function formatarEndereco() {
    if (tipoEndereco.value && nomeEndereco.value && numeroEndereco.value) {
        enderecoCompleto.value = `${tipoEndereco.value} ${nomeEndereco.value}, ${numeroEndereco.value}`;
    } else {
        enderecoCompleto.value = '';
    }
}

// Adiciona os event listeners
tipoEndereco.addEventListener('change', formatarEndereco);
nomeEndereco.addEventListener('input', formatarEndereco);
numeroEndereco.addEventListener('input', formatarEndereco);

// Modifique o event listener do formulário para validar o endereço
document.getElementById("hotelForm").addEventListener("submit", function(event) {
    formatarEndereco(); // Garante que o endereço está formatado antes do envio
    
    if (!enderecoCompleto.value) {
        alert('Por favor, preencha todos os campos do endereço corretamente.');
        event.preventDefault();
    }
});

// Mapeamento de estados para siglas
const estadosSiglas = {
    'Acre': 'AC', 'Alagoas': 'AL', 'Amapá': 'AP', 'Amazonas': 'AM',
    'Bahia': 'BA', 'Ceará': 'CE', 'Distrito Federal': 'DF',
    'Espírito Santo': 'ES', 'Goiás': 'GO', 'Maranhão': 'MA',
    'Mato Grosso': 'MT', 'Mato Grosso do Sul': 'MS', 'Minas Gerais': 'MG',
    'Pará': 'PA', 'Paraíba': 'PB', 'Paraná': 'PR', 'Pernambuco': 'PE',
    'Piauí': 'PI', 'Rio de Janeiro': 'RJ', 'Rio Grande do Norte': 'RN',
    'Rio Grande do Sul': 'RS', 'Rondônia': 'RO', 'Roraima': 'RR',
    'Santa Catarina': 'SC', 'São Paulo': 'SP', 'Sergipe': 'SE',
    'Tocantins': 'TO'
};

// Modifique o estado selecionado para a sigla correspondente antes de inserir no banco de dados
document.getElementById("hotelForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    
    // Formatar endereço e estado antes de enviar
    formatarEndereco();
    const estadoSelect = document.querySelector('select[name="estado"]');
    const estadoNome = estadoSelect.value;
    const estadoSigla = estadosSiglas[estadoNome] || estadoNome;
    
    // Criar FormData com todos os campos
    const formData = new FormData(this);
    formData.append('estadoSigla', estadoSigla);
    
    try {
        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        showNotificationModal(result.message, result.status);
        
        if (result.status === 'success') {
            showNotificationModal(result.message, 'success');
        } else {
            showNotificationModal(result.message, 'error');
        }
        
    } catch (error) {
        showNotificationModal('Erro na comunicação com o servidor', 'error');
    }
});

// Função para mostrar o modal
function showNotificationModal(message, type) {
    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    const modalIcon = document.getElementById('modalIcon');
    const modalMessage = document.getElementById('modalMessage');
    
    // Configura aparência baseada no tipo
    if(type === 'success') {
        modalIcon.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
        modalMessage.className = 'my-3 text-success';
    } else {
        modalIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger"></i>';
        modalMessage.className = 'my-3 text-danger';
    }
    
    modalMessage.textContent = message;
    modal.show();
    
    // Configura tempo de fechamento automático
    const timeout = type === 'success' ? 5000 : 10000;
    
    // Fechamento para sucesso (com reload)
    if(type === 'success') {
        document.getElementById('notificationModal').addEventListener('hidden.bs.modal', () => {
            window.location.reload(); // Recarrega a página após fechar o modal
        }, {once: true}); // Garante que o listener só executa uma vez
    }
    
    setTimeout(() => modal.hide(), timeout);
}

// Campo Número - Aceitar apenas números
document.getElementById('numeroEndereco').addEventListener('keypress', function(e) {
    // Permite apenas números (0-9)
    if (e.key < '0' || e.key > '9') {
        e.preventDefault(); // Bloqueia a tecla se não for número
    }
});

// Evita colar texto não numérico
document.getElementById('numeroEndereco').addEventListener('paste', function(e) {
    const pasteData = e.clipboardData.getData('text');
    if (!/^\d+$/.test(pasteData)) {
        e.preventDefault(); // Bloqueia o colar se não for número
    }
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