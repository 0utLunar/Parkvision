document.addEventListener('DOMContentLoaded', () => {
  const reserveButtons = document.querySelectorAll('.spot');
  const reserveFormSection = document.getElementById('reserve-form-section');
  const spotInput = document.getElementById('spot');
  const reserveForm = document.getElementById('reserve-form');
  const notificationDiv = document.getElementById('notification');

  // Função para atualizar o status das vagas com base no banco de dados
  function atualizarVagas() {
      fetch('vagas.php')
          .then(response => response.json())
          .then(vagas => {
              vagas.forEach(vaga => {
                  const spotElement = document.querySelector(`.spot[data-numero-vaga='${vaga.numero_vaga}']`);
                  if (spotElement) {
                      // Remove as classes de status anteriores
                      spotElement.classList.remove('free', 'occupied', 'reserved');
                      // Adiciona a nova classe correspondente ao status
                      spotElement.classList.add(vaga.status_vaga); 
                      // Atualiza o atributo data-status
                      spotElement.setAttribute('data-status', vaga.status_vaga);
                  }
              });
          })
          .catch(error => {
              console.error('Erro ao buscar as vagas: ', error);
          });
  }

  // Atualiza as vagas quando a página carregar
  atualizarVagas();

  // Atualiza as vagas periodicamente a cada 30 segundos (ou o tempo que preferir)
  setInterval(atualizarVagas, 2000);

  // Mostra o formulário de reserva ao clicar na vaga
  reserveButtons.forEach(button => {
      button.addEventListener('click', function() {
          const numeroVaga = this.dataset.numeroVaga;
          spotInput.value = numeroVaga;
          reserveFormSection.style.display = 'block'; // Exibe o formulário de reserva
          notificationDiv.style.display = 'none'; // Oculta notificações anteriores ao exibir o formulário
      });
  });

// Envia o formulário via AJAX
reserveForm.addEventListener('submit', function(event) {
  event.preventDefault(); // Evita o comportamento padrão do formulário

  const formData = new FormData(reserveForm); // Coleta os dados do formulário

  // Envia a requisição AJAX para o servidor
  fetch('reserve.php', {
      method: 'POST',
      body: formData
  })
  .then(response => response.json())
  .then(data => {
      // Exibe a mensagem de sucesso ou erro
      notificationDiv.style.display = 'block';
      notificationDiv.innerText = data.message;

      // Se a reserva foi bem-sucedida, atualiza as vagas
      if (data.status === 'success') {
          atualizarVagas(); // Chama a função para atualizar o status das vagas
          reserveFormSection.style.display = 'none'; // Esconde o formulário
      }
  })
  .catch(error => {
      console.error('Erro ao reservar a vaga:', error);
      notificationDiv.style.display = 'block';
      notificationDiv.innerText = 'Erro ao tentar reservar a vaga. Tente novamente mais tarde.';
  });
});

  // Fazer requisição AJAX para buscar dados das vagas reservadas
  fetch('buscar_vagas_reservadas.php')
  .then(response => response.json())
  .then(data => {
      let tabela = document.getElementById('tabela-vagas');

      data.forEach(vaga => {
          let row = document.createElement('tr');

          let cellNumero = document.createElement('td');
          cellNumero.textContent = vaga.numero_vaga;
          row.appendChild(cellNumero);

          let cellObservacao = document.createElement('td');
          cellObservacao.textContent = vaga.observacao_vaga || "Nenhuma observação";
          row.appendChild(cellObservacao);

          let cellAcao = document.createElement('td');
          let btnDesreservar = document.createElement('button');
          btnDesreservar.textContent = "Excluir Reserva";
          btnDesreservar.onclick = () => desreservarVaga(vaga.numero_vaga);
          cellAcao.appendChild(btnDesreservar);
          row.appendChild(cellAcao);

          tabela.appendChild(row);
      });
  })
  .catch(error => console.error('Erro ao buscar vagas reservadas:', error));
});

// Função para desreservar uma vaga
function desreservarVaga(numeroVaga) {
if (confirm(`Tem certeza que deseja exluir a reserva a vaga ${numeroVaga}?`)) {
  // Enviar requisição para o PHP para desreservar a vaga
  fetch('desreservar_vaga.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
      },
      body: JSON.stringify({ numero_vaga: numeroVaga })
  })
  .then(response => response.json())
  .then(data => {
      alert(data.message);
      if (data.status === 'success') {
          // Atualiza a tabela após a desreservação
          location.reload();
      }
  })
  .catch(error => console.error('Erro ao excluir a reserva a vaga:', error));
}
}
