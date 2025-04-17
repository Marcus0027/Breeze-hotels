<?php 

include "conn.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <title>Hospedes</title>
</head>
<body class="d-flex justify-content-center py-4">
  <main class="w-100 m-auto form-container " style="max-width: 1000px; padding: 1rem;">


  <form method="POST" class="d-flex align-items-end gap-2 flex-wrap">
  <div >
    <label for="pesquisar" class="form-label">Pesquisar</label>
    <input type="text" class="form-control" id="pesquisar" placeholder="Nome ou Id do hóspede.">
  </div>
  <button type="submit" class="btn btn-primary" style="height: 40px;">
    Filtrar
  </button>
</form>
<!--ACIMA ESTA O INPUT DE FILTRAR. FAZER SCRIPT DPS XD-->

    <table class="table table-bordered align-middle caption-top mb-0">
        <thead class="table-dark text-center">
            <tr>
                <th> ID </th>
                <th> Nome </th>
                <th> Email </th>
                <th> CPF </th>
                <th> Telefone </th>
                <th> Reservas </th>
                <th> Ações </th>
            </tr>
        </thead>
        <tbody>
            <tr class="text-center align-middle">
            <?php
              $sql = "SELECT c.idcliente, c.cpf, c.telefone, c.u_idusuarios, u.idusuarios, u.nome, u.email
              FROM cliente c
              JOIN usuarios u ON c.u_idusuarios = u.idusuarios";

              $result = $conn->query($sql);

              if ($result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                    $cpf = $row['cpf'];
                    $cpfFormat = '***.' . substr($cpf, 4, 3) . '.***-**';

                    echo "<td>{$row['idcliente']}</td>
                          <td>{$row['nome']}</td>
                          <td>{$row['email']}</td>
                          <td>{$cpfFormat}</td>
                          <td>{$row['telefone']}</td>";
                  }
              } else {
                  echo "<tr><td colspan='3'>Nenhum funcionário cadastrado.</td></tr>";
              }
            ?>
                <td class="p-1">
                    <button class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalReservas">
                        Ver Reservas
                    </button>
                </td>
                <td class="p-1">
                <button class="btn btn-success btn-sm w-49" data-bs-toggle="modal" data-bs-target="#modalEditar">
                        Editar
                    </button>
                <button class="btn btn-danger btn-sm w-49" onclick="removerHospede(/*aqui vai o id do hospede*/)"> <!--ESSE BOTÃO PRECISA DE FUNÇAO PARA REMOVER!!!-->
                        Remover
                    </button>
                </td>
            </tr>
        </tbody>
        <caption class="text-center">Hóspedes</caption>
    </table>
  </main>

<!-- Modal de Reservas -->
<div class="modal fade" id="modalReservas" tabindex="-1" aria-labelledby="modalReservasLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalReservasLabel">Reservas do Hóspede</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <table class="table table-hover text-center align-middle">
          <thead class="table-secondary">
            <tr>
              <th> ID </th>
              <th> Check-In </th>
              <th> Check-Out </th>
              <th> Hotel </th>
              <th> Quarto </th>
              <th> Tipo Quarto </th>
              <th> Preço da reserva </th>
            </tr>
          </thead>
          <tbody id="corpo-tabela-reservas">
              <tr class="text-center align-middle">
              <?php
                  $sql2 = "SELECT r.idreserva, r.checkin, r.checkout, r.preco, r.c_idcliente, r.cq_idquarto, r.cqh_idhotel, r.cqtq_idtipo,
                  q.idquarto, q.numero, h.idhotel, h.nome, t.idtipo_quarto, t.tipoQuarto
                  FROM reserva r
                  JOIN cliente c ON r.c_idcliente = c.idcliente 
                  JOIN quartos q ON r.cq_idquarto = q.idquarto 
                  JOIN hoteis h ON r.cqh_idhotel = h.idhotel 
                  JOIN tipo_quarto t ON r.cqtq_idtipo = t.idtipo_quarto";

                  $result2 = $conn->query($sql2);

                  if ($result2->num_rows > 0) {
                      while ($row2 = $result2->fetch_assoc()) {
                          echo "<td>{$row2['idreserva']}</td>
                                <td>{$row2['checkin']}</td>
                                <td>{$row2['checkout']}</td>
                                <td>{$row2['nome']}</td>
                                <td>{$row2['numero']}</td>
                                <td>{$row2['tipoQuarto']}</td>
                                <td>{$row2['preco']}</td>";
                      }
                  } else {
                      echo "<tr><td colspan='3'>Nenhum funcionário cadastrado.</td></tr>";
                  }
                ?>
              </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<!--modal Editar-->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Hóspede</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="editar.php" method="POST"> <!--Fazer arquivo editar.php para funcionar !!! --> 
                    <input type="hidden" id="editId" name="idHospede">
                    <div class="mb-3">
                        <label for="editNome" class="form-label">Nome do hóspede</label>
                        <input type="text" class="form-control" id="editNome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="editemail" class="form-label">Email do hóspede</label>
                        <input type="text" class="form-control" id="editemail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edittelefone" class="form-label">Telefone do hóspede</label>
                        <input type="text" class="form-control" id="edittelefone" name="telefone" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

  function removerHospede(id){
    alert("NADA FOI REMOVIDO PQ NAO FUNCIONA AINDA '-')b") // seria bom todos os scripts serem em arquivos separados !! coloquei aqui so por conveniência
  }


</script>
</body>
</html>