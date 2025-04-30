// Função para mostrar o modal
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

// Evento de submit do formulário via AJAX
document.getElementById('quartosForm').addEventListener('submit', async function(e) {
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

// Formatação do valor monetário
document.getElementById('valor').addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, '');
    value = (value / 100).toFixed(2) + '';
    value = value.replace(".", ",");
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    this.value = 'R$ ' + value;
});

// Campo Número - Aceitar apenas números (como no campo Valor)
document.getElementById('numero').addEventListener('keypress', function(e) {
    // Permite apenas números (0-9)
    if (e.key < '0' || e.key > '9') {
        e.preventDefault(); // Bloqueia a tecla se não for número
    }
});

// Evita colar texto não numérico
document.getElementById('numero').addEventListener('paste', function(e) {
    const pasteData = e.clipboardData.getData('text');
    if (!/^\d+$/.test(pasteData)) {
        e.preventDefault(); // Bloqueia o colar se não for número
    }
});