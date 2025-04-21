// Scrip para abrir o modal de visualização de reservas do hospede selecionado
document.addEventListener('DOMContentLoaded', function () {
  const botoesVerReservas = document.querySelectorAll('.ver-reservas-btn');
    botoesVerReservas.forEach(botao => {
      botao.addEventListener('click', function () {
        const idCliente = this.getAttribute('data-idcliente');

        fetch('hospedes.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ idcliente: idCliente })
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
        })
      })
    })
})

// Script para garantir que o modal seja fechado corretamente
document.addEventListener('hidden.bs.modal', function (event) {
  const modal = event.target;
  if (modal.classList.contains('modal')) {
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
      backdrop.remove();
    }
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
  }
});

// Script para abrir o modal de edição de hospede
document.querySelectorAll(".btn-editar").forEach((btn) => {
  btn.addEventListener("click", function () {
    const id = this.getAttribute("data-id");

    fetch("hospedes.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ action: "buscar_hospede", id: id }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.error) {
          alert(data.error);
          return;
        }

        document.getElementById("editId").value = id;
        document.getElementById("editNome").value = data.nome;
        document.getElementById("editEmail").value = data.email;
        document.getElementById("editTelefone").value = data.telefone;
      });
  });
});

// Script para formatar a máscara do telefone no modal de edição
document.addEventListener("DOMContentLoaded", function () {
  const telefoneInput = document.getElementById("editTelefone");

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

// Script para atualizar os dados do hóspede no modal de edição
document.getElementById("formEditarHospede").addEventListener("submit", function (e) {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form); // já contém action = editar_hospede

  fetch("hospedes.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((response) => {
      const msgErro = document.getElementById("mensagemErro");
      if (response.success) {
        msgErro.textContent = "";
        alert("Hóspede atualizado com sucesso!");
        location.reload();
      } else {
        msgErro.textContent = response.error || "Erro ao atualizar.";
      }
    });
});

// Script da mensagem de exclusão

function removerHospede(id) {
  alert("Ainda não implementado.");
}