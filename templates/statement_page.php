<div id="grafico" style=""></div>

<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">submissions</th>
      <th scope="col">userid</th>
      <th scope="col">timecreated</th>
      <th scope="col">timecreated_next</th>
      <th scope="col">grade</th>
      <th scope="col">grade_next</th>
      <th scope="col">answer</th>
      <th scope="col">answer_next</th>
      <th scope="col">diff sec</th>
      <th scope="col">diff answer</th>
    </tr>
  </thead>
  <tbody>
    {{trs}}
  </tbody>
</table>

<b>Observações:</b>
<ul>
    <li>Na tabela acima somente estudantes que submeteram mais que uma vez a tarefa aparecem</li>
</ul>

<script src="https://cdn.plot.ly/plotly-2.16.4.min.js"></script>

<script>

    var trace1 = {

        x: [{{difftime}}], //[1, 2, 3, 4],
        y: [{{diffanswer}}], //[10, 11, 12, 13],
        mode: 'markers',
        /*marker: {
            size: [{{grade_next}}] //[40, 60, 80, 100]
        }*/
    };

    var data = [trace1];

    var layout = {
        title: 'Marker Size',
        showlegend: false,
        height: 600,
        width: 600
    };

    Grafico = document.getElementById('grafico');
    Plotly.newPlot(Grafico, data, layout);


</script>
