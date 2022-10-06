Plugin moodle (em desenvolvimento) para disciplina: 

MAC5857 - Desenvolvimento de Sistemas Web para Apoio ao Ensino/Aprendizagem
prof. Leonidas de Oliveira Brandao

Ambiente dev:

    cd blocks
    git clone 

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


SELECT count(s.id) FROM s_iassign_statement AS stat, s_iassign AS i, s_iassign_submission  AS s WHERE stat.iassignid = i.id AND i.course = 484
AND s.iassign_statementid = stat.id;

484 - curso licenciatura
489 - curso atual
