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

document.addEventListener('DOMContentLoaded', function() {
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


    // Edição de reserva
    const editarButtons = document.querySelectorAll('.btn-editar');
    editarButtons.forEach(button => {
        button.addEventListener('click', function() {
            const idReserva = this.getAttribute('editar');
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=buscar_reserva&id=${idReserva}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showNotificationModal(data.error, 'error');
                    return;
                }
                
                document.getElementById('editId').value = data.idreserva;
                document.getElementById('editCheckin').value = data.checkin;
                document.getElementById('editCheckout').value = data.checkout;
                document.getElementById('editPreco').value = data.preco;
            })
            .catch(error => {
                showNotificationModal('Erro ao buscar reserva: ' + error, 'error');
            });
        });
    });

    // Formulário de edição
    const formEditar = document.getElementById('formEditarReserva');
    if (formEditar) {
        formEditar.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('mensagemErro').textContent = data.error;
                    return;
                }
                
                if (data.success) {
                    showNotificationModal('Reserva atualizada com sucesso!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            })
            .catch(error => {
                document.getElementById('mensagemErro').textContent = 'Erro ao atualizar: ' + error;
            });
        });
    }

    // Remoção de reserva
    const removerButtons = document.querySelectorAll('.btn-remover');
    removerButtons.forEach(button => {
        button.addEventListener('click', function() {
            const idReserva = this.getAttribute('remover');
            
            // Verifica se há dependências antes de remover
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=verificar_remocao&id=${idReserva}`
            })
            .then(response => response.json())
            .then(data => {
                const removerMessage = document.getElementById('removerMessage');
                const removerFooter = document.getElementById('removerFooter');
                
                if (data.error) {
                    removerMessage.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                    removerFooter.innerHTML = '';
                    return;
                }
                
                if (data.canDelete) {
                    removerMessage.innerHTML = `<p>Tem certeza que deseja remover esta reserva?</p>`;
                    removerFooter.innerHTML = `
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="confirmarRemocao">Remover</button>
                    `;
                    
                    document.getElementById('confirmarRemocao').addEventListener('click', function() {
                        fetch(window.location.href, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=remover_reserva&id=${idReserva}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalRemover'));
                            modal.hide();
                            
                            showNotificationModal(data.message, data.status);
                            if (data.status === 'success') {
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            }
                        })
                        .catch(error => {
                            showNotificationModal('Erro ao remover: ' + error, 'error');
                        });
                    });
                } else {
                    removerMessage.innerHTML = `
                        <div class="alert alert-warning">
                            Não é possível remover esta reserva pois está vinculada a ${data.count} ${data.table}.
                        </div>
                    `;
                    removerFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>';
                }
            })
            .catch(error => {
                showNotificationModal('Erro ao verificar remoção: ' + error, 'error');
            });
        });
    });
});