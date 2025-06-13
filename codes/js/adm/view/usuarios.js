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
// Script para abrir o modal de edição de ocupação
document.querySelectorAll(".btn-editar").forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = this.getAttribute("editar");
  
      fetch("usuarios.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ action: "buscar_usuario", id: id }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.error) {
            showNotificationModal(data.error, 'error');
            return;
          }
  
          document.getElementById("editId").value = id;
          document.getElementById("editNome").value = data.nome;
          document.getElementById("editEmail").value = data.email;
        });
    });
  });

// Script para atualizar os dados do hóspede no modal de edição
document.getElementById("formEditarUsuario").addEventListener("submit", function (e) {
    e.preventDefault();
  
    const form = e.target;
    const formData = new FormData(form);
  
    fetch("usuarios.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((response) => {
        const msgErro = document.getElementById("mensagemErro");
        if (response.success) {
          showNotificationModal("Usuário atualizado com sucesso!", 'success');
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
    fetch("usuarios.php", {
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
        <p>Não é possível remover este usuário.</p>
        <p>Há <strong>${info.count}</strong> registro(s) na tabela <strong>${info.table}</strong> que depende(m) deste usuário.</p>
        `;
        footerEl.innerHTML = '<button class="btn btn-secondary" data-bs-dismiss="modal"> Entendido </button>';
    } else {
        // pode remover — confirmação
        msgEl.textContent = "Tem certeza que deseja remover este usuário?";
        footerEl.innerHTML = `
        <button id="confirmRemoveBtn" class="btn btn-danger"> Sim, remover </button>
        <button class="btn btn-secondary" data-bs-dismiss="modal"> Cancelar </button>
        `;

        // ao clicar em confirmar, chama a action de remoção
        document.getElementById("confirmRemoveBtn")
        .addEventListener("click", () => {
            fetch("usuarios.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ action: "remover_usuario", id })
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