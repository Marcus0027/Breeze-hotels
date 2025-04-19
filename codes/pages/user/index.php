<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reserva de Quartos</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }
    body {
      background: #f3f4f6;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }
    .search-form {
      background: white;
      padding: 2rem;
      border-radius: 1.5rem;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 800px;
    }
    .search-form h2 {
      margin-bottom: 1.5rem;
      font-size: 1.5rem;
      font-weight: 600;
    }
    .form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1rem;
    }
    .form-row label {
      font-weight: 600;
      margin-bottom: 0.25rem;
      display: block;
    }
    .form-row input,
    .form-row select {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #d1d5db;
      border-radius: 0.5rem;
      background-color: white;
      color: #111827;
    }
    .range-slider {
      display: flex;
      flex-direction: column;
    }
    .range-values {
      display: flex;
      justify-content: space-between;
      font-size: 0.9rem;
      color: #4b5563;
    }
    .submit-btn {
      width: 100%;
      padding: 0.75rem;
      font-size: 1rem;
      font-weight: 600;
      background: #2563eb;
      color: white;
      border: none;
      border-radius: 0.75rem;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .submit-btn:hover {
      background: #1e40af;
    }

    /* Estilo Flatpickr para combinar com os inputs */
    .flatpickr-input {
      background-color: white;
      border: 1px solid #d1d5db;
      border-radius: 0.5rem;
      padding: 0.5rem;
      width: 100%;
      color: #111827;
    }

    /* Customização visual do calendário */
    .flatpickr-calendar {
      font-family: 'Inter', sans-serif;
      border-radius: 1rem;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      border: 1px solid #e5e7eb;
    }
    .flatpickr-months .flatpickr-month {
      background-color: #ffffff;
      border-bottom: 1px solid #e5e7eb;
      padding: 0.5rem 1rem;
    }
   /* Remover o estilo padrão do select do mês */
.flatpickr-current-month {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: 600;
  gap: 0.4rem;
  background: none;
  padding: 0.5rem;
  border: none;
}

/* Estilizar o mês como um dropdown mais elegante */
.flatpickr-monthDropdown-months {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  border: none;
  background: transparent;
  font-weight: 600;
  font-size: 1rem;
  padding-right: 1.2rem;
  cursor: pointer;
  color: #111827;
}

.flatpickr-monthDropdown-months:focus {
  outline: none;
}

/* Estilizar o input de ano como texto plano */
.flatpickr-current-month input.cur-year {
  appearance: textfield;
  border: none;
  background: none;
  font-weight: 600;
  font-size: 1rem;
  text-align: left;
  width: 3.5rem;
  padding: 0;
  margin: 0;
  color: #111827;
}

.flatpickr-current-month input.cur-year:focus {
  outline: none;
}

/* Setas de ano */
.numInputWrapper span.arrowUp,
.numInputWrapper span.arrowDown {
  color: #4b5563;
  padding: 0 4px;
  font-size: 0.8rem;
}

  </style>
</head>
<body>
  <form class="search-form" method="POST" action="reserva.php">
    <h2>Encontre sua estadia perfeita</h2>
    <div class="form-row">
      <div>
        <label for="destino">Destino</label>
        <input type="text" id="destino" name="destino" placeholder="Cidade ou hotel" required>
      </div>
      <div>
        <label for="datas">Datas</label>
        <input type="text" id="datas" name="datas" placeholder="Check-in - Check-out" required readonly>
        <input type="hidden" id="checkin" name="checkin">
        <input type="hidden" id="checkout" name="checkout">
      </div>
    </div>

    <div class="form-row">
      <div>
        <label for="adultos">Adultos</label>
        <select id="adultos" name="adultos">
          <option value="1">1</option>
          <option value="2" selected>2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5+</option>
        </select>
      </div>
      <div>
        <label for="criancas">Crianças</label>
        <select id="criancas" name="criancas">
          <option value="0" selected>0</option>
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3+</option>
        </select>
      </div>
      <div>
        <label for="quartos">Quartos</label>
        <select id="quartos" name="quartos">
          <option value="1" selected>1</option>
          <option value="2">2</option>
          <option value="3">3+</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="range-slider">
        <label for="valor">Faixa de Preço (R$)</label>
        <input type="range" id="valor" name="valor" min="50" max="3000" step="50" oninput="atualizaValor(this.value)">
        <div class="range-values">
          <span>R$50</span>
          <span id="valorSelecionado">R$300</span>
          <span>R$3000+</span>
        </div>
      </div>
    </div>

    <button type="submit" class="submit-btn">Buscar Quartos</button>
  </form>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    function atualizaValor(valor) {
      document.getElementById('valorSelecionado').innerText = `R$${valor}`;
    }

    flatpickr("#datas", {
      mode: "range",
      minDate: "today",
      dateFormat: "d/m/Y",
      onClose: function(selectedDates) {
        if (selectedDates.length === 2) {
          const [start, end] = selectedDates;
          document.getElementById('checkin').value = start.toISOString().split('T')[0];
          document.getElementById('checkout').value = end.toISOString().split('T')[0];
        } else {
          document.getElementById('checkin').value = "";
          document.getElementById('checkout').value = "";
        }
      }
    });
  </script>
</body>
</html>

<!-- PHP - reserva.php -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $destino = $_POST['destino'];
  $checkin = $_POST['checkin'];
  $checkout = $_POST['checkout'];
  $adultos = $_POST['adultos'];
  $criancas = $_POST['criancas'];
  $quartos = $_POST['quartos'];
  $valor = $_POST['valor'];

  echo "<h2>Resultados para $destino de $checkin até $checkout</h2>";
  echo "<p>Adultos: $adultos | Crianças: $criancas | Quartos: $quartos | Valor máximo: R$$valor</p>";
}
?>