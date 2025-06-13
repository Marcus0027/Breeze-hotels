// Função para mostrar o modal
function showNotificationModal(message, type, email = null) {
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
            // Verifica se o email é o de admin
            const targetPage = email === 'adm@adm.com' ? 'indexA.php' : 'index.php';
            window.location.href = targetPage;
        }
    }, {once: true});
}

// Evento de submit do formulário via AJAX
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formLogin');

    form.addEventListener('submit', async function (e) {
        e.preventDefault(); // Impede o envio padrão

        const formData = new FormData(form);
        const email = formData.get('emailInput') || formData.get('email'); // Pega o email do formulário
        
        try {
            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            showNotificationModal(result.message, result.status, email); // Passa o email para a função
        } catch (error) {
            showNotificationModal('Erro na comunicação com o servidor.', 'error');
        }

        const emailField = document.getElementById("email");
        const senhaField = document.getElementById("senha");
        const lembrarCheck = document.getElementById("lembrar");
        const erroDiv = document.getElementById("mensagemErro");
        const sucessoDiv = document.getElementById("mensagemSucesso");
    
        formData.append("ajax", "1");
        formData.append("emailInput", emailField.value);
        formData.append("senhaInput", senhaField.value);
        formData.append("lembrar", lembrarCheck.checked);
    
        const response = await fetch("", {
            method: "POST",
            body: formData
        });
    
        const result = await response.json();
    
        if (result.status === 'success') {
            erroDiv.classList.add("d-none");
            sucessoDiv.textContent = result.message;
            sucessoDiv.classList.remove("d-none");
        } else {
            sucessoDiv.classList.add("d-none");
            erroDiv.textContent = result.message;
            erroDiv.classList.remove("d-none");
            senhaField.value = "";
        }
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