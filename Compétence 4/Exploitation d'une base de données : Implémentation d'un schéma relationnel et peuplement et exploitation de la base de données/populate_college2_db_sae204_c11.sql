set schema 'colleges2';
-----
-- PREREQUIS : Ce script ne fonctionne que si vous avez défini un répertoire de travail (Working directory)
--             par défaut dans lequel se trouve votre script et les données sous forme de fichiers CSV

-- _region - infos dans fr-en-etablissements-ep
/*WbImport -file=./fr-en-etablissements-ep.csv
         -header=true
         -delimiter=';'
         -quoteChar='"' 
         -table=_region
         -schema=colleges2
         -mode=insert,update
         -filecolumns=$wb_skip$,$wb_skip$,$wb_skip$,$wb_skip$,$wb_skip$,$wb_skip$,$wb_skip$,$wb_skip$,libelle_region,$wb_skip$,$wb_skip$,$wb_skip$,$wb_skip$,$wb_skip$,$wb_skip$,$wb_skip$,$wb_skip$,$wb_skip$,code_region
         -importColumns=libelle_region,code_region
         -keyColumns=code_region; */
         
/* Cette approche est loin d'être optimale et va requérir de charger plusieurs fois le même fichier CSV pour différentes tables.
   Importing file '/Users/delhay/vrac/git/enseignement/but_info/but_info_bdd/bdd_s2/sae_204/colleges-ips/enonce/soluce/./fr-en-etablissements-ep.csv' into table _region
   19 row(s) inserted
   8430 row(s) updated
   Execution time: 3m 11s   
   
   Il vaut donc mieux charger une bonne fois pour toute tous les fichiers CSV dans de grosses tables temporaires puis faire des requêtes. */

-- Données générales pour l'année 2022-2023
drop table if exists _temp_fr_en_etablissements_ep cascade;
create table _temp_fr_en_etablissements_ep(
  uai                     char(8) primary key,
  ep_2022_2023            varchar(7),  -- ep pour l'année 2022-2023 : REP, REP+, HORS REP. Pas utilisé a priori
  nom_etablissement       varchar(108),
  type_etablissement      varchar(39), -- libelle_nature
  statut_public_prive     varchar(18), -- secteur
  libelle_academie        varchar(16), -- lib_academie
  libelle_departement     varchar(23), -- nom_departement
  nom_commune             varchar(45), -- nom_de_la_commune
  libelle_region          varchar(34), -- libelle_region
  uai_tete_de_reseau      char(8),     -- pas utilisé
  qp_a_proximite_o_n      char(16),    -- 'Dans QP' ou NULL
  qp_a_proximite          char(8),     -- code_quartier_prioritaire
  nom_du_qp               varchar(85), -- nom_quartier_prioritaire
  nombre_d_eleves         integer,     -- effectifs
  code_postal             char(5),     -- code_postal
  code_commune            char(5),     -- code_insee_de_la_commune
  code_departement        char(3),     -- code_du_departement
  code_academie           char(2),     -- code_academie
  code_region             char(2),     -- code_region
  libelle_nature          varchar(39), -- libelle_nature
  code_nature             char(3),     -- code_nature
  latitude                numeric,
  longitude               numeric
);

delete from _temp_fr_en_etablissements_ep;
WbImport -file=./fr-en-etablissements-ep.csv
         -header=true
         -delimiter=';'
         -quoteChar='"' 
         -table=_temp_fr_en_etablissements_ep
         -schema=colleges2
         -mode=insert, update -- car il y a des doublons (toute la ligne)
         -filecolumns=uai,ep_2022_2023,nom_etablissement,type_etablissement,statut_public_prive,libelle_academie,libelle_departement,nom_commune,libelle_region,uai_tete_de_reseau,qp_a_proximite_o_n,qp_a_proximite,nom_du_qp,nombre_d_eleves,code_postal,code_commune,code_departement,code_academie,code_region,libelle_nature,code_nature,$wb_skip$,latitude,longitude
         -importColumns=uai,ep_2022_2023,nom_etablissement,type_etablissement,statut_public_prive,libelle_academie,libelle_departement,nom_commune,libelle_region,uai_tete_de_reseau,qp_a_proximite_o_n,qp_a_proximite,nom_du_qp,nombre_d_eleves,code_postal,code_commune,code_departement,code_academie,code_region,libelle_nature,code_nature,latitude,longitude
         -keyColumns=uai;

-- Données sur les effectifs de 2019-2020 à 2022-2023
drop table if exists _temp_fr_en_college_effectif_niveau_sexe_lv;
create table _temp_fr_en_college_effectif_niveau_sexe_lv(
  rentree_scolaire        char(9)       not null,
  region_academique       varchar(26)   not null,
  academie                varchar(16)   not null,
  departement             varchar(21)   not null,
  commune                 varchar(32)   not null,
  numero_college          char(8)       not null,
  denomination_principale varchar(30)   not null,
  patronyme               varchar(30),
  secteur                 varchar(6),
  rep                     numeric(1),
  rep_plus                numeric(1),
  nombre_eleves_total     numeric       not null default 0, -- si champ vide alors 0
  nombre_eleves_segpa     numeric       not null default 0,
  nombre_eleves_ulis      numeric       not null default 0,
  _6eme_total              numeric       not null default 0,
  _6eme_segpa              numeric       not null default 0,
  _6eme_ulis               numeric       not null default 0,
  _6eme_filles             numeric       not null default 0,
  _6emes_garcons           numeric       not null default 0,
  _6eme_lv1_allemand       numeric       not null default 0,
  _6eme_lv1_anglais        numeric       not null default 0,
  _6eme_lv1_espagnol       numeric       not null default 0,
  _6eme_lv1_autres         numeric       not null default 0,
  _6eme_lv2_allemand       numeric       not null default 0,
  _6eme_lv2_anglais        numeric       not null default 0,
  _6eme_lv2_espagnol       numeric       not null default 0,
  _6eme_lv2_italien        numeric       not null default 0,
  _6eme_lv2_autres         numeric       not null default 0,
  _5eme_total              numeric       not null default 0,
  _5eme_segpa              numeric       not null default 0,
  _5eme_ulis               numeric       not null default 0,
  _5eme_filles             numeric       not null default 0,
  _5emes_garcons           numeric       not null default 0,
  _5eme_lv1_allemand       numeric       not null default 0,
  _5eme_lv1_anglais        numeric       not null default 0,
  _5eme_lv1_espagnol       numeric       not null default 0,
  _5eme_lv1_autres         numeric       not null default 0,
  _5eme_lv2_allemand       numeric       not null default 0,
  _5eme_lv2_anglais        numeric       not null default 0,
  _5eme_lv2_espagnol       numeric       not null default 0,
  _5eme_lv2_italien        numeric       not null default 0,
  _5eme_lv2_autres         numeric       not null default 0,
  _4eme_total              numeric       not null default 0,
  _4eme_segpa              numeric       not null default 0,
  _4eme_ulis               numeric       not null default 0,
  _4eme_filles             numeric       not null default 0,
  _4emes_garcons           numeric       not null default 0,
  _4eme_lv1_allemand       numeric       not null default 0,
  _4eme_lv1_anglais        numeric       not null default 0,
  _4eme_lv1_espagnol       numeric       not null default 0,
  _4eme_lv1_autres         numeric       not null default 0,
  _4eme_lv2_allemand       numeric       not null default 0,
  _4eme_lv2_anglais        numeric       not null default 0,
  _4eme_lv2_espagnol       numeric       not null default 0,
  _4eme_lv2_italien        numeric       not null default 0,
  _4eme_lv2_autres         numeric       not null default 0,
  _3eme_total              numeric       not null default 0,
  _3eme_segpa              numeric       not null default 0,
  _3eme_ulis               numeric       not null default 0,
  _3eme_filles             numeric       not null default 0,
  _3emes_garcons           numeric       not null default 0,
  _3eme_lv1_allemand       numeric       not null default 0,
  _3eme_lv1_anglais        numeric       not null default 0,
  _3eme_lv1_espagnol       numeric       not null default 0,
  _3eme_lv1_autres         numeric       not null default 0,
  _3eme_lv2_allemand       numeric       not null default 0,
  _3eme_lv2_anglais        numeric       not null default 0,
  _3eme_lv2_espagnol       numeric       not null default 0,
  _3eme_lv2_italien        numeric       not null default 0,
  _3eme_lv2_autres         numeric       not null default 0,
  code_postal             char(5)       not null, 
  constraint _temp_effectif_pk primary key (rentree_scolaire, numero_college)
);

-- Reformatage des années en années scolaires.
drop function if exists ftg_rentree4_rentree9();
create or replace function ftg_rentree4_rentree9() returns trigger as $$
declare
  annee_int   integer;
begin
  annee_int := new.rentree_scolaire::integer;
  new.rentree_scolaire := to_char(annee_int,'FM9999') || '-' || to_char(annee_int+1,'FM9999');
  return new;
end;
$$ language 'plpgsql';

-- Trigger se déclenchant sur ajout de données dans _temp_fr_en_college_effectif_niveau_sexe_lv
drop trigger if exists tg_rentree4_rentree9 on _temp_fr_en_college_effectif_niveau_sexe_lv;
create trigger tg_rentree4_rentree9
before insert or update of rentree_scolaire
on _temp_fr_en_college_effectif_niveau_sexe_lv
for each row
execute procedure ftg_rentree4_rentree9(); 

-- rentree_scolaire;region_academique;academie;departement;commune;numero_college;denomination_principale;patronyme;secteur;rep;rep_plus;nombre_eleves_total;nombre_eleves_total_hors_segpa_hors_ulis;nombre_eleves_segpa;nombre_eleves_ulis;6eme_total;6eme_hors_segpa_hors_ulis;6eme_segpa;6eme_ulis;6eme_filles;6emes_garcons;6eme_lv1_allemand;6eme_lv1_anglais;6eme_lv1_espagnol;6eme_lv1_autres;6eme_lv2_allemand;6eme_lv2_anglais;6eme_lv2_espagnol;6eme_lv2_italien;6eme_lv2_autres;5eme_total;5eme_hors_segpa_hors_ulis;5eme_segpa;5eme_ulis;5eme_filles;5emes_garcons;5eme_lv1_allemand;5eme_lv1_anglais;5eme_lv1_espagnol;5eme_lv1_autres;5eme_lv2_allemand;5eme_lv2_anglais;5eme_lv2_espagnol;5eme_lv2_italien;5eme_lv2_autres;4eme_total;4eme_hors_segpa_hors_ulis;4eme_segpa;4eme_ulis;4eme_filles;4emes_garcons;4eme_lv1_allemand;4eme_lv1_anglais;4eme_lv1_espagnol;4eme_lv1_autres;4eme_lv2_allemand;4eme_lv2_anglais;4eme_lv2_espagnol;4eme_lv2_italien;4eme_lv2_autres;3eme_total;3eme_hors_segpa_hors_ulis;3eme_segpa;3eme_ulis;3eme_filles;3emes_garcons;3eme_lv1_allemand;3eme_lv1_anglais;3eme_lv1_espagnol;3eme_lv1_autres;3eme_lv2_allemand;3eme_lv2_anglais;3eme_lv2_espagnol;3eme_lv2_italien;3eme_lv2_autres;code_postal
delete from _temp_fr_en_college_effectif_niveau_sexe_lv;
WbImport -file=./fr-en-college-effectifs-niveau-sexe-lv.csv
         -header=true
         -delimiter=';'
         -quoteChar='"' 
         -table=_temp_fr_en_college_effectif_niveau_sexe_lv
         -schema=colleges2
         -mode=insert, update -- car il y a des doublons (toute la ligne)
         -filecolumns=rentree_scolaire,region_academique,academie,departement,commune,numero_college,denomination_principale,patronyme,secteur,rep,rep_plus,nombre_eleves_total,$wb_skip$,nombre_eleves_segpa,nombre_eleves_ulis,_6eme_total,$wb_skip$,_6eme_segpa,_6eme_ulis,_6eme_filles,_6emes_garcons,_6eme_lv1_allemand,_6eme_lv1_anglais,_6eme_lv1_espagnol,_6eme_lv1_autres,_6eme_lv2_allemand,_6eme_lv2_anglais,_6eme_lv2_espagnol,_6eme_lv2_italien,_6eme_lv2_autres,_5eme_total,$wb_skip$,_5eme_segpa,_5eme_ulis,_5eme_filles,_5emes_garcons,_5eme_lv1_allemand,_5eme_lv1_anglais,_5eme_lv1_espagnol,_5eme_lv1_autres,_5eme_lv2_allemand,_5eme_lv2_anglais,_5eme_lv2_espagnol,_5eme_lv2_italien,_5eme_lv2_autres,_4eme_total,$wb_skip$,_4eme_segpa,_4eme_ulis,_4eme_filles,_4emes_garcons,_4eme_lv1_allemand,_4eme_lv1_anglais,_4eme_lv1_espagnol,_4eme_lv1_autres,_4eme_lv2_allemand,_4eme_lv2_anglais,_4eme_lv2_espagnol,_4eme_lv2_italien,_4eme_lv2_autres,_3eme_total,$wb_skip$,_3eme_segpa,_3eme_ulis,_3eme_filles,_3emes_garcons,_3eme_lv1_allemand,_3eme_lv1_anglais,_3eme_lv1_espagnol,_3eme_lv1_autres,_3eme_lv2_allemand,_3eme_lv2_anglais,_3eme_lv2_espagnol,_3eme_lv2_italien,_3eme_lv2_autres,code_postal
         -importColumns=rentree_scolaire,region_academique,academie,departement,commune,numero_college,denomination_principale,patronyme,secteur,rep,rep_plus,nombre_eleves_total,nombre_eleves_total_hors_segpa_hors_ulis,nombre_eleves_segpa,nombre_eleves_ulis,_6eme_total,_6eme_hors_segpa_hors_ulis,_6eme_segpa,_6eme_ulis,_6eme_filles,_6emes_garcons,_6eme_lv1_allemand,_6eme_lv1_anglais,_6eme_lv1_espagnol,_6eme_lv1_autres,_6eme_lv2_allemand,_6eme_lv2_anglais,_6eme_lv2_espagnol,_6eme_lv2_italien,_6eme_lv2_autres,_5eme_total,_5eme_hors_segpa_hors_ulis,_5eme_segpa,_5eme_ulis,_5eme_filles,_5emes_garcons,_5eme_lv1_allemand,_5eme_lv1_anglais,_5eme_lv1_espagnol,_5eme_lv1_autres,_5eme_lv2_allemand,_5eme_lv2_anglais,_5eme_lv2_espagnol,_5eme_lv2_italien,_5eme_lv2_autres,_4eme_total,_4eme_hors_segpa_hors_ulis,_4eme_segpa,_4eme_ulis,_4eme_filles,_4emes_garcons,_4eme_lv1_allemand,_4eme_lv1_anglais,_4eme_lv1_espagnol,_4eme_lv1_autres,_4eme_lv2_allemand,_4eme_lv2_anglais,_4eme_lv2_espagnol,_4eme_lv2_italien,_4eme_lv2_autres,_3eme_total,_3eme_hors_segpa_hors_ulis,_3eme_segpa,_3eme_ulis,_3eme_filles,_3emes_garcons,_3eme_lv1_allemand,_3eme_lv1_anglais,_3eme_lv1_espagnol,_3eme_lv1_autres,_3eme_lv2_allemand,_3eme_lv2_anglais,_3eme_lv2_espagnol,_3eme_lv2_italien,_3eme_lv2_autres,code_postal
         -keyColumns=rentree_scolaire,numero_college;


-- Données sur la valeur ajoutée
drop table if exists _temp_fr_en_indicateurs_valeur_ajoutee_colleges;
create table _temp_fr_en_indicateurs_valeur_ajoutee_colleges(
  session                               char(9),
  uai                                   char(8),
  nom_de_l_etablissement                varchar(87)   not null,
  commune                               varchar(32)   not null,
  departement                           varchar(21)   not null,
  academie                              varchar(16)   not null,
  secteur                               char(2)       not null,
  nb_candidats_g                        numeric       default 0,
  taux_de_reussite_g                    numeric(5,1),
  va_du_taux_de_reussite_g              numeric(5,1),
  nb_candidats_p                        numeric       default 0,
  taux_de_reussite_p                    numeric(5,1),
  note_a_l_ecrit_g                      numeric(4,1),
  va_de_la_note_g                       numeric(4,1),
  note_a_l_ecrit_p                      numeric(4,1),
  taux_d_acces_6eme_3eme                numeric(5,1),
  part_presents_3eme_ordinaire_total    numeric(5,1),
  part_presents_3eme_ordinaire_g        numeric(5,1),
  part_presents_3eme_ordinaire_p        numeric(5,1),
  part_presents_3eme_segpa_total        numeric(5,1),
  nb_mentions_ab_g                      numeric       default 0,
  nb_mentions_b_g                       numeric       default 0,
  nb_mentions_tb_g                      numeric       default 0,
  constraint indicateurs_valeur_ajoutee_pk primary key (uai, session)
);

-- reformatage des années de session en années scolaires
drop function if exists ftg_insert_session();
create or replace function ftg_insert_session() returns trigger as $$
declare
  annee_session   integer;
begin
  annee_session := new.session::integer;
  new.session := to_char(annee_session-1,'FM9999') || '-' || to_char(annee_session,'FM9999');
  return new;
end;
$$ language 'plpgsql';

drop trigger if exists tg_insert_session on _temp_fr_en_indicateurs_valeur_ajoutee_colleges;
create trigger tg_insert_session
before insert or update
on _temp_fr_en_indicateurs_valeur_ajoutee_colleges
for each row
execute procedure ftg_insert_session();

-- session;uai;nom_de_l_etablissement;commune;departement;academie;secteur;nb_candidats_g;taux_de_reussite_g;va_du_taux_de_reussite_g;nb_candidats_p;taux_de_reussite_p;note_a_l_ecrit_g;va_de_la_note_g;note_a_l_ecrit_p;taux_d_acces_6eme_3eme;part_presents_3eme_ordinaire_total;part_presents_3eme_ordinaire_g;part_presents_3eme_ordinaire_p;part_presents_3eme_segpa_total;nb_mentions_ab_g;nb_mentions_b_g;nb_mentions_tb_g;nb_mentions_global_g
delete from _temp_fr_en_indicateurs_valeur_ajoutee_colleges;
WbImport -file=./fr-en-indicateurs-valeur-ajoutee-colleges.csv
         -header=true
         -delimiter=';'
         -quoteChar='"' 
         -table=_temp_fr_en_indicateurs_valeur_ajoutee_colleges
         -schema=colleges2
         -mode=insert, update -- car il y a des doublons (toute la ligne)
         -filecolumns=session,uai,nom_de_l_etablissement,commune,departement,academie,secteur,nb_candidats_g,taux_de_reussite_g,va_du_taux_de_reussite_g,nb_candidats_p,taux_de_reussite_p,note_a_l_ecrit_g,va_de_la_note_g,note_a_l_ecrit_p,taux_d_acces_6eme_3eme,part_presents_3eme_ordinaire_total,part_presents_3eme_ordinaire_g,part_presents_3eme_ordinaire_p,part_presents_3eme_segpa_total,nb_mentions_ab_g,nb_mentions_b_g,nb_mentions_tb_g
         -importColumns=session,uai,nom_de_l_etablissement,commune,departement,academie,secteur,nb_candidats_g,taux_de_reussite_g,va_du_taux_de_reussite_g,nb_candidats_p,taux_de_reussite_p,note_a_l_ecrit_g,va_de_la_note_g,note_a_l_ecrit_p,taux_d_acces_6eme_3eme,part_presents_3eme_ordinaire_total,part_presents_3eme_ordinaire_g,part_presents_3eme_ordinaire_p,part_presents_3eme_segpa_total,nb_mentions_ab_g,nb_mentions_b_g,nb_mentions_tb_g
         -keyColumns=session,uai;

-----------------------
-- Données sur IPS
drop table if exists _temp_fr_en_ips_colleges_ap2022;
create table _temp_fr_en_ips_colleges_ap2022(
  rentree_scolaire          char(9),     
  academie                  varchar(16)   not null,
  code_du_departement       char(3)       not null,
  departement               varchar(21)   not null,
  uai                       char(8),
  nom_de_l_etablissment     varchar(79)   not null,
  code_insee_de_la_commune  varchar(5)    not null,
  nom_de_la_commune         varchar(35)   not null,
  secteur                   varchar(18)   not null,
  effectifs                 integer       not null,
  ips                       numeric(5,1)  not null,
  ecart_type_de_l_ips       numeric(4,1)  not null,
  constraint ips_colleges_ap2022_pk primary key(rentree_scolaire,uai)
);

-- rentree_scolaire;academie;code_du_departement;departement;uai;nom_de_l_etablissment;code_insee_de_la_commune;nom_de_la_commune;secteur;effectifs;ips;ecart_type_de_l_ips
delete from _temp_fr_en_ips_colleges_ap2022;
WbImport -file=./fr-en-ips-colleges-ap2022.csv
         -header=true
         -delimiter=';'
         -quoteChar='"' 
         -table=_temp_fr_en_ips_colleges_ap2022
         -schema=colleges2
         -mode=insert, update -- car il y a peut-être des doublons (toute la ligne)
         -filecolumns=rentree_scolaire,academie,code_du_departement,departement,uai,nom_de_l_etablissment,code_insee_de_la_commune,nom_de_la_commune,secteur,effectifs,ips,ecart_type_de_l_ips
         -importColumns=rentree_scolaire,academie,code_du_departement,departement,uai,nom_de_l_etablissment,code_insee_de_la_commune,nom_de_la_commune,secteur,effectifs,ips,ecart_type_de_l_ips
         -keyColumns=rentree_scolaire,uai;


--------------------
--------------------
-- STATS SUR LES DONNEES : a décommenter pour obtenir le résultat
/*
select count(distinct uai) as nb_row_temp_fr_en_etablissements_ep
from _temp_fr_en_etablissements_ep;
-- 8500 établissements en tout

select count(distinct uai) as nb_row_colleges_temp_fr_en_etablissements_ep
from _temp_fr_en_etablissements_ep
where type_etablissement = 'Collège';
-- 1414

select count(*) as nb_row_temp_fr_en_college_effectif_niveau_sexe_lv
from _temp_fr_en_college_effectif_niveau_sexe_lv;
-- 32982 (colleges,annee)

select count(distinct numero_college) as nb_row_colleges_temp_fr_en_college_effectif_niveau_sexe_lv
from _temp_fr_en_college_effectif_niveau_sexe_lv;
-- 8337

select count(distinct uai) from 
(select uai
from _temp_fr_en_etablissements_ep
where type_etablissement = 'Collège'
intersect
select numero_college as uai
from _temp_fr_en_college_effectif_niveau_sexe_lv) as intersection;
-- 1414 (1414 si pas de restriction sur type_etablissement)
-- A priori tous les colleges de fr_en_etablissements_ep
*/

------------------------------------------------------
---- DISTRIBUTION DES DONNEES DANS LES TABLES
------------------------------------------------------
-- _region : 19 n-uplets
insert into _region (code_region, libelle_region)
  select distinct code_region, libelle_region
  from _temp_fr_en_etablissements_ep
  where code_region is not null;
  

-- _departement : 98 n-uplets --> il en manque
insert into _departement(code_du_departement, nom_departement, code_region)
  select distinct code_departement, libelle_departement, code_region
  from _temp_fr_en_etablissements_ep
  where code_departement is not null;

-- Mise en majuscules et suppression des accents 
update _departement
set nom_departement = upper(nom_departement);
update _departement
set nom_departement = replace(nom_departement,'É','E');
update _departement
set nom_departement = replace(nom_departement,'È','E');
update _departement
set nom_departement = replace(nom_departement,'Ô','O');
  
-- _commune :

-- Uniformisation des noms de commune
update _temp_fr_en_college_effectif_niveau_sexe_lv
set commune = replace(commune, '-', ' ');

delete from _commune;
insert into _commune (code_insee_de_la_commune, nom_de_la_commune, code_du_departement)
  select distinct code_postal as code_commune, commune, code_du_departement
  from _temp_fr_en_college_effectif_niveau_sexe_lv inner join _departement
  on departement = nom_departement
  order by code_commune;
-- 4747 n-uplets

-- Difficulté sur la presentation des communes : prendre un référence
-- https://public.opendatasoft.com/explore/dataset/correspondance-code-cedex-code-insee/table/?flg=fr-fr

drop table if exists _temp_communes;
create table _temp_communes(
  Code              char(5)     not null,
  Libelle           varchar(38),
  Code_INSEE        char(5),
  type_code         varchar(11) not null,
  Nom_commune       varchar(45),
  Nom_departement   varchar(23),
  NOM_EPCI          varchar(75),
  Nom_region        varchar(26)
);
delete from _temp_communes;
WbImport -file=./correspondance-code-cedex-code-insee.csv
         -header=true
         -delimiter=';'
         --quoteChar='"' 
         -table=_temp_communes
         -schema=colleges2
         -mode=insert-- , update -- car il y a des doublons (toute la ligne)
         -filecolumns=Code,Libelle,Code_INSEE,type_code,Nom_commune,Nom_departement,NOM_EPCI,Nom_region
         -importColumns=Code,Libelle,Code_INSEE,type_code,Nom_commune,Nom_departement,NOM_EPCI,Nom_region
         -keyColumns=code_insee;

insert into _commune (code_insee_de_la_commune, nom_de_la_commune, code_du_departement)
  select distinct coalesce(code_insee, code) as code_insee_de_la_commune, commune, code_du_departement
  from _temp_fr_en_indicateurs_valeur_ajoutee_colleges va left outer join _temp_communes tc
  on va.commune = tc.libelle
  inner join _departement d
  on tc.nom_departement = d.nom_departement
  where (code_insee, commune) not in (select code_insee_de_la_commune, nom_de_la_commune from _commune)
    and (code, commune) not in (select code_insee_de_la_commune, nom_de_la_commune from _commune);

insert into _commune (code_insee_de_la_commune, nom_de_la_commune, code_du_departement)
  select distinct code_commune, upper(nom_commune), code_departement
  from _temp_fr_en_etablissements_ep
  where (code_commune, upper(nom_commune)) not in (select code_insee_de_la_commune, nom_de_la_commune from _commune) ;
  


-- Il manque des départements dans _departement

insert into _departement (code_du_departement, code_region, nom_departement)
  values ('015','84','CANTAL'),
         ('032','76','GERS'),
         ('043','84','HAUTE-LOIRE'),
         ('048','76','LOZERE');

insert into _commune (code_insee_de_la_commune, nom_de_la_commune, code_du_departement)
  select distinct code_insee_de_la_commune, nom_de_la_commune, code_du_departement
  from _temp_fr_en_ips_colleges_ap2022
  where (code_insee_de_la_commune, nom_de_la_commune) not in (select code_insee_de_la_commune, nom_de_la_commune from _commune) ;
  
----------------
--- _academie
delete from _academie;
--- A compléter
insert into _academie (code_academie, lib_academie)
  select distinct code_academie, libelle_academie
  from _temp_fr_en_etablissements_ep
  where code_academie is not null;

-- Nettoyage des noms d'académies : majuscules et sans accents
update _academie
set lib_academie = upper(lib_academie);
update _academie
set lib_academie = replace(lib_academie,'É','E');
update _academie
set lib_academie = replace(lib_academie,'È','E');
update _academie
set lib_academie = replace(lib_academie,'Ô','O');

------------------------
-- Quartier prioritaire
delete from _quartier_prioritaire;

alter table _quartier_prioritaire
  drop constraint quartier_prioritaire_pk cascade;     /**nous avous absolument aucune idée de pourquoi nous sommes obligés de faire ça pour éviter les dupliqués malgré les multiples tentative lors de l'insertion*/

insert into _quartier_prioritaire (code_quartier_prioritaire, nom_quartier_prioritaire)
  select distinct qp_a_proximite, nom_du_qp
  from _temp_fr_en_etablissements_ep
  where qp_a_proximite is not null and qp_a_proximite not in (select code_quartier_prioritaire from _quartier_prioritaire);     /**la dernière contrainte laisse des dupliqué dans la table*/
  
/**nous regardons donc si le code est présent plus d'une fois dans la table et on supprime les doublons*/
delete from _quartier_prioritaire
where code_quartier_prioritaire in (
    select code_quartier_prioritaire
    from _quartier_prioritaire
    group by code_quartier_prioritaire
    having count(*) > 1)
;
  
/**enfin nous réinserons les contraintes de clé primaire et de clé étrangère*/
alter table _quartier_prioritaire
  add constraint quartier_prioritaire_pk primary key (code_quartier_prioritaire);
  
alter table _est_a_proximite_de
  add constraint est_a_proximite_de_fk_qp foreign key(code_quartier_prioritaire) references _quartier_prioritaire(code_quartier_prioritaire);
  

--- A compléter

--- et tout le reste


delete from _type;

insert into _type (code_nature, libelle_nature)
  select distinct code_nature, libelle_nature
  from _temp_fr_en_etablissements_ep
  where code_nature is not null;


delete from _etablissement;

insert into _etablissement (uai, nom_etablissement, secteur, code_postal, lattitude, longitude, code_insee_de_la_commune, nom_de_la_commune, code_academie, code_nature)
  select distinct uai, nom_etablissement, statut_public_prive, code_postal, latitude, longitude, code_commune, upper(nom_commune), code_academie, code_nature
  from _temp_fr_en_etablissements_ep
  where nom_etablissement is not null and code_postal is not null and (code_commune, upper(nom_commune)) in (select code_insee_de_la_commune, nom_de_la_commune from _commune);
  

delete from _annee;

insert into _annee (annee_scolaire)
  select distinct rentree_scolaire
  from _temp_fr_en_college_effectif_niveau_sexe_lv;
  

delete from _classe;

insert into _classe (id_classe) values ('6eme'), ('5eme'), ('4eme'), ('3eme');


delete from _caracteristiques_tout_etablissement;

insert into _caracteristiques_tout_etablissement (uai, annee_scolaire, effectifs)
  select distinct uai, annee_scolaire, nombre_d_eleves
  from _temp_fr_en_etablissements_ep
  join _annee on annee_scolaire = annee_scolaire
  where nombre_d_eleves is not null;


commit;
