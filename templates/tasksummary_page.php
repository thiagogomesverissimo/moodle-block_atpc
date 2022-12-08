<div>
  <p>Quantidade de tarefas moodle com Itarefas: {{tasks}}</p>
</div>

<div>
  <p>Quantidade de exerícicios do Itarefas: {{statements}} </p>
</div>

<div>
  <p>Quantidade de exerícicios do Itarefas com submissões: {{statements_with_submissions_total}} </p>
</div>


<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">statement id</th>
      <th scope="col">Enunciado</th>
      <th scope="col">Quantidade de submissões</th>
      <th scope="col">Quantidade de usuários</th>
      <th scope="col">Média de submissões por usuários</th>
      <th scope="col">Mediana</th>
      <th scope="col">Máximo de submissões por um único usuário</th>
    </tr>
  </thead>
  <tbody>
    {{trs}}
  </tbody>
</table>

