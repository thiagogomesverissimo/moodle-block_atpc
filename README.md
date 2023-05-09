Plugin moodle: Programming Exercises Teaching Assistant - peta

MAC5857 - Desenvolvimento de Sistemas Web para Apoio ao Ensino/Aprendizagem

In config.php:

    $CFG->dirroot   = '/home/thiago/repos/moodle3_composer_saw';

prof. Leonidas de Oliveira Brandao

Ambiente dev:

    cd blocks
    git clone git@github.com:thiagogomesverissimo/moodle-block_peta.git peta

Será que métodos de Aprendizagem de Máquina são canhões para matar mosca?

### Anotações

Tabelas:

- mdl_iassign
- mdl_iassign_allsubmissions
- mdl_iassign_ilm
- mdl_iassign_ilm_config
- mdl_iassign_log
- mdl_iassign_security
- mdl_iassign_statement
- mdl_iassign_submission
- mdl_iassign_submission_comment

Reinstalando:

    php admin/cli/uninstall_plugins.php --plugins=block_itpc --run
    php admin/cli/upgrade.php


## Análise da qualidade do enunciado:

### exercícios com problemas de interpretação

Algumas envidências:

- Média e mediana de submissões por usuários distantes

### Gráfico de tempo versus variação de código, colorir com a nota
- média de envios acima -> possivel problema no enunciado

Medidas de Comportamento dos alunos:

- Olhar histórico do alunos.
- classificar alunos muito acima da média com algum perfil "insistente" - considerar alguns desvião padrão da média
- classificar alunos que submetem muito rápido tempo - impaciente (pouca alteração)
- intervalo maior com notas melhores;
- frequência com que esses perfis (impaciente e insistente) ocorrem
- Gráfico de tempo versus variação de código fixando o aluno com vários exercícios 

Considerações:

- Considerar as submissões "úteis", ou seja, que tiveram alteração na resposta
- nesse primeiro momento vamos considerar alteração no código, apenas o tamanho: 

    select CHAR_LENGTH(answer) from s_iassign_allsubmissions;

Usar como referência - o Lucas fez uma iterativo e está no github

- I know what you coded last summer - https://sol.sbc.org.br/index.php/sbie/article/view/18117/17951
- Subir essa aplicação, que tb analisa os dados do ivprog - http://200.144.254.107/git/LInE/ivprog_log_analysis


arrumar submissões únicas para serem consideradas na análise

Alinhar a direita na tabela
$table->align = array('left','right','right'); // (n. de colunas)


grade com casa decimal

plugin moodle para baixar todos questinãrios de um curso

arrumar sequencia dos statements

deixar genérico para qualquer outro plugin, exemplo vpl

barbara - 9749 - aluno boa
jailson - 9769 - good
jose ailton - 9772 - good
pedro custodio - 9789 - good

larissa - 9778 - bad
demerval - 9757 - bad


módulo da diferença 



          
