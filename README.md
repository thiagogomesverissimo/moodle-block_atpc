Plugin moodle (em desenvolvimento) para disciplina: 

MAC5857 - Desenvolvimento de Sistemas Web para Apoio ao Ensino/Aprendizagem
prof. Leonidas de Oliveira Brandao

Ambiente dev:

    cd blocks
    git clone git@github.com:thiagogomesverissimo/tasksummary.git

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




    SELECT count(s.id) FROM mdl_iassign_statement AS stat, 
                            mdl_iassign AS i, 
                            mdl_iassign_submission AS s 
    WHERE stat.iassignid = i.id 
        AND i.course = 484 
        AND s.iassign_statementid = stat.id;

484 - curso licenciatura - 2021
489 - curso atual - 2021

## Tabela do DUmp do leonidas:

- s_iassign_allsubmissions
- s_iassign
- s_iassign_ilm
- s_iassign_statement
- s_iassign_submission

Importando tabelas do saw:

    mariadb -uadmin moodle -padmin < ~/Downloads/bd_moodle_saw2021_iassign.sql
    drop table mdl_iassign_allsubmissions;
    drop table mdl_iassign;
    drop table mdl_iassign_ilm;
    drop table mdl_iassign_statement;
    drop table mdl_iassign_submission;

    rename table s_iassign_allsubmissions to mdl_iassign_allsubmissions; 
    rename table s_iassign to mdl_iassign;
    rename table s_iassign_ilm to mdl_iassign_ilm;
    rename table s_iassign_statement to mdl_iassign_statement;
    rename table s_iassign_submission to mdl_iassign_submission;

    ou apenas:

    drop table s_iassign_allsubmissions;
    drop table s_iassign;
    drop table s_iassign_ilm;
    drop table s_iassign_statement;
    drop table s_iassign_submission;

Medidas de enunciado -  análise de qualidade do exercício:

- exercícios com problemas de interpretação
- Gráfico de tempo versus variação de código, colorir com a nota
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

Agumas queries:

    select iassign_statementid,count(*) from mdl_iassign_allsubmissions group by iassign_statementid

    select userid,iassign_statementid,count(*) from mdl_iassign_allsubmissions where iassign_statementid=5822 group by userid;

    select userid,iassign_statementid,count(*) from mdl_iassign_allsubmissions where iassign_statementid=5822 group by userid;

    select userid,iassign_statementid from mdl_iassign_allsubmissions where iassign_statementid=5822 and userid=9795;

SELECT a.iassign_statementid, 
       a.userid, 
       a.timecreated, 
       a.grade, 
       CHAR_LENGTH(a.answer) as answersize1,

       b.iassign_statementid, 
       b.userid, 
       b.timecreated, 
       b.grade, 
       CHAR_LENGTH(b.answer) as answersize2

FROM s_iassign_allsubmissions AS a
LEFT JOIN s_iassign_submission AS b
    ON ( a.userid = b. userid AND
         a.iassign_statementid = b.iassign_statementid );


Fixando um iassign_statementid:

    SELECT a.iassign_statementid, 
        a.userid, 
        a.timecreated, 
        a.grade, 
        CHAR_LENGTH(a.answer) as answersize1,

        b.iassign_statementid, 
        b.userid, 
        b.timecreated,
        b.timemodified, 
        b.grade, 
        CHAR_LENGTH(b.answer) as answersize2

    FROM s_iassign_allsubmissions AS a
    LEFT JOIN s_iassign_submission AS b
        ON ( a.userid = b. userid AND
            a.iassign_statementid = b.iassign_statementid )
    WHERE a.iassign_statementid = 5739;


Fixando um usuário:

    SELECT a.iassign_statementid, 
        a.userid, 
        a.timecreated, 
        a.grade, 
        CHAR_LENGTH(a.answer) as answersize1,

        b.iassign_statementid, 
        b.userid, 
        b.timecreated,
        b.timemodified, 
        b.grade, 
        CHAR_LENGTH(b.answer) as answersize2

    FROM s_iassign_allsubmissions AS a
    LEFT JOIN s_iassign_submission AS b
        ON ( a.userid = b. userid AND
            a.iassign_statementid = b.iassign_statementid )
    WHERE a.iassign_statementid = 5739;



          