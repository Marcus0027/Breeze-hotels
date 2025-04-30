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
document.getElementById('usuariosForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const nome = document.getElementById("nome").value.trim();
    const email = document.getElementById("email").value.trim();
    const senha = document.getElementById("senha").value;
    const senhaConf = document.getElementById("csenha").value;
    
    // Validação básica no cliente
    if (senha !== senhaConf) {
        showNotificationModal('As senhas não coincidem!', 'error');
        document.getElementById("senha").value = "";
        document.getElementById("csenha").value = "";
        return;
    }

    const senhaForte = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    if (!senhaForte.test(senha)) {
        showNotificationModal('A senha deve conter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma minúscula, um número e um caractere especial!', 'error');
        document.getElementById("senha").value = "";
        document.getElementById("csenha").value = "";
        return;
    }

    // Envia os dados via AJAX
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