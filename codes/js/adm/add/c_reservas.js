// Função para mostrar o modal de notificação
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
    
    // Fechamento automático
    const timeout = type === 'success' ? 5000 : 10000;
    setTimeout(() => modal.hide(), timeout);
    
    // Ações pós-fechamento
    modal._element.addEventListener('hidden.bs.modal', () => {
        if(type === 'success') {
            window.location.reload();
        }
    }, {once: true});
}

// Função para formatar data como YYYY-MM-DD
function formatDate(date) {
    if (!(date instanceof Date) || isNaN(date)) return '';
    
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
}

// Função para formatar valor monetário
function formatCurrency(value) {
    return 'R$ ' + value.toFixed(2).replace('.', ',');
}

// Função para validar se uma data é válida
function isValidDate(date) {
    return date instanceof Date && !isNaN(date);
}

// Função principal para calcular o total da reserva
function calcularTotal() {
    const valorDiaria = parseFloat(document.getElementById('valorDiaria').value) || 0;
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    const checkinDate = new Date(checkinInput.value);
    const checkoutDate = new Date(checkoutInput.value);

    // Cálculo do total
    if (isValidDate(checkinDate) && isValidDate(checkoutDate)) {
        const diffTime = checkoutDate - checkinDate;
        const diffDays = Math.max(1, Math.ceil(diffTime / (1000 * 3600 * 24))); // Mínimo 1 dia
        const total = valorDiaria * diffDays;
        
        // Atualiza tanto o campo de exibição quanto o campo hidden que será enviado
        document.getElementById('precoDisplay').value = formatCurrency(total);
        document.getElementById('preco').value = total.toFixed(2); // Envia o valor numérico
    } else {
        document.getElementById('precoDisplay').value = '';
        document.getElementById('preco').value = '';
    }
}
// Configuração inicial quando o DOM é carregado
document.addEventListener('DOMContentLoaded', function() {
    // Definir datas mínimas e valores padrão
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');

    checkinInput.min = formatDate(today);
    checkoutInput.min = formatDate(today);



    // Calcular total inicial
    calcularTotal();

    // Configurar eventos
    setupEventListeners();
});

document.getElementById('reservaForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Desabilitar o botão e mostrar spinner
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...';
    
    try {
        const response = await fetch('', {
            method: 'POST',
            body: new FormData(this)
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            // Resetar formulário após sucesso
            this.reset();
            // Redirecionar para evitar F5 e reenvio
            window.location.href = this.action + '?success=1';
        } else {
            showNotificationModal(result.message, 'error');
        }
    } catch (error) {
        showNotificationModal('Erro na comunicação com o servidor', 'error');
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    }
});

// Configura todos os event listeners
function setupEventListeners() {
    // Evento de submit do formulário
    document.getElementById('reservaForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            showNotificationModal(result.message, result.status);
            
        } catch (error) {
            showNotificationModal('Erro na comunicação com o servidor', 'error');
        }
    });

    // Buscar email ao selecionar cliente
    document.getElementById('cliente').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('email').value = selectedOption?.dataset.email || '';
    });

    // Buscar quartos ao selecionar hotel
    document.getElementById('hotel').addEventListener('change', async function() {
        const hotelId = this.value;
        const quartoSelect = document.getElementById('quarto');

        if (!hotelId) {
            quartoSelect.innerHTML = '<option value="">Selecione o Quarto...</option>';
            return;
        }

        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax_request=true&get_quartos=true&hotel_id=${hotelId}`
            });
            
            const result = await response.json();
            
            if (result.error) {
                quartoSelect.innerHTML = `<option value="">${result.error}</option>`;
            } else {
                quartoSelect.innerHTML = '<option value="">Selecione o Quarto...</option>';
                result.forEach(quarto => {
                    quartoSelect.innerHTML += `<option value="${quarto.idquarto}" data-valor="${quarto.valor}">${quarto.numero}</option>`;
                });
            }
            
        } catch (error) {
            quartoSelect.innerHTML = '<option value="">Erro ao carregar quartos</option>';
            console.error('Erro:', error);
        }
    });

    // Atualizar o mínimo do check-out quando o check-in muda
    document.getElementById('checkin').addEventListener('change', function() {
        const checkinDate = new Date(this.value);
        if (isValidDate(checkinDate)) {
            const checkoutInput = document.getElementById('checkout');
            checkoutInput.min = this.value;
            
            // Se o check-out atual for anterior ao novo check-in, atualizar
            const checkoutDate = new Date(checkoutInput.value);
            if (checkoutDate < checkinDate) {
                checkoutInput.value = formatDate(new Date(checkinDate.getTime() + 86400000)); // +1 dia
            }
        }
        calcularTotal();
    });

    // Evento para o check-out
    document.getElementById('checkout').addEventListener('change', calcularTotal);

    // Quando o quarto é selecionado, atualizar o valor e recalcular
    document.getElementById('quarto').addEventListener('change', async function() {
        const quartoId = this.value;
        if (!quartoId) return;

        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax_request=true&get_quarto_details=true&quarto_id=${quartoId}`
            });
            
            const result = await response.json();
            document.getElementById('tquarto').value = result.tipoQuarto || '';
            document.getElementById('ocupacao').value = result.ocupacao || '';
            document.getElementById('valorDiaria').value = result.valor || 0;
            calcularTotal();
            
        } catch (error) {
            console.error('Erro:', error);
        }
    });

    // Configuração para ocultar URLs na barra de status
    document.querySelectorAll('a[href]:not([href^="javascript:"])').forEach(link => {
        if (!link.dataset.originalHref) {
            link.dataset.originalHref = link.href;
        }

        link.addEventListener('mouseenter', function() {
            this.removeAttribute('href');
        });

        link.addEventListener('mouseleave', function() {
            if (this.dataset.originalHref) {
                this.href = this.dataset.originalHref;
            }
        });

        link.addEventListener('click', function(e) {
            if (!this.hasAttribute('href') && this.dataset.originalHref) {
                e.preventDefault();
                
                if (this.dataset.originalHref.startsWith('#')) {
                    document.querySelector(this.dataset.originalHref)?.scrollIntoView({ 
                        behavior: 'smooth' 
                    });
                } else if (this.target === '_blank') {
                    window.open(this.dataset.originalHref, '_blank');
                } else {
                    window.location.href = this.dataset.originalHref;
                }
            }
        });

        link.addEventListener('blur', function() {
            if (!this.hasAttribute('href') && this.dataset.originalHref) {
                this.href = this.dataset.originalHref;
            }
        });
    });
}