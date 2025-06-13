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
document.getElementById('clienteForm').addEventListener('submit', async function(e) {
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

// Formatação do telefone
document.addEventListener("DOMContentLoaded", function () {
    const telefoneInput = document.getElementById("telefone");
  
    telefoneInput.addEventListener("input", function () {
      let raw = telefoneInput.value.replace(/\D/g, "");
    
      if (raw.length > 11) {
        raw = raw.slice(0, 11);
      }
  
      if (raw.length > 2 && raw[2] !== "9") {
        raw = raw.slice(0, 2) + "9" + raw.slice(2);
      }
    
      let formatado = "";
    
      if (raw.length === 0) {
        formatado = "";
      } else if (raw.length <= 2) {
        formatado = raw; 
      } else if (raw.length <= 7) {
        formatado = `(${raw.slice(0, 2)}) ${raw.slice(2)}`;
      } else {
        formatado = `(${raw.slice(0, 2)}) ${raw[2]}${raw.slice(3, 7)}-${raw.slice(7, 11)}`;
      }
    
      telefoneInput.value = formatado;
    });
  
    telefoneInput.addEventListener("keydown", function (e) {
      const permitidos = [
        "Backspace", "Delete", "ArrowLeft", "ArrowRight", "Tab"
      ];
  
      if (
        permitidos.includes(e.key) ||
        (e.key >= "0" && e.key <= "9")
      ) {
        const raw = telefoneInput.value.replace(/\D/g, "");
  
        if (raw.length >= 11 && !permitidos.includes(e.key)) {
          e.preventDefault();
        }
  
        return;
      }
  
      e.preventDefault();
    });
});

// Formatação do CPF
document.getElementById('cpf').addEventListener('input', function(e) {
    // Remove todos os caracteres que não são números
    let value = e.target.value.replace(/\D/g, '');
    
    // Aplica a formatação do CPF (###.###.###-##)
    if (value.length > 3) {
        value = value.substring(0, 3) + '.' + value.substring(3);
    }
    if (value.length > 7) {
        value = value.substring(0, 7) + '.' + value.substring(7);
    }
    if (value.length > 11) {
        value = value.substring(0, 11) + '-' + value.substring(11);
    }
    
    // Limita o tamanho máximo do CPF (11 dígitos + formatação)
    if (value.length > 14) {
        value = value.substring(0, 14);
    }
    
    e.target.value = value;
});

document.getElementById('idusuario').addEventListener('change', function() {
    const select = this;
    const emailInput = document.getElementById('email');
    
    if (select.value) {
        const selectedOption = select.options[select.selectedIndex];
        emailInput.value = selectedOption.dataset.email;
    } else {
        emailInput.value = '';
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