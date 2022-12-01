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

484 - curso licenciatura
489 - curso atual

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


select iassign_statementid,count(*) from mdl_iassign_allsubmissions group by iassign_statementid

select userid,iassign_statementid,count(*) from mdl_iassign_allsubmissions where iassign_statementid=5822 group by userid;

