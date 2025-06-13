// Função para mostrar o modal de mensagens
    function showNotificationModal(message, type) {
      const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
      const modalIcon = document.getElementById('modalIcon');
      const modalMessage = document.getElementById('modalMessage');
      
      if (type === 'success') {
          modalIcon.className = 'notification-icon notification-success';
          modalIcon.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
          modalMessage.className = 'notification-message text-success';
      } else if (type === 'error') {
          modalIcon.className = 'notification-icon notification-error';
          modalIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i>';
          modalMessage.className = 'notification-message text-danger';
      } else {
          modalIcon.className = 'notification-icon notification-info';
          modalIcon.innerHTML = '<i class="bi bi-info-circle-fill"></i>';
          modalMessage.className = 'notification-message text-primary';
      }
      
      modalMessage.textContent = message;
      modal.show();
      
      const timeout = type === 'success' ? 5000 : 10000;
      setTimeout(() => modal.hide(), timeout);
      
      modal._element.addEventListener('hidden.bs.modal', () => {
          if (type === 'success') {
              window.location.reload();
          }
      }, {once: true});
    }

    // Botão Voltar
    document.getElementById('btnVoltar').addEventListener('click', () => {
      window.history.back();
    });


    // Sistema de Filtragem
    function aplicarFiltros() {
    const filterId = document.getElementById('filterId').value.trim().toLowerCase();
    const filterHotel = document.getElementById('filterHotel').value.trim().toLowerCase();
    const filterNumber = document.getElementById('filterNumber').value.trim().toLowerCase();
    const filterType = document.getElementById('filterType').value.trim().toLowerCase();
    const filterCapacity = document.getElementById('filterCapacity').value.trim().toLowerCase();
    const filterAvailability = document.getElementById('filterAvailability').value.trim().toLowerCase();

    const filterMinValueRaw = document.getElementById('filterMinValue').value.replace('R$', '').replace(/\./g, '').replace(',', '.');
    const filterMaxValueRaw = document.getElementById('filterMaxValue').value.replace('R$', '').replace(/\./g, '').replace(',', '.');
    const filterMinValue = parseFloat(filterMinValueRaw) || 0;
    const filterMaxValue = parseFloat(filterMaxValueRaw) || Number.MAX_VALUE;

    const rows = document.querySelectorAll('#tabela-quartos tbody tr:not(#noResultsRow)');
    const noResultsRow = document.getElementById('noResultsRow');

    let visibleCount = 0;

    rows.forEach(row => {
      const id = row.cells[0].textContent.trim().toLowerCase();
      const hotel = row.cells[1].textContent.trim().toLowerCase();
      const numero = row.cells[2].textContent.trim().toLowerCase();
      const tipo = row.cells[3].textContent.trim().toLowerCase();
      const capacidade = row.cells[4].textContent.trim().toLowerCase();
      const disponibilidade = row.cells[6].textContent.trim().toLowerCase();

      const valorText = row.cells[7].textContent.replace('R$', '').replace(/\./g, '').replace(',', '.');
      const valor = parseFloat(valorText);

      let show = true;

      if (filterId && id !== filterId) show = false;
      if (filterHotel && !hotel.includes(filterHotel)) show = false;
      if (filterNumber && numero !== filterNumber) show = false;
      if (filterType && tipo !== filterType) show = false;
      if (filterCapacity && capacidade !== filterCapacity) show = false;
      if (filterAvailability && disponibilidade !== filterAvailability) show = false;
      if (!isNaN(filterMinValue) && valor < filterMinValue) show = false;
      if (!isNaN(filterMaxValue) && valor > filterMaxValue) show = false;

      row.style.display = show ? '' : 'none';

      if (show) visibleCount++;
    });

    noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
  }

  document.getElementById('btnApplyFilters').addEventListener('click', aplicarFiltros);
    
    document.getElementById('btnClearFilters').addEventListener('click', () => {
      document.getElementById('filterId').value = '';
      document.getElementById('filterHotel').value = '';
      document.getElementById('filterNumber').value = '';
      document.getElementById('filterType').value = '';
      document.getElementById('filterCapacity').value = '';
      document.getElementById('filterAvailability').value = '';
      document.getElementById('filterMinValue').value = '';
      document.getElementById('filterMaxValue').value = '';
      
      const rows = document.querySelectorAll('#tabela-quartos tbody tr');
      rows.forEach(row => row.style.display = '');

      const noResultsRow = document.getElementById('noResultsRow');
      if (noResultsRow) noResultsRow.style.display = 'none';
    });

    // Demais scripts com DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
      // Script para o modal de descrição
      const modalDescricao = document.getElementById('modalDescricao');
      
      if (modalDescricao) {
          modalDescricao.addEventListener('show.bs.modal', function(event) {
          const button = event.relatedTarget;
          const descricao = button.getAttribute('data-descricao');
          const modalBody = modalDescricao.querySelector('#descricaoCompleta');
          modalBody.textContent = descricao;
          });
      }

        // Script para abrir o modal de visualização de reservas
        const botoesVerReservas = document.querySelectorAll('.ver-reservas-btn');
        botoesVerReservas.forEach(botao => {
            botao.addEventListener('click', function() {
            const idquarto = this.getAttribute('reservas');

            fetch('quartos.php', {
                method: 'POST',
                headers: {
                'Content-Type': 'application/json'
                },
                body: JSON.stringify({ idquarto: idquarto })
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('corpo-tabela-reservas').innerHTML = data;
                const modal = new bootstrap.Modal(document.getElementById('modalReservas'));
                modal.show();
            })
            .catch(error => {
                console.error('Erro ao buscar reservas:', error);
                document.getElementById('corpo-tabela-reservas').innerHTML = '<tr><td colspan="7">Erro ao carregar reservas.</td></tr>';
            });
            });
        });

        // IDs que devem receber formatação monetária
        const valorIds = ['editValor', 'filterMinValue', 'filterMaxValue'];

        valorIds.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');

                    if (value === '') {
                        this.value = '';
                        return;
                    }

                    value = (parseInt(value) / 100).toFixed(2) + '';
                    value = value.replace(".", ",");
                    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
                    this.value = 'R$ ' + value;
                });
            }
        });

        // IDs que devem aceitar apenas números
        const ids = ['editValor', 'editNumero', 'filterNumber', 'filterId', 'filterMinValue', 'filterMaxValue'];

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

        // Script para abrir o modal de edição de quarto
        document.querySelectorAll(".btn-editar[editar]").forEach((btn) => {
            btn.addEventListener("click", function() {
            const id = this.getAttribute("editar");
        
            fetch("quartos.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ action: "buscar_quarto", id: id }),
            })
            .then((res) => res.json())
            .then((data) => {
                if (data.error) {
                showNotificationModal(data.error, 'error');
                return;
                }
        
                document.getElementById("editId").value = id;
                document.getElementById("editNumero").value = data.numero;
                document.getElementById("editTipoQuarto").value = data.tq_idtipo;
                document.getElementById("editOcupacao").value = data.o_idocupacao;
                document.getElementById("editDescricao").value = data.descricao;
                document.getElementById("editDisponibilidade").value = data.disponibilidade;
                document.getElementById("editValor").value = 'R$ ' + data.valor;
            });
            });
        });

        // Script para atualizar os dados do quarto
        document.getElementById("formEditarQuarto").addEventListener("submit", function(e) {
            e.preventDefault();

            const valorInput = document.getElementById("editValor");
            let valorNumerico = valorInput.value
              .replace("R$", "")
              .replace(/\./g, "")
              .replace(",", ".");
            
            valorInput.value = parseFloat(valorNumerico).toFixed(2);
                  
            const form = e.target;
            const formData = new FormData(form);
        
            fetch("quartos.php", {
            method: "POST",
            body: formData,
            })
            .then((res) => res.json())
            .then((response) => {
            if (response.success) {
                showNotificationModal("Quarto atualizado com sucesso!", 'success');
            } else {
                showNotificationModal(response.error || "Erro ao atualizar.", 'error');
            }
            });
        });

        // Script para o botão de remover
        document.querySelectorAll(".btn-remover").forEach(btn => {
            btn.addEventListener("click", () => {
            const id = btn.getAttribute("remover");

            // Verificar se pode remover
            fetch("quartos.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ action: "verificar_remocao", id })
            })
            .then(res => res.json())
            .then(info => {
                const msgEl    = document.getElementById("removerMessage");
                const footerEl = document.getElementById("removerFooter");
                footerEl.innerHTML = "";

                if (info.error) {
                msgEl.textContent = info.error;
                footerEl.innerHTML = '<button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>';
                }
                else if (!info.canDelete) {
                msgEl.innerHTML = `
                <p>Não é possível remover este quarto.</p>
                <p>Há <strong>${info.count}</strong> registro(s) na tabela <strong>${info.table}</strong> que depende(m) deste quarto.</p>
                `;
                footerEl.innerHTML = '<button class="btn btn-secondary" data-bs-dismiss="modal">Entendido</button>';
                } else {
                msgEl.textContent = "Tem certeza que deseja remover este quarto?";
                footerEl.innerHTML = `
                <button id="confirmRemoveBtn" class="btn btn-danger">Sim, remover</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                `;

                document.getElementById("confirmRemoveBtn")
                .addEventListener("click", () => {
                    fetch("quartos.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: new URLSearchParams({ action: "remover_quarto", id })
                    })
                    .then(res => res.json())
                    .then(resp => {
                    showNotificationModal(resp.message, resp.status === 'success' ? 'success' : 'error');
                    bootstrap.Modal.getInstance(document.getElementById("modalRemover")).hide();
                    });
                });
                }

                new bootstrap.Modal(document.getElementById("modalRemover")).show();
            });
            });
        });

        // Script para ordenar a tabela
        const getCellValue = (tr, idx) => tr.children[idx].innerText.trim();

        // Função que transformar o valor para número real
        const parseToNumber = val => {
            // Remove R$, espaços, pontos (milhar), e troca vírgula por ponto (decimal)
            const cleaned = val.replace(/[^0-9,.-]/g, '')
                              .replace(/\./g, '')
                              .replace(',', '.');
            return parseFloat(cleaned);
        };

        // Verifica se o valor é numérico válido após normalização
        const isNumeric = val => !isNaN(parseToNumber(val));

        // Comparador
        const comparer = (idx, asc) => (a, b) => {
            const valA = getCellValue(asc ? a : b, idx);
            const valB = getCellValue(asc ? b : a, idx);

            return isNumeric(valA) && isNumeric(valB)
                ? parseToNumber(valA) - parseToNumber(valB)
                : valA.localeCompare(valB, 'pt', { numeric: true, sensitivity: 'base' });
        };

        // Script de ordenação
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

        // Script para ocultar o URL no status bar
        const allLinks = document.querySelectorAll('a[href]:not([href^="javascript:"])');
        allLinks.forEach(link => {
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

        // Variáveis globais para controle de modais
    let activeImageModal = null;

    // Configurar o modal de adicionar imagem
    document.querySelectorAll('.btn-add[vimg]').forEach(btn => {
        btn.addEventListener('click', function() {
            const idQuarto = this.getAttribute('vimg');
            document.getElementById('addImagemIdQuarto').value = idQuarto;
            
            // Resetar formulário
            document.getElementById('previewContainer').innerHTML = '';
            document.getElementById('imagens').value = '';
            document.getElementById('uploadFeedback').style.display = 'none';
            document.getElementById('btnSubmitImages').disabled = true;
        });
    });

    // Configurar o modal de visualizar imagem
    document.querySelectorAll('.btn-view[vimg]').forEach(btn => {
        btn.addEventListener('click', function() {
            const idQuarto = this.getAttribute('vimg');
            activeImageModal = idQuarto;
            document.getElementById('modalViewImagem').setAttribute('data-idquarto', idQuarto);
            carregarImagensQuarto(idQuarto);
        });
    });

    // Evento para atualizar imagens
    document.getElementById('btnRefreshImages').addEventListener('click', function() {
        const idQuarto = document.getElementById('modalViewImagem').getAttribute('data-idquarto');
        if (idQuarto) {
            carregarImagensQuarto(idQuarto);
        }
    });

    // Configurar área de upload
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('imagens');
    const previewContainer = document.getElementById('previewContainer');
    const submitBtn = document.getElementById('btnSubmitImages');
    const uploadFeedback = document.getElementById('uploadFeedback');

    // Corrigir clique na área de upload
    uploadArea.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.click();
    });

    // Arrastar e soltar
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('bg-light');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('bg-light');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('bg-light');
        
        if (e.dataTransfer.files.length > 0) {
            // Criar novo DataTransfer para evitar duplicação
            const dataTransfer = new DataTransfer();
            
            // Adicionar apenas o primeiro arquivo (para evitar múltiplos)
            dataTransfer.items.add(e.dataTransfer.files[0]);
            
            // Atualizar input
            fileInput.files = dataTransfer.files;
            
            // Processar arquivo
            handleFiles(fileInput.files);
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            // Criar novo DataTransfer para evitar múltiplos arquivos
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(fileInput.files[0]);
            fileInput.files = dataTransfer.files;
            
            handleFiles(fileInput.files);
        }
    });

    function handleFiles(files) {
        // Limpar previews anteriores
        previewContainer.innerHTML = '';
        
        // Processar apenas o primeiro arquivo
        const file = files[0];
        
        // Verificar tipo de arquivo
        if (!file.type.match('image.*')) {
            showFeedback('Tipo de arquivo não suportado. Selecione uma imagem.', 'error');
            return;
        }
        
        // Verificar tamanho (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            showFeedback('Arquivo muito grande. Tamanho máximo: 5MB.', 'error');
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';
            
            previewItem.innerHTML = `
                <img src="${e.target.result}" alt="${file.name}">
                <button class="remove-btn" type="button">
                    <i class="bi bi-x"></i>
                </button>
            `;
            
            previewItem.querySelector('.remove-btn').addEventListener('click', () => {
                previewItem.remove();
                fileInput.value = '';
                submitBtn.disabled = true;
            });
            
            previewContainer.appendChild(previewItem);
            submitBtn.disabled = false;
        };
        
        reader.readAsDataURL(file);
    }

    // Função para carregar as imagens do quarto
    function carregarImagensQuarto(idQuarto) {
      const container = document.getElementById('containerImagens');
      container.innerHTML = `
          <div class="d-flex justify-content-center align-items-center" style="height: 300px;">
              <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Carregando...</span>
              </div>
          </div>
      `;

      fetch('quartos.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
              action: 'listar_imagens',
              idquarto: idQuarto
          })
      })
      .then(response => {
          if (!response.ok) throw new Error('Erro no servidor');
          return response.text();
      })
      .then(html => {
          container.innerHTML = html;
          document.getElementById('imageCount').textContent = 
              container.querySelectorAll('.count').length || 0;
          
          // Ajustar altura do modal conforme número de imagens
          const rows = Math.ceil(container.querySelectorAll('.card').length / 3);
          const modalBody = document.querySelector('#modalViewImagem .modal-body');
          modalBody.style.minHeight = `${300 + (rows * 250)}px`;
          
          // Adicionar eventos de remoção
          document.querySelectorAll('.btn-remover-imagem').forEach(btn => {
              btn.addEventListener('click', function() {
                  const idImagem = this.getAttribute('data-idimagem');
                  const modalView = bootstrap.Modal.getInstance(document.getElementById('modalViewImagem'));
                  modalView.hide();
                  
                  showConfirmationModal('Tem certeza que deseja remover esta imagem?', () => {
                      removerImagem(idImagem, idQuarto);
                  });
              });
          });
      })
      .catch(error => {
          container.innerHTML = `
              <div class="d-flex justify-content-center align-items-center" style="height: 300px;">
                  <div class="text-center">
                      <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                      <p class="mt-3">Erro ao carregar imagens</p>
                      <small class="text-muted">${error.message}</small>
                  </div>
              </div>
          `;
      });
    }
    // Função para mostrar confirmação personalizada
    function showConfirmationModal(message, callback) {
        const modal = new bootstrap.Modal(document.getElementById('modalRemover'));
        const modalBody = document.querySelector('#modalRemover .modal-body');
        
        modalBody.innerHTML = `
            <div class="notification-icon text-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <h5 id="modalMessage" class="notification-message">${message}</h5>
            <div class="d-flex justify-content-center gap-2 mt-3">
                <button type="button" class="btn btn-danger" id="confirmYes">Sim</button>
                <button type="button" class="btn btn-secondary" id="confirmNo">Não</button>
            </div>
        `;
        
        modal.show();
        
        document.getElementById('confirmYes').addEventListener('click', () => {
            callback();
            modal.hide();
        });
        
        document.getElementById('confirmNo').addEventListener('click', () => {
            modal.hide();
            // Reabrir o modal de imagens
            const modalView = new bootstrap.Modal(document.getElementById('modalViewImagem'));
            modalView.show();
        });
    }

    // Função para remover uma imagem
    function removerImagem(idImagem, idQuarto) {
        fetch('quartos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'remover_imagem',
                idimagem: idImagem
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotificationModal(data.message, 'success');
            } else {
                showNotificationModal(data.error || 'Erro ao remover imagem.', 'error');
            }
            
            // Reabrir o modal de imagens
            setTimeout(() => {
                const modalView = new bootstrap.Modal(document.getElementById('modalViewImagem'));
                modalView.show();
                carregarImagensQuarto(idQuarto);
            }, 100);
        });
    }

    // Envio do formulário de upload de imagens
    document.getElementById('formAddImagem').addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Verificar se há arquivo selecionado
      if (!fileInput.files || fileInput.files.length === 0) {
          showFeedback('Selecione uma imagem para enviar.', 'error');
          return;
      }
      
      const formData = new FormData(this);
      const submitBtn = document.getElementById('btnSubmitImages');
      const originalBtnText = submitBtn.innerHTML;
      
      // Mostrar loading
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';
      showFeedback('Enviando imagem, por favor aguarde...', 'info');
      
      fetch('quartos.php', {
          method: 'POST',
          body: formData
      })
      .then(response => {
          // Primeiro verifica se a resposta é JSON
          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
              return response.text().then(text => {
                  throw new Error(`Resposta inválida do servidor: ${text.substring(0, 100)}...`);
              });
          }
          return response.json();
      })
      .then(data => {
          if (data.success) {
              showFeedback(data.message, 'success');
              
              // Fechar o modal após 2 segundos
              setTimeout(() => {
                  bootstrap.Modal.getInstance(document.getElementById('modalAddImagem')).hide();
                  
                  // Atualizar o modal de visualização se estiver aberto
                  if (activeImageModal) {
                      carregarImagensQuarto(activeImageModal);
                  }
              }, 2000);
          } else {
              showFeedback(data.error || 'Erro no upload da imagem.', 'error');
          }
      })
      .catch(error => {
          console.error('Erro no upload:', error);
          showFeedback(error.message || 'Erro na comunicação com o servidor.', 'error');
      })
      .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnText;
      });
    });

    // Função para mostrar feedback
    function showFeedback(message, type) {
        uploadFeedback.innerHTML = message;
        uploadFeedback.className = 'upload-feedback';
        
        if (type === 'success') {
            uploadFeedback.classList.add('upload-success');
        } else if (type === 'error') {
            uploadFeedback.classList.add('upload-error');
        } else {
            uploadFeedback.style.backgroundColor = 'rgba(52, 152, 219, 0.15)';
            uploadFeedback.style.color = '#2c3e50';
            uploadFeedback.style.border = '1px solid #3498db';
        }
        
        uploadFeedback.style.display = 'block';
    }

});