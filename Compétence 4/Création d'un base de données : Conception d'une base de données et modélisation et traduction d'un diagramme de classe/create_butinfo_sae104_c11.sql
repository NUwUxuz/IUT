CREATE TABLE _activites (
    lib_activite VARCHAR(255) PRIMARY KEY
);

CREATE TABLE _competences (
    lib_competence VARCHAR(255) PRIMARY KEY
);

CREATE TABLE _activite_competence (
    lib_activite VARCHAR(255),
    lib_competence VARCHAR(255),
    PRIMARY KEY (lib_activite, lib_competence),
    FOREIGN KEY (lib_activite) REFERENCES _activites(lib_activite),
    FOREIGN KEY (lib_competence) REFERENCES _competences(lib_competence)
);

CREATE TABLE _niveau (
    numero_n INT PRIMARY KEY
);

CREATE TABLE _semestre (
    numero_sem VARCHAR(255) PRIMARY KEY,
    niveau_id INT,
    FOREIGN KEY (niveau_id) REFERENCES _niveau(numero_n)
);

CREATE TABLE _ue (
    code_ue VARCHAR(255) PRIMARY KEY
);

CREATE TABLE _sae (
    code_sae VARCHAR(255) PRIMARY KEY,
    lib_sae VARCHAR(255),
    nb_h_td_enc INT,
    nb_h_td_projet_autonomie INT,
    ue_code VARCHAR(255),
    FOREIGN KEY (ue_code) REFERENCES _ue(code_ue)
);

CREATE TABLE _ressources (
    code_r VARCHAR(255) PRIMARY KEY,
    lib_r VARCHAR(255),
    nb_h_cm_pn INT,
    nb_h_td_pn INT,
    nb_h_tp_pn INT
);

CREATE TABLE _parcours (
    code_p CHAR(1) PRIMARY KEY,
    libelle_parcours VARCHAR(255),
    nbre_gpe_td_p INT,
    nbre_gpe_tp_p INT
);
