// Script para abrir o modal de visualização de reservas do hospede selecionado
document.addEventListener('DOMContentLoaded', function () {
    // Abrir o modal de reservas
    const botoesVerReservas = document.querySelectorAll('.ver-reservas-btn');
    botoesVerReservas.forEach(botao => {
        botao.addEventListener('click', function () {
            const idtipo_quarto = this.getAttribute('data-id_tquarto');

            fetch('tquartos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ idtipo_quarto: idtipo_quarto })
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('corpo-tabela-reservas').innerHTML = data;
                const modal = new bootstrap.Modal(document.getElementById('modalReservas'));
                modal.show();
            })
            .catch(error => {
                console.error('Erro ao buscar reservas:', error);
                document.getElementById('corpo-tabela-reservas').innerHTML = '<tr><td colspan="7"> Erro ao carregar reservas. </td></tr>';
            });
        });
    });

    // Script para ordenar a tabela em ordem crescente/decrescente
    const getCellValue = (tr, idx) => tr.children[idx].innerText.trim();
    const isNumeric = val => !isNaN(val) && val !== '';

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