Plugin moodle: Programming Exercise Teaching Assistant - peta

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


    UPDATE s_iassign_statement SET name='Total of days' where id=5751;
    UPDATE s_iassign_statement SET name='Print the greatest number among two integers' where id=5653;
    UPDATE s_iassign_statement SET name='Weighted average' where id=5754;
    UPDATE s_iassign_statement SET name='Predecessor and successor' where id=5748;
    UPDATE s_iassign_statement SET name='Average consumption' where id=5746;
    UPDATE s_iassign_statement SET name='Invert a 3-digit integer' where id=5651;
    UPDATE s_iassign_statement SET name='Array - Sum of x and y positions' where id=5658;
    UPDATE s_iassign_statement SET name='Value swap' where id=5747;
    UPDATE s_iassign_statement SET name='Determine whether 2 numbers are equal or different' where id=5652;
    UPDATE s_iassign_statement SET name='Introduction - Read an integer and print it' where id=5649;
    UPDATE s_iassign_statement SET name='Even or odd' where id=5759;
    UPDATE s_iassign_statement SET name='Loop - Sum of odd numbers (until 0 is entered)' where id=5657;
    UPDATE s_iassign_statement SET name='Payroll' where id=5750;
    UPDATE s_iassign_statement SET name='' where id=;

    UPDATE s_course SET shortname='2021_summer',fullname='Introduction to programming (daytime)' where id=472;
    UPDATE s_course SET shortname='2021_summer',fullname='Introduction to programming (nighttime)' where id=475;
    UPDATE s_course SET shortname='2022_summer',fullname='Introduction to programming (daytime)' where id=486;
    UPDATE s_course SET shortname='2022_summer',fullname='Introduction to programming (nighttime)' where id=487;
    UPDATE s_course SET shortname='2023_summer',fullname='Introduction to programming (daytime)' where id=492;
    UPDATE s_course SET shortname='2023_summer',fullname='Introduction to programming (nighttime)' where id=493;
    UPDATE s_course SET shortname='2021_mac118',fullname='Introduction to programming' where id=484;
    UPDATE s_course SET shortname='2022_mac110',fullname='Introduction to programming' where id=489;
   
Quantidade de exercícios por curso

   select courseid, count(*) from (select courseid, count(distinct statementid) from s_block_peta_statement_metrics group by
 statementid) as d group by courseid;

Quantidade de submissões:

 |      472 |             445 |
|      475 |             656 |
|      484 |             409 |
|      486 |             379 |
|      487 |             302 |
|      489 |             414 |
|      492 |             320 |
|      493 |             522 |




          
