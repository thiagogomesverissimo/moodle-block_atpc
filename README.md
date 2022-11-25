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

## Tabela do DUmp do leonidas:

- iassign_allsubmissions
- iassign
- iassign_ilm
- iassign_statement
- iassign_submission


    SELECT count(s.id) FROM mdl_iassign_statement AS stat, 
                            mdl_iassign AS i, 
                            mdl_iassign_submission AS s 
    WHERE stat.iassignid = i.id 
        AND i.course = 484 
        AND s.iassign_statementid = stat.id;

484 - curso licenciatura
489 - curso atual
