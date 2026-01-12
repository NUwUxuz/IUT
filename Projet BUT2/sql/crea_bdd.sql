DROP SCHEMA IF EXISTS pact CASCADE;
CREATE SCHEMA pact;
SET SCHEMA 'pact';

----------- ADRESSE -----------

create table pact._adresse(
  idAdresse serial,
  ville varchar(20) not null,
  numero_voie integer not null,
  voie varchar(100) not null,
  code_postal integer not null,
  complement varchar(30),

  constraint _adresse_pk primary key (idAdresse)
);

----------- IMAGES -----------
create table pact._image(
  idImage serial,
  src_image varchar(60) not null unique,

  constraint _image_pk primary key (idImage)
);

----------- COMPTES -----------

create table pact._compte
(
  idC serial,
  nom varchar(20) not null,
  prenom varchar(20) not null,
  email varchar(50) not null,
  telephone varchar(10) not null,
  mdp varchar(25) not null,
  clefApi varchar(64),
  secretOTP varchar(103),
  
  -- FK
  idAdresse integer not null,
  image_compte integer,

  -- TCHATATOR
  estBan boolean default false,
  estBloque boolean default false,
  dateBlocage timestamp,
  
  constraint _compte_pk 
  primary key (idC),
  
  constraint _compte_fk_adresse foreign key
  (idAdresse) references pact._adresse(idAdresse),

  constraint _compte_fk_image foreign key
  (image_compte) references pact._image(idImage)
);

create table pact._professionnel(

  idC integer,
  codePro integer unique not null, -- need trigger so that this is frozen or see if you can make it an id
  denomination_sociale varchar(30) not null,

  constraint _professionnel_pk primary key (idC),
  constraint _professionnel_fk_inherit foreign key
  (idC) references pact._compte(idC)
);

create table pact._secteur_public(

  idC integer,
    
  constraint _secteur_public_pk primary key (idC),
  constraint _secteur_public_fk_inherit foreign key
  (idC) references pact._professionnel(idC)
);

create table pact._secteur_prive(
  idC integer,
  iban varchar(34),
  bic varchar(11),
  siren varchar(9) not null,
  raison_sociale varchar(30) not null,
    
  constraint _secteur_prive_pk primary key (idC),
  constraint _secteur_prive_fk_inherit foreign key
  (idC) references pact._professionnel(idC)
);

create table pact._membre(

  idC integer,
  pseudo varchar(20) unique not null,-- need trigger so that this is frozen or see if you can make it an id
    
  constraint _membre_pk primary key (idC),
  constraint _membre_fk_inherit foreign key
  (idC) references pact._compte(idC)
);

----------- AVIS -----------
create table pact._avis(

  idA serial,
  note integer,
  titre varchar(30),
  corps varchar(500),
  date_publication date,
  date_visite date,
  contexte varchar(20),
  --/nbr_like
  --/nbr_dislike
  consulte boolean default false,
  blackliste boolean default false,
  date_blackliste date,
  duree_blackliste interval,
  
  constraint _avis_pk primary key (idA),
  
  constraint check_note_min check(0<=note),
  constraint check_note_max check(note <=5)
);

create table pact._photo_avis(
  idImage integer,
  idA integer,

  constraint _photo_avis_pk primary key (idImage, idA),

  constraint _photo_avis_fk_image foreign key
  (idImage) references pact._image(idImage),

  constraint _photo_avis_fk_avis foreign key
  (idA) references pact._avis(idA)

);

----------- TAGS -----------

create table pact._tag(
  idTag serial,
  libelle varchar(20) not null,
  
  constraint _tag_pk primary key (idTag)
);

create table pact._tag_default(
  idTag integer,
  
  constraint _tag_default_pk primary key (idTag),
  constraint _tag_default_fk_inherit foreign key
  (idTag) references pact._tag(idTag)
);

create table pact._tag_resto(
  idTag integer,
  
  constraint _tag_resto_pk primary key (idTag),
  constraint _tag_resto_fk_inherit foreign key
  (idTag) references pact._tag(idTag)
);

----------- Type_offre -----------
create table pact._type_offre(
  idT serial,
  nom_type varchar(20) not null,
  cout_HT float not null,
  cout_TTC float not null,

  constraint _type_offre_pk primary key (idT)
);

----------- Option -----------
create table pact._option(
  idOption serial,
  nom_option varchar(20) not null,
  cout_HT float not null,
  cout_TTC float not null,

  constraint _option_pk primary key (idOption)
);

----------- Offre est ses enfants -----------

create table pact._offre(
  idO serial,
  titre varchar(50),
  resume varchar(250),
  description varchar(1000),
  site_web varchar(200),
  prix_min float,
  --/moy_note
  --/nbrAvis
  --/nbrAvisBlacklistes
  accessibilite varchar(200),
  categorie varchar(30), -- need to be frozen
  date_creation date default now(),
  date_publication date,
  en_ligne boolean default false,
  date_hors_ligne date,
  
  -- FK
  idAdresse integer,
  "type" integer,
  idC integer,

  constraint _offre_pk primary key (idO),

  constraint _offre_fk_type_offre foreign key
  ("type") references _type_offre(idT),
  
  constraint _offre_fk_professionnel foreign key
  (idC) references _professionnel(idC),
  
  constraint _offre_fk_adresse foreign key
  (idAdresse) references pact._adresse(idAdresse)
);

create table pact._activite(
  idO integer,
  duree interval not null,
  age_min integer  not null,
  prestation_incluse varchar(100), -- not null ?
  prestation_excluse varchar(100), -- not null ?
  
  constraint _activite_pk primary key(idO),
  constraint _activite_fk_inherit foreign key
  (idO) references pact._offre(idO)
);

create table pact._tags_activite(
  -- tags liés à une offre d'activité
  idO integer,
  idTag integer,
  
  constraint _tags_activite_pk primary key(idO, idTag),
  constraint _tags_activite_fk_activite foreign key
  (idO) references pact._activite(idO),
  constraint _tags_activite_fk_tag_default foreign key
  (idTag) references pact._tag_default(idTag)
);

create table pact._visite(
  idO integer,
  duree interval not null,
  langues  varchar(30),
  
  constraint _visite_pk primary key(idO),
  constraint _visite_fk_inherit foreign key
  (idO) references pact._offre(idO)
);

create table pact._tags_visite(
  -- tags liés à une offre de visite
  idO integer,
  idTag integer,
  
  constraint _tags_visite_pk primary key(idO, idTag),
  constraint _tags_visite_fk_visite foreign key
  (idO) references _visite(idO),
  constraint _tags_visite_fk_tag_default foreign key
  (idTag) references pact._tag_default(idTag)
);

create table pact._spectacle(
  idO integer,
  duree interval not null,
  capacite integer not null,
  
  constraint _spectacle_pk primary key(idO),
  constraint _spectacle_fk_inherit foreign key
  (idO) references pact._offre(idO)
);

create table pact._tags_spectacle(
  -- tags liés à une offre de spectacle
  idO integer,
  idTag integer,
  
  constraint _tags_spectacle_pk primary key(idO, idTag),
  constraint _tags_spectacle_fk_spectacle foreign key
  (idO) references pact._spectacle(idO),
  constraint _tags_spectacle_fk_tag_default foreign key
  (idTag) references pact._tag_default(idTag)
);


create table pact._parc_d_attraction(
  idO integer,
  nbr_attractions integer not null,
  age_min integer  not null,

  -- FK

  "plan" integer not null,
  
  constraint _parc_d_attraction_pk primary key(idO),
  constraint _parc_d_attraction_fk_inherit foreign key
  (idO) references pact._offre(idO),

  constraint _parc_d_attraction_fk_image foreign key
  ("plan") references pact._image(idImage)
);

create table pact._tags_parc_d_attraction(
  -- tags liés à une offre de parc d'attraction
  idO integer,
  idTag integer,
  
  constraint _tags_parc_d_attraction_pk primary key(idO, idTag),
  constraint _tags_parc_d_attraction_fk_parc_d_attraction foreign key
  (idO) references pact._parc_d_attraction(idO),
  constraint _tags_parc_d_attraction_fk_tag_default foreign key
  (idTag) references pact._tag_default(idTag)
);

create table pact._restauration(
  idO integer,
  gamme_prix varchar(5),
  petit_dejeuner boolean not null,
  brunch boolean not null,
  dejeuner boolean not null,
  diner boolean not null,
  boissons boolean not null,

  -- FK
  carte_restaurant integer not null,
  
  constraint _restauration_pk primary key(idO),
  constraint _restauration_fk_inherit foreign key
  (idO) references pact._offre(idO),

  constraint _restauration_fk_image foreign key
  (carte_restaurant) references pact._image(idImage)
);

create table pact._tags_restauration(
  -- tags liés à une offre de restauration
  idO integer,
  idTag integer,
  
  constraint _tags_restauration_pk primary key(idO, idTag),
  constraint _tags_restauration_fk_restauration foreign key
  (idO) references pact._restauration(idO),
  constraint _tags_restauration_fk_tag_resto foreign key
  (idTag) references pact._tag_resto(idTag)
);

-- HORAIRE RESTO
create table pact._horaire(
  idHoraire serial,
  heureOuverture time not null,
  heureFermeture time not null,
  ouvertWeekend boolean default false,
    
  --FK
  idO integer unique,

  constraint _horaire_pk primary key (idHoraire),
  
  constraint _horaire_fk_restauration foreign key
    (idO) references pact._restauration(idO)
);

create table pact._photo_offre(
  idImage integer,
  idO integer,

  constraint _photo_offre_pk primary key (idImage, idO),

  constraint _photo_offre_fk_image foreign key
  (idImage) references pact._image(idImage),

  constraint _photo_offre_fk_offre foreign key
  (idO) references pact._offre(idO)

);

create table pact._option_offre(
  --table qui gère les options des offres
  idO integer,
  idOption integer,
  date_lancement date, -- if null when putting online ask user to set these
  duree_option interval,

  constraint _option_offre_pk primary key (idO),

  constraint _option_offre_fk_offre foreign key
  (idO) references pact._offre(idO),

  constraint _option_offre_fk_option foreign key
  (idOption) references pact._option(idOption)
);

----------- REGISTRE -----------

create table pact._registre(
  -- enregistre toutes les dates de publication et de mise hors ligne pour ne pas les perdre
  -- registre des perodes où l'offre était en ligne
  idRegistre serial,
  date_publication date not null,
  date_hors_ligne date not null,
  idO integer,

  constraint _registre_pk primary key (idRegistre),

  constraint _registre_fk_offre foreign key
  (idO) references pact._offre(idO)
);

----------- FACTURATION -----------

create table pact._facturation(
  numFacture integer,
  --/prixOffreTTC
  --/prixOffreHT
  --/prixOptionTTC
  --/prixOptionHT
  date_facture date default now(),
  idO integer,

  constraint _facturation_pk primary key (numFacture, idO),

  constraint _facturation_fk_offre foreign key
  (idO) references pact._offre(idO)
);


----------- Exprience ----------- 

create table pact._experience(
  idC integer,
  idO integer,
  idA integer,
  
  constraint _experience_pk primary key(idC, idO, idA),
  
  constraint _experience_fk_membre foreign key
  (idC) references pact._membre(idC),
  
  constraint _experience_fk_offre foreign key
  (idO) references pact._offre(idO),
  
  constraint _experience_fk_avis foreign key
  (idA) references pact._avis(idA)
  
);

----------- Reponse -----------

create table pact._reponse(
  idA integer,
  idC integer, -- id d'un professionnel
  corps varchar(1000),
  
  constraint _reponse_pk primary key(idA),
  
  constraint _reponse_fk_avis foreign key
  (idA) references pact._avis(idA),
  
  constraint _reponse_fk_professionnel foreign key
  (idC) references pact._professionnel(idC)
);

---Signalements---

create table pact._signal_avis(
  idA integer, -- id de l'avis signalé
  idSignaleur integer,

  constraint _signal_avis_pk primary key(idA, idSignaleur),

  constraint _signal_avis_fk_avis foreign key
  (idA) references pact._avis(idA),
  
  constraint _signal_avis_fk_compte foreign key
  (idSignaleur) references pact._compte(idC)
);

create table pact._signal_reponse(
  idA integer, --id de l'avis répondu
  idSignaleur integer,
  -- avec l'id de l'avis on peut ensuite faire une joiture avec la table reponse pour trouver la reponse signalée

  constraint _signal_reponse_pk primary key(idA, idSignaleur),

  constraint _signal_reponse_fk_reponse foreign key
  (idA) references pact._reponse(idA),
  
  constraint _signal_reponse_fk_compte foreign key
  (idSignaleur) references pact._compte(idC)
);


----------- like/dislike -----------

create table pact._recommande(
  idC integer,
  idA integer,
  
  constraint _recommande_pk primary key(idC, idA),
  
  constraint _recommande_fk_membre foreign key
    (idC) references pact._membre(idC)
);

create table pact._recommande_pas(
  idC integer,
  idA integer,
  
  constraint _recommande_pas_pk primary key(idC, idA),
  
  constraint _recommande_pas_fk_membre foreign key
    (idC) references pact._membre(idC)
);

/* xor does not work
create or replace function existe_recommande_pas(idC integer, idA integer)
   returns boolean as $$
declare
  res boolean;
begin
  select exists(select * from recommande_pas rp where rp.idC = idC and rp.idA = idA) into res; 
  RETURN res;
end;
$$ language 'plpgsql';

alter table _recommande add constraint xor_recommande check (existe_recommande_pas(idC, idA));

create or replace function existe_recommande(idC integer, idA integer)
   returns boolean as $$
declare
  res boolean;
begin
  select exists(select * from recommande r where r.idC = idC and r.idA = idA) into res; 
  RETURN res;
end;
$$ language 'plpgsql';

alter table _recommande_pas add constraint xor_recommande_pas check (existe_recommande(idC, idA));
*/
---------------------------------------------
-------------Triggers tables-----------------
---------------------------------------------

-- Compte

create or replace function pact.genererApiKey(idCompte integer)
  returns varchar(64) as $$
declare
  res varchar(64);
  temp varchar(4);
begin
  
  perform setseed(idCompte/100.0);
  res = concat(substr(md5(random()::text), 1, 32),substr(md5(random()::text), 1, 29));
  
  if exists(select * from pact._membre where idC = idCompte) then
    temp='mbr';
  elsif  exists(select * from pact._professionnel where idC = idCompte) then
    temp='pro';
  end if;
  raise notice '%', temp;
  res =  concat(temp,res);
  RETURN res;
end;
$$ language 'plpgsql';


/*DEPRECIATED was used in a trigger but no longer usefull in current implementation*/
create or replace function pact.inserer_compte()
  returns trigger as $$
begin
  new.clefApi = pact.genererApiKey(new.idC);
  update pact._compte set clefApi=new.clefApi where idC=new.idC;
  RETURN NEW;
end;
$$ language 'plpgsql';


-------- This should freeze codePro
create or replace function pact.mis_a_jour_professionnel()
  returns trigger as $$
begin
  if (new.codePro <> old.codePro) then
    raise exception 'Pas de mise à jour codePro';
  end if;  
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_update_professionnel
before update on pact._professionnel
  for each row execute procedure pact.mis_a_jour_professionnel();
  
--This should freeze pseudo
create or replace function pact.mis_a_jour_membre()
  returns trigger as $$
begin
  if (new.pseudo <> old.pseudo) then
    raise exception 'Pas de mise à jour pseudo';
  end if;
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_update_membre
before update on pact._membre
  for each row execute procedure pact.mis_a_jour_membre();


-- gestion du registre
create or replace function pact.mis_a_jour_offre()
  returns trigger as $$
begin
   if new.date_publication <> old.date_publication and old.date_hors_ligne is not null then
     insert into pact._registre(date_publication, date_hors_ligne, idO) values (old.date_publication, old.date_hors_ligne, old.idO);
     new.date_hors_ligne = null;
   end if;
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_mis_a_jour_offre
before update on pact._offre
  for each row execute procedure pact.mis_a_jour_offre();

---------------------------------------------
------------------- Views ------------------- 
---------------------------------------------

-- Membre
create or replace view pact.membre as
  select idC, pseudo, nom, prenom, email, telephone, mdp, clefApi, idAdresse, ville, numero_voie, voie, code_postal, complement from pact._membre natural join pact._compte natural join pact._adresse;
  


create or replace function pact.insert_membre()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;
  insert into pact._compte (nom, prenom, email, telephone, mdp, clefApi, idAdresse) values (new.nom, new.prenom, new.email, new.telephone, new.mdp, new.clefApi, new.idAdresse) returning idC into new.idC;
  insert into pact._membre (idc, pseudo) values (new.idc, new.pseudo);
  /*Clef api doit être fait après insertion car le début dépend de si elle existe dans la table pro ou membre*/
  new.clefApi = pact.genererApiKey(new.idC);
  update pact._compte set clefApi=new.clefApi where idC=new.idC;
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_membre
instead of insert on pact.membre
  for each row execute procedure pact.insert_membre();


create or replace function pact.update_membre()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;

  update pact._compte set nom = new.nom, prenom = new.prenom, email = new.email, telephone = new.telephone, mdp = new.mdp, clefApi = new.clefApi, idAdresse = new.idAdresse where idC = old.idC;
  -- on ne peut pas modifier le pseudo
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_update_membre
instead of update on pact.membre
  for each row execute procedure pact.update_membre();
  

create or replace function pact.delete_membre()
  returns trigger as $$
begin
  update pact._experience set idC = 1 where idC = old.idC;
  delete from pact._membre where idC = old.idC;
  delete from pact._recommande where idC = old.idC;
  delete from pact._recommande_pas where idC = old.idC;
  return OLD;
end;
$$ language 'plpgsql';
  
create or replace trigger tg_delete_membre
instead of delete on pact.membre
  for each row execute procedure pact.delete_membre();
  
-- professionnel
create or replace view pact.professionnel as
  select idC, codePro, denomination_sociale, nom, prenom, email, telephone, mdp, clefApi, idAdresse, ville, numero_voie, voie, code_postal, complement from pact._professionnel natural join pact._compte natural join pact._adresse;
  


create or replace function pact.insert_professionnel()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;
  insert into pact._compte (nom, prenom, email, telephone, mdp, clefApi, idAdresse) values (new.nom, new.prenom, new.email, new.telephone, new.mdp, new.clefApi, new.idAdresse) returning idC into new.idC;
  insert into pact._professionnel (idc, codePro, denomination_sociale) values (new.idc, new.codePro, new.denomination_sociale);
  /*Clef api doit être fait après insertion car le début dépend de si elle existe dans la table pro ou membre*/
  new.clefApi = pact.genererApiKey(new.idC);
  update pact._compte set clefApi=new.clefApi where idC=new.idC;
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_professionnel
instead of insert on pact.professionnel
  for each row execute procedure pact.insert_professionnel();

create or replace function pact.update_professionnel()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;
  update pact._compte set nom = new.nom, prenom = new.prenom, email = new.email, telephone = new.telephone, mdp = new.mdp, clefApi=new.clefApi, idAdresse = new.idAdresse where idC = old.idC;
  update pact._professionnel set denomination_sociale = new.denomination_sociale where idC = old.idC;
  -- on ne peut pas modifier le code pro
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_professionnel
instead of update on pact.professionnel
  for each row execute procedure pact.update_professionnel();

-- professionnel secteur public

create or replace view pact.secteur_public as
  select * from pact._secteur_public natural join pact.professionnel;


create or replace function pact.insert_secteur_public()
  returns trigger as $$
begin
  insert into pact.professionnel (codePro, denomination_sociale, nom, prenom, email, telephone, mdp, clefApi, ville, numero_voie, voie, code_postal, complement) 
    values (new.codePro, new.denomination_sociale, new.nom, new.prenom, new.email, new.telephone, new.mdp, new.clefApi, new.ville, new.numero_voie, new.voie, new.code_postal, new.complement);
  select idC from pact.professionnel where codePro = new.codePro into new.idC;
  insert into pact._secteur_public (idC) values (new.idC);
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_secteur_public
instead of insert on pact.secteur_public
  for each row execute procedure pact.insert_secteur_public();

create or replace function pact.update_secteur_public()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;
  update pact._compte set nom = new.nom, prenom = new.prenom, email = new.email, telephone = new.telephone, mdp = new.mdp, clefApi = new.clefApi, idAdresse = new.idAdresse where idC = old.idC;
  update pact._professionnel set denomination_sociale = new.denomination_sociale where idC = old.idC;
  -- on ne peut pas modifier le code pro
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_update_secteur_public
instead of update on pact.secteur_public
  for each row execute procedure pact.update_secteur_public();


-- professionnel secteur prive

create or replace view pact.secteur_prive as
  select * from pact._secteur_prive natural join pact.professionnel;


create or replace function pact.insert_secteur_prive()
  returns trigger as $$
begin
  insert into pact.professionnel (codePro, denomination_sociale, nom, prenom, email, telephone, mdp, clefApi, ville, numero_voie, voie, code_postal, complement) 
    values (new.codePro, new.denomination_sociale, new.nom, new.prenom, new.email, new.telephone, new.mdp, new.clefApi, new.ville, new.numero_voie, new.voie, new.code_postal, new.complement);
  select idC from pact.professionnel where codePro = new.codePro into new.idC;
  insert into pact._secteur_prive (idC, iban, bic, siren, raison_sociale) values (new.idC, new.iban, new.bic, new.siren, new.raison_sociale);
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_secteur_prive
instead of insert on pact.secteur_prive
  for each row execute procedure pact.insert_secteur_prive();

create or replace function pact.update_secteur_prive()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;
  update pact._compte set nom = new.nom, prenom = new.prenom, email = new.email, telephone = new.telephone, mdp = new.mdp, clefApi = new.clefApi, idAdresse = new.idAdresse where idC = old.idC;
  update pact._professionnel set denomination_sociale = new.denomination_sociale where idC = old.idC;
  update pact._secteur_prive set iban = new.iban, bic = new.bic, raison_sociale = new.raison_sociale where idC = old.idC;
  -- on ne peut pas modifier le code pro
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_update_secteur_prive
instead of update on pact.secteur_prive
  for each row execute procedure pact.update_secteur_prive();
  
--- Tags

-- Tags default

create or replace view pact.tag_default as
  select * from pact._tag_default natural join pact._tag;
  
create or replace function pact.insert_tag_default()
  returns trigger as $$
begin
  insert into pact._tag (libelle) values (new.libelle) returning idTag into new.idTag;
  insert into pact._tag_default (idTag) values (new.idTag);
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_tag_default
instead of insert on pact.tag_default
  for each row execute procedure pact.insert_tag_default();


-- Tags default

create or replace view pact.tag_resto as
  select * from pact._tag_resto natural join pact._tag;
  
create or replace function pact.insert_tag_resto()
  returns trigger as $$
begin
  insert into pact._tag (libelle) values (new.libelle) returning idTag into new.idTag;
  insert into pact._tag_resto (idTag) values (new.idTag);
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_tag_resto
instead of insert on pact.tag_resto
  for each row execute procedure pact.insert_tag_resto();

--- Avis

create or replace function pact.count_likes(idAvis integer)
  returns integer as $$
declare
  comp integer;
begin
  select count(*) from pact._recommande r where r.idA = idAvis into comp;
  RETURN comp;
end;
$$ language 'plpgsql';

create or replace function pact.count_dislikes(idAvis integer)
  returns integer as $$
declare
  comp integer;
begin
  select count(*) from pact._recommande_pas r where r.idA = idAvis into comp;
  RETURN comp;
end;
$$ language 'plpgsql';

create or replace view pact.avis as 
  select idA, note, titre, corps, date_publication, date_visite, contexte, pact.count_likes(idA) as nbr_likes, pact.count_dislikes(idA) as nbr_dislikes, consulte, blackliste from _avis;

create or replace function pact.delete_avis()
  returns trigger as $$
begin
  
  delete from pact._experience
  where idA = OLD.idA;
  
  delete from pact._avis
  where idA = OLD.idA;
 
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_delete_avis
instead of delete on pact.avis
  for each row execute procedure pact.delete_avis();

--- option offre
create or replace view option_offre as
 select * from _option_offre natural join _option;

--- Offres

create or replace function pact.count_avis(idOffre integer)
  returns integer as $$
declare
  comp integer;
begin
  select count(*) from pact._experience e inner join pact.avis a on e.idA = a.idA where e.idO = idOffre and a.blackliste = False into comp;
  RETURN comp;
end;
$$ language 'plpgsql';

create or replace function pact.nbrAvisBlacklistes(idOffre integer)
  returns int as $$
declare
  res int;
begin
  select COUNT(*) from pact._avis a inner join pact._experience e on a.ida = e.ida where e.idO = idOffre and a.blackliste = True into res;
  RETURN res;
end;
$$ language 'plpgsql';

create or replace function pact.moy_notes(idOffre integer)
  returns float as $$
declare
  comp float;
begin
  select avg(a.note) from pact._experience e inner join pact.avis a on e.idA = a.idA where e.idO = idOffre and a.blackliste = False into comp;
  if (comp is null) then
    comp = 0;
  end if;
  RETURN comp;
end;
$$ language 'plpgsql';

create or replace view pact.offre as
  select  o.idO, o.titre, resume, "type", op.idOption, op.nom_option as option, nom_type, t.cout_HT, t.cout_TTC, description, site_web, prix_min, pact.moy_notes(o.idO) as moy_note, pact.count_avis(o.idO) as nbrAvis, pact.nbrAvisBlacklistes(o.idO) as nbrAvisBlacklistes, 
  accessibilite, categorie, date_creation, date_publication, date_hors_ligne, o.idAdresse, ville, numero_voie, voie, code_postal, complement, en_ligne, idC from pact._offre o 
  inner join pact._type_offre t on "type" = idT
  inner join pact._adresse a on a.idAdresse = o.idAdresse
  left outer join option_offre op on op.idO = o.idO;
  
create or replace function pact.insert_offre()
  returns trigger as $$
begin

 -- test adresse
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;

  insert into pact._offre (titre, resume, "type", description, site_web, prix_min, accessibilite, categorie, date_creation, date_publication, date_hors_ligne, idAdresse, idC)
  values (new.titre, new.resume, new."type", new.description, new.site_web, new.prix_min, new.accessibilite, new.categorie, new.date_creation, new.date_publication, new.date_hors_ligne, new.idAdresse, new.idC);
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_offre
instead of insert on pact.offre
  for each row execute procedure pact.insert_offre();



-- activite

--insert
create or replace view pact.activite as
  select idO, titre, resume, "type", option, description, site_web, prix_min, accessibilite, categorie, date_creation, date_publication, date_hors_ligne, duree, age_min, prestation_incluse, prestation_excluse, idAdresse, ville, numero_voie, voie, code_postal, complement, en_ligne, idC
   from pact._activite natural join pact.offre;
  
  create or replace function pact.insert_activite()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;
  insert into pact._offre (titre, resume, "type", description, site_web, prix_min, accessibilite, categorie, date_creation, date_publication, date_hors_ligne, idAdresse, idC) 
  values (new.titre, new.resume, new."type", new.description, new.site_web, new.prix_min, new.accessibilite, 'Activite', new.date_creation, new.date_publication, new.date_hors_ligne, new.idAdresse, new.idC) returning idO into new.idO;
  
  insert into pact._activite (idO, duree, age_min, prestation_incluse, prestation_excluse) values (new.idO, new.duree, new.age_min, new.prestation_incluse, new.prestation_excluse);
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_activite
instead of insert on pact.activite
  for each row execute procedure pact.insert_activite();

--update
create or replace function pact.update_activite()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;

  update pact._offre set titre = new.titre, resume = new.resume, "type" = new."type", description = new.description, site_web = new.site_web,  prix_min = new.prix_min,
   accessibilite = new.accessibilite, date_publication = new.date_publication, date_hors_ligne = new.date_hors_ligne, idAdresse = new.idAdresse where idO = old.idO;
  
  update pact._activite set duree = new.duree, age_min = new.age_min, prestation_incluse = new.prestation_incluse, prestation_excluse = new.prestation_excluse where idO = old.idO;
  
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_update_activite
instead of update on pact.activite
  for each row execute procedure pact.update_activite();


--  

-- visite
--insert
create or replace view pact.visite as
  select idO, titre, resume, "type", option, description, site_web, prix_min, accessibilite, categorie, date_creation, date_publication, date_hors_ligne, duree, langues, idAdresse, ville, numero_voie, voie, code_postal, complement, en_ligne, idC
   from _visite natural join offre;
  
create or replace function pact.insert_visite()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;
  
  insert into pact._offre (titre, resume, "type", description, site_web, prix_min, accessibilite, categorie, date_creation, date_publication, date_hors_ligne, idAdresse, idC) 
  values (new.titre, new.resume, new."type", new.description, new.site_web, new.prix_min, new.accessibilite, 'Visite', new.date_creation, new.date_publication, new.date_hors_ligne, new.idAdresse, new.idC) returning idO into new.idO;
  
insert into pact._visite (idO, duree, langues) values (new.idO, new.duree, new.langues);
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_visite
instead of insert on pact.visite
  for each row execute procedure pact.insert_visite();

--update
create or replace function pact.update_visite()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;

  update pact._offre set titre = new.titre, resume = new.resume, "type" = new."type", description = new.description, site_web = new.site_web, prix_min = new.prix_min,
   accessibilite = new.accessibilite, date_publication = new.date_publication, date_hors_ligne = new.date_hors_ligne, idAdresse = new.idAdresse where idO = old.idO;
  
  update pact._visite set duree = new.duree, langues = new.langues where idO = old.idO;
  
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_update_visite
instead of update on pact.visite
  for each row execute procedure pact.update_visite();
  
-- spectacle
--insert
create or replace view pact.spectacle as
  select idO, titre, resume, "type", option, description, site_web, prix_min, accessibilite, categorie, date_creation, date_publication, date_hors_ligne, duree, capacite, idAdresse, ville, numero_voie, voie, code_postal, complement, en_ligne, idC
   from pact._spectacle natural join pact.offre;
  
create or replace function pact.insert_spectacle()
  returns trigger as $$
begin

  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;
  
  insert into pact._offre (titre, resume, "type", description, site_web, prix_min, accessibilite, categorie, date_creation, date_publication, date_hors_ligne, idAdresse, idC) 
  values (new.titre, new.resume, new."type", new.description, new.site_web, new.prix_min, new.accessibilite, 'Spectacle', new.date_creation, new.date_publication, new.date_hors_ligne, new.idAdresse, new.idC) returning idO into new.idO;
  
  insert into pact._spectacle (idO, duree, capacite) values (new.idO, new.duree, new.capacite);
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_spectacle
instead of insert on pact.spectacle
  for each row execute procedure pact.insert_spectacle();

--update
create or replace function pact.update_spectacle()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;

  update pact._offre set titre = new.titre, resume = new.resume, "type" = new."type", description = new.description, site_web = new.site_web, prix_min = new.prix_min,
   accessibilite = new.accessibilite, date_publication = new.date_publication, date_hors_ligne = new.date_hors_ligne, idAdresse = new.idAdresse where idO = old.idO;
  
  update pact._spectacle set duree = new.duree, capacite = new.capacite where idO = old.idO;
  
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_update_spectacle
instead of update on pact.spectacle
  for each row execute procedure pact.update_spectacle();

-- parc_d_attraction
--insert
create or replace view pact.parc_d_attraction as
  select idO, titre, resume, "type", option, description, site_web, prix_min, accessibilite, categorie, date_creation, date_publication, date_hors_ligne, "plan", src_image, nbr_attractions, age_min, idAdresse, ville, numero_voie, voie, code_postal, complement, en_ligne, idC
   from pact._parc_d_attraction 
   natural join pact.offre
   inner join pact._image on "plan"=idImage;
  
create or replace function pact.insert_parc_d_attraction()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;
  
  insert into pact._offre (titre, resume, "type", description, site_web, prix_min, accessibilite, categorie, date_creation, date_publication, date_hors_ligne, idAdresse, idC) 
  values (new.titre, new.resume, new."type", new.description, new.site_web, new.prix_min, new.accessibilite, 'Parc d''attraction', new.date_creation, new.date_publication, new.date_hors_ligne, new.idAdresse, new.idC) returning idO into new.idO;
  

  insert into pact._image(src_image) values (new.src_image) returning idImage into new."plan";
  insert into pact._parc_d_attraction (idO, "plan", nbr_attractions, age_min) values (new.idO, new."plan", new.nbr_attractions, new.age_min);
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_parc_d_attraction
instead of insert on pact.parc_d_attraction
  for each row execute procedure pact.insert_parc_d_attraction();
--update
create or replace function pact.update_parc_d_attraction()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;

  update pact._offre set titre = new.titre, resume = new.resume, "type" = new."type", description = new.description, site_web = new.site_web, prix_min = new.prix_min,
   accessibilite = new.accessibilite, date_publication = new.date_publication, date_hors_ligne = new.date_hors_ligne, idAdresse = new.idAdresse where idO = old.idO;
  
  update pact._image set src_image = new.src_image where idImage = old."plan";
  update pact._parc_d_attraction set "plan" = old."plan", nbr_attractions = new.nbr_attractions, age_min = new.age_min  where idO = old.idO;
  
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_update_parc_d_attraction
instead of update on pact.parc_d_attraction
  for each row execute procedure pact.update_parc_d_attraction();

-- restauration
--insert
create or replace view pact.restauration as
  select idO, titre, resume, "type", option, description, site_web, prix_min, accessibilite, categorie, date_creation, date_publication, date_hors_ligne, 
  carte_restaurant, src_image, gamme_prix, petit_dejeuner, brunch, dejeuner, diner, boissons, idAdresse, ville, numero_voie, voie, code_postal, complement, en_ligne, idC
   from pact._restauration 
    natural join pact.offre
    inner join pact._image on carte_restaurant=idImage;
  
create or replace function pact.insert_restauration()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;
  
  insert into pact._offre (titre, resume, "type", description, site_web, prix_min, accessibilite, categorie, date_creation, date_publication, date_hors_ligne, idAdresse, idC) 
  values (new.titre, new.resume, new."type", new.description, new.site_web, new.prix_min, new.accessibilite, 'Restauration', new.date_creation, new.date_publication, new.date_hors_ligne, new.idAdresse, new.idC) returning idO into new.idO;
  
  insert into pact._image(src_image) values (new.src_image) returning idImage into new.carte_restaurant;
  insert into pact._restauration (idO, carte_restaurant, gamme_prix, petit_dejeuner, brunch, dejeuner, diner, boissons) 
  values (new.idO, new.carte_restaurant, new.gamme_prix, new.petit_dejeuner, new.brunch, new.dejeuner, new.diner, new.boissons);
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_restauration
instead of insert on pact.restauration
  for each row execute procedure pact.insert_restauration();

--update
create or replace function pact.update_restauration()
  returns trigger as $$
begin
  if (select not exists(select * from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement)) then
    insert into pact._adresse (ville, numero_voie, voie, code_postal, complement) values (new.ville, new.numero_voie, new.voie, new.code_postal, new.complement) returning idAdresse into new.idAdresse;
  else
    select idAdresse from pact._adresse where ville = new.ville and numero_voie = new.numero_voie and voie = new.voie and code_postal = new.code_postal and complement = new.complement into new.idAdresse;
  end if;

  update pact._offre set titre = new.titre, resume = new.resume, "type" = new."type", description = new.description, site_web = new.site_web, prix_min = new.prix_min,
   accessibilite = new.accessibilite, date_publication = new.date_publication, date_hors_ligne = new.date_hors_ligne, idAdresse = new.idAdresse where idO = old.idO;
  
  update pact._image set src_image = new.src_image where idImage = old.carte_restaurant;
  update pact._restauration set carte_restaurant = old.carte_restaurant, 
    gamme_prix = new.gamme_prix, 
    petit_dejeuner = new.petit_dejeuner, 
    brunch = new.brunch, 
    dejeuner = new.dejeuner, 
    diner = new.diner, 
    boissons = new.boissons
  where idO = old.idO;
  
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_update_restauration
instead of update on pact.restauration
  for each row execute procedure pact.update_restauration();

--- experience ---

create or replace view pact.experience as
  select e.idC, pseudo, e.idA, avis.titre as titre_avis, note, corps, avis.date_publication, date_visite, contexte, e.idO, offre.titre as titre_offre from _experience e
  inner join membre on e.idC = membre.idC 
  inner join avis on e.idA = avis.idA
  inner join offre on e.idO = offre.idO;


create or replace function pact.insert_experience()
  returns trigger as $$
begin
  insert into pact.avis (note, titre, corps, date_publication, date_visite, contexte)
  values (new.note, new.titre_avis, new.corps, now(), new.date_visite, new.contexte) returning idA into new.idA;
  insert into pact._experience(idA, idC, idO) values (new.idA, new.idC, new.idO);
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_experience
instead of insert on pact.experience
  for each row execute procedure pact.insert_experience();
  
--- photo_offre ---

create or replace view pact.photo_offre as
  select * from _photo_offre natural join _image;
  
--insert
create or replace function pact.insert_photo_offre()
  returns trigger as $$
begin
  insert into pact._image (src_image) values (new.src_image) returning idImage into new.idImage;
  insert into pact._photo_offre (idImage, idO) values (new.idImage, new.idO);
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_insert_photo_offre
instead of insert on pact.photo_offre
  for each row execute procedure pact.insert_photo_offre();

--delete
create or replace function pact.delete_photo_offre()
  returns trigger as $$
declare
  idIm integer;
begin
  select idImage from pact.photo_offre where src_image = old.src_image into idIm;

  delete from pact._photo_offre where idImage = idIm;

  delete from pact._image where idImage = idIm;
  
  RETURN OLD;
end;
$$ language 'plpgsql';

create or replace trigger tg_delete_photo_offre
instead of delete on pact.photo_offre
  for each row execute procedure pact.delete_photo_offre();

--- facturation ---

/*
  Fonction compte le nombre de jours dans le registre pour un mois
  date debut est le premier jour d'un mois donné (la date de facture)
*/
create or replace function pact.nbrJoursRegistre(idOffre integer, date_debut date)
  returns integer as $$
declare
  comp integer;
  date_fin date;
  temprow record;
  n integer;
begin
  comp = 0;
  date_fin = date_trunc('month'::text, date_debut + '1 mon'::interval);
  
  FOR temprow IN
    select * from pact._registre where ido =  idOffre
  loop
    --temprow.date_publication, temprow.date_hors_ligne
    if (temprow.date_hors_ligne > date_fin) then
      temprow.date_hors_ligne = date_fin;
    end if;
    
    if (temprow.date_publication < date_debut) then
      temprow.date_publication = date_debut;
    end if;
  
    select(temprow.date_hors_ligne - temprow.date_publication) as days into n;
    -- dans le cas où la date de mise hors ligne ne change pas mais elle n'est pas du même mois donc temprow.date_hors_ligne < temprow.date_publication
    if (n < 0) then
      n = 0;
    end if;
    comp = comp + n;

  end loop;

  RETURN comp;
end;
$$ language 'plpgsql';

/*
Compte le nombre de jours qu'une facture est active pour le mois
*/
create or replace function pact.nbrJours(idOffre integer, date_fin date, idFacture integer)
  returns integer as $$
declare
  comp float;
  date_debut date;
  date_offre_hors_ligne date;
  date_facture date;
  date_emmission_facture date;
  n integer;
begin
  n = 0;
  select f.date_facture from pact._facturation f where f.numFacture = idFacture into date_facture;
  date_emmission_facture = date_trunc('month'::text, date_facture + '1 mon'::interval);
  
  -- si la date d'emmission est plus vielle que la date donnée donc elle devient la date de fin
  -- celà évite qu'il n'y ai plus de jours que dans le mois comptés
  
  if date_emmission_facture < date_fin then
    date_fin = date_emmission_facture;
   end if;
  
  select o.date_publication from pact.offre o where o.idO = idOffre into date_debut;
  
  -- voir si la date de publication est plus vielle que la date de facture
  if date_debut < date_trunc('month', date_facture) then
    date_debut = date_trunc('month', date_facture);
   end if;

  if (date_emmission_facture > date_debut) then
    select o.date_hors_ligne from pact.offre o where o.idO = idOffre into date_offre_hors_ligne;
    
    -- try date truncate with month if lasts over a month
    if (date_offre_hors_ligne is null) OR (date_offre_hors_ligne > date_fin) then
      select(date_fin - date_debut) as days into n;
    else
      select(date_offre_hors_ligne - date_debut) as days into n;
      -- corrige si la date de mise hors ligne est plus vielle que la date de debut
      if n<0 then
        n = 0;
      end if;
    end if;
  end if;
  n = n + (select pact.nbrJoursRegistre(idOffre, date_facture));
  RETURN n;
end;
$$ language 'plpgsql';

/*
  Cette fonction calcule le cout TTC d'une offre pour une certaine durée 
  Le nombre de jours entre la date de publication et la date donnée dans la plupart des cas, 
  sauf si la date de mise hors ligne de l'offre est plus vielle avant.
*/

create or replace function pact.prix_offre_TTC(idOffre integer, date_fin date, idFacture integer)
  returns float as $$
declare
  cout float;
  comp float;
  n integer;
begin
  comp = 0;
  select o.cout_TTC from pact.offre o where o.idO = idOffre into cout;
  select pact.nbrJours(idOffre, date_fin, idFacture) into n;
  for counter in 1..n loop
    comp = comp + cout;
  end loop;
  
  RETURN comp;
end;
$$ language 'plpgsql';

/*
DEPRECIATED : compte le nombre d'offres pour une facture (facture à plusieurs offres)
*/
create or replace function pact.nombre_offre_facture(idCompte integer, idFacture integer)
  returns integer as $$
declare
  comp integer;
begin
  select count(*) from pact.offre o inner join pact._facturation f  on f.idC = o.idC where o.idC = idCompte and f.numFacture = idFacture and 
    EXTRACT(MONTH FROM f.date_facture) = EXTRACT(MONTH FROM o.date_publication) 
    and EXTRACT(YEAR FROM f.date_facture) = EXTRACT(YEAR FROM o.date_publication) into comp;
  RETURN comp;
end;
$$ language 'plpgsql';

/*
  Cette fonction calcule le cout HT d'une offre pour une certaine durée
  Le nombre de jours entre la date de publication et la date donnée dans la plupart des cas, 
  sauf si la date de mise hors ligne de l'offre est plus vielle avant.
*/
create or replace function pact.prix_offre_HT(idOffre integer, date_fin date, idFacture integer)
  returns float as $$
declare
  cout float;
  comp float;
  n integer;
begin
  comp = 0;
  select o.cout_HT from pact.offre o where o.idO = idOffre into cout;
  select pact.nbrJours(idOffre, date_fin, idFacture) into n;
  for counter in 1..n loop
    comp = comp + cout;
  end loop;
  
  RETURN round(CAST(comp as numeric),2);
end;
$$ language 'plpgsql';


/*
  DEPRECIATED : pour facture à plusieur offres
  Cette fonction calcule le prix TTC de la facture pour le mois et l'année, ne prend pas en compte les options 
*/
create or replace function pact.prix_total_TTC(idCompte integer, idFacture integer)
  returns float as $$
declare
  comp float;
begin

  select sum(pact.prix_offre_TTC(idCompte, o.idO, now()::date)) from pact.offre o inner join pact._facturation f on f.idC = o.idC where o.idC = idCompte and f.numFacture = idFacture and 
    EXTRACT(MONTH FROM f.date_facture) = EXTRACT(MONTH FROM o.date_publication)
    and EXTRACT(YEAR FROM f.date_facture) = EXTRACT(YEAR FROM o.date_publication) into comp;
  if (comp is null) then
    comp = 0;
  end if;

  RETURN comp;
end;
$$ language 'plpgsql';


/*
  DEPRECIATED : pour facture à plusieur offres
  Cette fonction calcule le prix HT de la facture pour le mois et l'année, ne prend pas en compte les options
*/
create or replace function pact.prix_total_HT(idCompte integer, idFacture integer)
  returns float as $$
declare
  comp float;
begin

  select sum(pact.prix_offre_HT(idCompte, o.idO, now()::date)) from pact.offre o inner join pact._facturation f on f.idC = o.idC where o.idC = idCompte and f.numFacture = idFacture and 
    EXTRACT(MONTH FROM f.date_facture) = EXTRACT(MONTH FROM o.date_publication)
    and EXTRACT(YEAR FROM f.date_facture) = EXTRACT(YEAR FROM o.date_publication) into comp;
  if (comp is null) then
    comp = 0;
  end if;

  RETURN round(CAST(comp as numeric),2);
end;
$$ language 'plpgsql';

--nombre semaines pour option offre active
create or replace function pact.nbSemaines(idOffre integer, idFacture integer)
  returns integer as $$
declare
  duree interval;
  comp float;
  days integer;
  res float;
  date_lancement date;
  date_facture date;
begin
  res = 0;
  select f.date_facture from pact._facturation f where f.numFacture = idFacture into date_facture;
  select o.date_lancement from pact.option_offre o where o.idO = idOffre into date_lancement;
  if (EXTRACT(MONTH FROM date_facture) = EXTRACT(MONTH FROM date_lancement) and EXTRACT(YEAR FROM date_facture) = EXTRACT(YEAR FROM date_lancement)) then
    if (current_date > date_lancement and date_lancement is not null) then
      select o.duree_option from pact.option_offre o where o.idO = idOffre into duree;
      select EXTRACT(DAY FROM duree) into days;
      res = days/7;
    end if;
  end if;
  RETURN res;
end;
$$ language 'plpgsql';

/*
  Fonction calcule le prix TTC d'une option pour une offre donnée,
  si la date de lancement n'a pas étée dépasée, le prix est 0
  si la date de lancement et la date de la facture ne sont pas du mêle mois et année, le prix de l'option est 0
*/
create or replace function pact.prix_option_offre_TTC(idOffre integer, idFacture integer)
  returns float as $$
declare
  cout float;
  weeks integer;
  res float;
begin
  select pact.nbSemaines(idOffre, idFacture) into weeks;
  select o.cout_TTC from pact.option_offre o where o.idO = idOffre into cout;
  res = weeks * cout;
  if res is null then
     res = 0;
  end if;
  RETURN res;
end;
$$ language 'plpgsql';


/*
  Fonction calcule le prix HT d'une option pour une offre donnée,
  si la date de lancement n'a pas étée dépasée, le prix est 0
  si la date de lancement et la date de la facture ne sont pas du mêle mois et année, le prix de l'option est 0
*/
create or replace function pact.prix_option_offre_HT(idOffre integer, idFacture integer)
  returns float as $$
declare
  cout float;
  duree interval;
  comp float;
  days integer;
  res float;
  date_lancement date;
  date_facture date;
begin
  res = 0;
  select f.date_facture from pact._facturation f where f.numFacture = idFacture into date_facture;
  select o.date_lancement from pact.option_offre o where o.idO = idOffre into date_lancement;
  if (EXTRACT(MONTH FROM date_facture) = EXTRACT(MONTH FROM date_lancement) and EXTRACT(YEAR FROM date_facture) = EXTRACT(YEAR FROM date_lancement)) then
    if (current_date > date_lancement and date_lancement is not null) then
      select o.cout_HT from pact.option_offre o where o.idO = idOffre into cout;
      select o.duree_option from pact.option_offre o where o.idO = idOffre into duree;
      select EXTRACT(DAY FROM duree) into days;
      res = days/7 * cout;
    end if;
  end if;
  RETURN res;
end;
$$ language 'plpgsql';


create or replace view pact.facturation as 
  select numfacture, date_facture, date_trunc('month', date_facture + interval '1 month') as date_emmission, pact.prix_offre_TTC(f.idO, now()::date, numfacture) as prix_offre_ttc, pact.prix_offre_HT(f.ido, now()::date, numfacture) as prix_offre_ht, 
  nbrJours(f.idO, now()::date, numfacture) ,pact.prix_option_offre_TTC(f.idO, numfacture) as  prix_option_ttc, pact.prix_option_offre_HT(f.idO, numfacture) as  prix_option_ht, pact.nbSemaines(f.idO, numfacture),
  f.idO, idC from pact._facturation f
  inner join pact.offre o on o.idO = f.idO;

/*
  Fonction pour voir si une offre a une facture pour le mois présent
*/
create or replace function pact.hasFacture(idOffre integer)
  returns boolean as $$
declare
  res boolean;
begin
  res = false;
  if (exists (select * from pact.offre o inner join pact._facturation f  on f.idO = o.idO where f.idO = idOffre 
  and EXTRACT(MONTH FROM f.date_facture) = EXTRACT(MONTH FROM now())and EXTRACT(YEAR FROM f.date_facture) = EXTRACT(YEAR FROM now()))) then
     res = true;
    end if;
  RETURN res;
end;
$$ language 'plpgsql';

/*
  Fonction pour voir si une offre a une facture pour le mois donné
*/
create or replace function pact.hasFacture(idOffre integer, mois date)
  returns boolean as $$
declare
  res boolean;
begin
  res = false;
  if (exists (select * from pact.offre o inner join pact._facturation f  on f.idO = o.idO where f.idO = idOffre 
  and EXTRACT(MONTH FROM f.date_facture) = EXTRACT(MONTH FROM mois)and EXTRACT(YEAR FROM f.date_facture) = EXTRACT(YEAR FROM mois))) then
     res = true;
    end if;
  RETURN res;
end;
$$ language 'plpgsql';

/*
  Génère numero de facture d'une offre
*/
create or replace function pact.genererNumF(idOffre integer)
  returns integer as $$
declare
  id varchar(10);
  nFact integer;
begin
  if length(max(idOffre)::text) < 2 then
        id = concat('0', idOffre::text);
      else
        id = idOffre::text;
      end if;
      select substring(concat(floor((extract(epoch from now())+ random())*100),id),10) into nFact;
  RETURN nFact;
end;
$$ language 'plpgsql';

/*
  Creer toutes les factures
  revoie true si au moins une facture est crée, false sinon
*/
create or replace function pact.creerFactures(idOffre integer)
  returns boolean as $$
declare
  i date;
  res boolean;
  date_debut date;
  query text;
  temprow record;
begin
  res = false;
  
  select o.date_publication from pact.offre o where o.idO = idOffre into date_debut;
  
  for i in select date_trunc('month', current_date) - (dates*interval '1 month')
      from generate_series(0,(extract(year from age(now(), date_debut)) * 12 + extract(month from age(now(), date_debut))),1) dates
   loop
   
    if not (select pact.hasFacture(idOffre, i)) then
      res = true;
      insert into pact._facturation(numFacture, idO, date_facture) values (pact.genererNumF(idOffre), idOffre, i);
    end if;
   end loop;
   --TODO Test this
   for temprow in
    select * from pact._registre where idO  = idOffre
   loop
      for i in select date_trunc('month', current_date) - (dates*interval '1 month')
        from generate_series(0,(extract(year from age(now(), temprow.date_publication)) * 12 + extract(month from age(now(), temprow.date_publication))),1) dates
      loop
        if not (select pact.hasFacture(idOffre, i)) then
          res = true;
          insert into pact._facturation(numFacture, idO, date_facture) values (pact.genererNumF(idOffre), idOffre, i);
        end if;
      end loop;    
   end loop;
  RETURN res;
end;
$$ language 'plpgsql';

/*
Verifie si compte a bien saisit ses coords bancaires
*/

create or replace function pact.hasBank(idCompte integer)
  returns boolean as $$
declare
  res boolean;
begin
  res = false;
  if exists(select * from _secteur_prive where iban is not null and bic is not null and idC = idCompte) then
     res = true;
   end if;
  RETURN res;
end;
$$ language 'plpgsql';


------ TCHATATOR --------


create table _discussion(
  idDiscussion serial,
  idClient integer,
  idPro integer,

  estBloque boolean default false,
  dateBlocage timestamp,
  
  constraint _discussion_pk primary key (idDiscussion),
  constraint _discussion_fk_membre foreign key
  (idClient) references pact._membre(idC),
  constraint _discussion_fk_professionnel foreign key
  (idPro) references pact._professionnel(idC)
);


create table _message(
  idMessage serial,
  idDiscussion integer,
  dateMessage timestamp default NOW(),
  dateModif timestamp default null,
  recu boolean default false,
  supprime boolean default false,
  texte varchar(1000),
  
  --FK
  idEmmeteur integer not null,
  idReceveur integer not null,
  
  constraint _message_pk primary key (idMessage),
  
  constraint _message_fk_discussion foreign key
  (idDiscussion) references pact._discussion(idDiscussion),
  
  constraint _message_fk_compte_receveur foreign key
  (idReceveur) references pact._compte(idC),
  constraint _message_fk_compte_emmeteur foreign key
  (idEmmeteur) references pact._compte(idC)
);


------ FONCTIONS ET TRIGGERS TCHATATOR -------


/*
Génère les clef API des compte memebres
*/
create or replace function pact.genererApiKey(idCompte integer)
  returns varchar(64) as $$
declare
  res varchar(64);
  temp varchar(4);
begin
  
  perform setseed(idCompte/100.0);
  res = concat(substr(md5(random()::text), 1, 32),substr(md5(random()::text), 1, 29));
  
  if exists(select * from pact._membre where idC = idCompte) then
    temp='mbr';
  elsif  exists(select * from pact._professionnel where idC = idCompte) then
    temp='pro';
  end if;
  raise notice '%', temp;
  res =  concat(temp,res);
  RETURN res;
end;
$$ language 'plpgsql';

create or replace function pact.inserer_compte()
  returns trigger as $$
begin
  new.clefApi = pact.genererApiKey(new.idC);
  update pact._compte set clefApi=new.clefApi where idC=new.idC;
  RETURN NEW;
end;
$$ language 'plpgsql';


/*Modifie la date de dernière modification*/

create or replace function pact.mis_a_jour_message()
  returns trigger as $$
begin
  if (old.texte != new.texte) then
    new.dateModif = NOW();
  end if;
  RETURN NEW;
end;
$$ language 'plpgsql';

create or replace trigger tg_update_message
before update on pact._message
  for each row execute procedure pact.mis_a_jour_message();


-- fait la gestion des blocages pour un utilisateur
create or replace function pact.gestionBlocage(idCompte int) 
  returns boolean as $$
declare
  f record;
  res boolean;
  dateBloc timestamp;
begin
    res = false;
    for f in select idDiscussion, dateBlocage from pact._discussion where idClient = idCompte and estBloque=true loop
      if (f.dateBlocage <= NOW() - INTERVAL '24 hours') then
        res = true;
        update pact._discussion set estBloque=false, dateBlocage = null where idDiscussion = f.idDiscussion;
      end if;
    end loop;
    
    select dateBlocage from pact._compte where idC = idCompte into dateBloc;
    if (dateBloc <= NOW() - INTERVAL '24 hours') then
      update pact._compte set estBloque=false, dateBlocage = null where idC = idCompte;
      res = true;
    end if;

    RETURN res;
end;
$$ language plpgsql;

------ PREVISIONNEL ------


create or replace function pact.nbrJoursPrevisionnel(idOffre integer)
  returns integer as $$
declare
  comp float;
  date_debut date;
  date_fin date;
  n integer;
begin
  n = 0;
  date_debut = now();
  date_fin = date_trunc('month'::text, date_debut + '1 mon'::interval);
  raise notice '%', date_fin;
  select(date_fin - date_debut) as days into n;
  RETURN n;
end;
$$ language 'plpgsql';

create or replace function pact.prix_previsionnel_offre_TTC(idOffre integer)
  returns float as $$
declare
  cout float;
  comp float;
  n integer;
begin
  comp = 0;
  select o.cout_TTC from pact.offre o where o.idO = idOffre into cout;
  select pact.nbrJoursPrevisionnel(idOffre) into n;
  for counter in 1..n loop
    comp = comp + cout;
  end loop;
  
  RETURN comp;
end;
$$ language 'plpgsql';

create or replace function pact.prix_previsionnel_offre_HT(idOffre integer)
  returns float as $$
declare
  cout float;
  comp float;
  n integer;
begin
  comp = 0;
  select o.cout_HT from pact.offre o where o.idO = idOffre into cout;
  select pact.nbrJoursPrevisionnel(idOffre) into n;
  for counter in 1..n loop
    comp = comp + cout;
  end loop;
  
  RETURN round(CAST(comp as numeric),2);
end;
$$ language 'plpgsql';

create or replace function pact.toStringDate(idAdr integer)
  returns varchar as $$
declare
  adresse record;
  res varchar;
begin
  res ='';
  select numero_voie, voie, ville, code_postal from pact._adresse a where a.idadresse = idAdr into adresse;
  res = CONCAT(adresse.numero_voie, ' ', adresse.voie, ' ', adresse.ville, ' ', adresse.code_postal);
  RETURN res;
end;
$$ language 'plpgsql';

/*
  Géerer avis blacklistées
  Fonction gère les blacklist temporaires
*/
create or replace function pact.geererBlacklist()
  returns boolean as $$
declare
  i record;
  res boolean;
begin
  res = false;
  
  for i in select ida, blackliste, date_blackliste, duree_blackliste from pact._avis
    loop
    if (i.blackliste) then
      res = true;
      update pact._avis set blackliste = false, duree_blackliste = null where ida = i.ida and i.date_blackliste + i.duree_blackliste < now();
    end if;
  end loop;
  RETURN res;
end;
$$ language 'plpgsql';