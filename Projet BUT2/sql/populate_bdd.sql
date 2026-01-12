SET SCHEMA 'pact';


-- MEMBRES --
insert into pact.membre (pseudo, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values ('Anonyme', 'Ano', 'nyme', 'anonyme@gmail.com', '0123456789', 'NeTrouverPasCeMotDePasse', 'Ici', 1, 'rue de la rue', 22222, '');
insert into pact.membre (pseudo, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values ('Jonnnie', 'Jon', 'Halford', 'jon.hal@gmail.com', '0651101267', 'skjosdkkvd', 'Lannion', 4, 'place du marchal', 22300, '');
insert into pact.membre (pseudo, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values ('Zirconium', 'Prigent', 'Richard', 'richard.prigent@wanadoo.fr', '0758124278', 'N5W7mOliROdlXYtRep', 'Saint-Brieuc', 11, 'rue de la gare', 22000, '');
insert into pact.membre (pseudo, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values ('HolyCannolee', 'La Vedette', 'Hugo', 'hugo.lavedette@orange', '0652637537', 'hugolemeilleur!', 'Guingamp', 5, 'boulevard clémenceau', 22200, '');
insert into pact.membre (pseudo, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values ('Kabibabie', 'Aquecouccou', 'Lillie', 'kabibaie@gmail.com', '0772368545', 'IDontLikeMakingPasswords', 'Lannion', 1, 'rue édouard branly', 22300, '');
insert into pact.membre (pseudo, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values ('Aberrington', 'Lacèsvash', 'Guillaume', 'guillaume.lacesvash@wanadoo.fr', '0687783861', 'g7YZk?D:2*q2', 'Saint-Brieuc', 7, 'rue piet mondrian', 22000, '');
insert into pact.membre (pseudo, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values ('IcyTerra', 'Chram', 'Ambre', 'ambre.chram@wanadoo.fr', '0754552861', 'LeMDPEstRéel', 'Plouaret', 4, 'rue de l''armorique', 22420, '');

-- TYPE_OFFRE --
insert into pact._type_offre (nom_type, cout_HT, cout_TTC) values ('Gratuit', 0, 0);
insert into pact._type_offre (nom_type, cout_HT, cout_TTC) values ('Standard', 1.67, 2);
insert into pact._type_offre (nom_type, cout_HT, cout_TTC) values ('Premium', 3.34, 4);

-- OPTION --
insert into pact._option(nom_option, cout_HT, cout_TTC) values ('En relief', 8.34, 10);
insert into pact._option(nom_option, cout_HT, cout_TTC) values ('À la Une', 16.68, 20);

-- PROFESSIONNEL SECTEUR PUBLIC --
insert into pact.secteur_public (codePro, denomination_sociale, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values (1002, 'Denom', 'Jon', 'Halford', 'jon.hal@gmail.com', '0651101267', 'skjosdkkvd', 'Lannion', 4, 'place du marchal', 22300, '');
insert into pact.secteur_public (codePro, denomination_sociale, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values (728, 'MyBuisiness', 'Prigent', 'Richard', 'richard.prigent@wanadoo.fr', '0758124278', 'N5W7mOliROdlXYtRep', 'Saint-Brieuc', 11, 'rue de la gare', 22000, '');
insert into pact.secteur_public (codePro, denomination_sociale, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values (5642, 'Waw L''entreprise','Glichton', 'Carter', 'theglitchton@gmail.com', '0610254122', 'exTraSpiCYhitBox', 'Dinan', 1, 'résidence la conninais', 22100, '');




-- PROFESSIONNEL SECTEUR PRIVE --
insert into pact.secteur_prive (codePro, denomination_sociale, iban, bic, siren, raison_sociale, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values (1003, 'Hal INC', 'iban', '', '125475842', 'Raison sociale', 'Jon', 'Halford', 'jon.hal@gmail.com', '0651101267', 'skjosdkkvd', 'Lannion', 4, 'place du marchal', 22300, ''); 
insert into pact.secteur_prive (codePro, denomination_sociale, iban, bic, siren, raison_sociale, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values (212, 'Youpi Land', 'iban', 'bic', '144365479', 'Raison sociale', 'Portimao', 'Jean-Jacques', 'jeanjacsques.portimao@orange.fr', '0654620539', 'lQ6Jm896GfAz', 'Paimpol', 13, 'rue salvador allende', 22500, '');
insert into pact.secteur_prive (codePro, denomination_sociale, iban, bic, siren, raison_sociale, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) values (18, 'LaVilleBlanche', 'iban', 'bic', '682647654', 'Raison sociale', 'Modem', 'Nicola', 'modem.nicola@gmail.com', '0742576876', 'MjTdtLDapoUd', 'Guingamp', 18, 'rue valentin', 22200, '');


--insert into pact.offre (titre, resume, "type", description, prix_min, accessibilite, date_creation, date_publication, ville, numero_voie, voie, code_postal, complement, idC) values ('TITRE', 'resume', 2, 'desc', '5.00'::double precision, 'Accessible', now(), now(), 'Perros-Guirec', 5, 'rue des cordiers', 22700, '', 11);

-- OFFRES -- 
-- 1 --
insert into pact.spectacle (titre, resume, "type", description, site_web, prix_min, accessibilite, date_creation, date_publication, duree, capacite, ville, numero_voie, voie, code_postal, complement, idC) values ('La Magie des arbres', 'Sur le site exceptionnel de la plage de Tourony, au cœur de la côte de Granit rose.', 2, 'Venez découvrir la Magie des arbres dans ce site exceptionnel de la côte de Granit rose : première partie musicale autour de Bagad de Perros-Guirec, puis la nuit tombée, assistez au son et lumière avec la projection sur des voiles de grands mâts tendues dans les arbres.', 'https://www.cotesdarmor.com/sites-incontournables/ploumanach-et-la-cote-de-granit-rose/', 5.00, 'Accessible pour personnes en situation de handicap par un ascenseur', now(), now(), interval '1 hour 30 minutes', 300, 'Perros-Guirec', 5, 'rue des cordiers', 22700, '', 11);
insert into _option_offre(idO, idOption, date_lancement, duree_option) values(currval('_offre_ido_seq'), 1, now(), interval '7 day');
-- 2 --
insert into pact.activite (titre, resume, "type", description, site_web, prix_min, accessibilite, date_creation, date_publication, duree, age_min, prestation_incluse, prestation_excluse, ville, numero_voie, voie, code_postal, complement, idC) values ('Qui m''aime me suive', 'Montrer que l''on peut réaliser localement de belles balades à vélo, en empruntant de petites routes tranquilles et sans trop monter.', 2, 'Les sorties sont volontairement limitées entre 15km et 20km pour permettre à un large public familial de se joindre à nous.', 'https://www.tregorbicyclette.fr', 0.00, 'Le public en situation de handicap est le bienvenu', now(), now(), interval '6 hours', 12, 'Encadrant, Kit de crevaisons, Déjeuner sandwich', 'Bicyclette, Crème solaire', 'Lannion', 3, 'allée des soupirs', 22300, '', 12);
-- 3 --
insert into pact.visite (titre, resume, "type", description, site_web, prix_min, accessibilite, date_creation, date_publication, duree, langues, ville, numero_voie, voie, code_postal, complement, idC) values ('Excursion vers les 7 îles', 'Découvrer l''archipel des Sept-Îles, la plus grande réserve ornithologique de France.', 3, 'Les vedettes des 7 Îles proposent des excursions et des visites commentées vers l''archipel des Sept-Îles, au départ de Perros-Guirrec. Le site est protégé et l''accès aux îles réglementé, mais vous pourrez néanmoins fouler le sol de l''Île-aux-Moines.', 'https://armor-navigation.bzh', 8.50, 'Accueil du public en situation de handicap avec fauteuil roulant manuel', now(), now(), interval '3 hours', 'Français, Anglais', 'Perros-Guirec', 39, 'boulevard joseph le bihan', 22700, '', 12);
-- 4 --
insert into pact.restauration (titre, resume, "type", description, site_web, prix_min, accessibilite, date_creation, date_publication, src_image, gamme_prix, petit_dejeuner, brunch, dejeuner, diner, boissons, ville, numero_voie, voie, code_postal, complement, idC) values ('La ville blanche', 'La Ville Blanche, en plein cœur du Trégor, non loin de la côte de Granit Rose, vous accueille pour le plaisir des papilles.', 3, 'Ce petit corps de ferme repris par la famille Jaguin est devenu, au fil du temps, une Maison de renom. D''aventures en aventures, la passion de cette cuisine s''est maintenant transmise, des souvenirs et des moments se sont déroulés dans cette Maison symbolique de Bretagne.', 'https://la-ville-blanche.com', 20.00, 'Non', now(), now(), 'image_offre/4-menu.jpg', '€€', false, false, true, true, true, 'Lannion', 29, 'route de tréguier', 22300, '', 13);
insert into _option_offre(idO, idOption, date_lancement, duree_option) values(currval('_offre_ido_seq'), 2, now(), interval '14 day');
insert into _horaire (idO, heureOuverture, heureFermeture) values (4, '09:00', '10:00');
-- 5 --
insert into pact.parc_d_attraction (titre, resume, "type", description, site_web, prix_min, accessibilite, date_creation, date_publication, src_image, nbr_attractions, age_min, ville, numero_voie, voie, code_postal, complement, idC) values ('Le village Gaulois', 'Explorer le village Gaulois de Pleumeur-Bodou et ses multiples attractions.', 1, 'L''association Un Village Gaulois pour l''Afrique accueil toute votre famille pour réaliser nos attractions qui sont destinées à tout âge. Des activités seront également mises à disposition, réalisées avec nos employés.', 'https://www.levillagegaulois.org/php/home.php', 5.00, 'Le public en situation de handicap est accueilli à main ouverte', now(), now(), 'image_offre/5-plan.jpg', 14, 4, 'Pleumeur-Bodou', 1, 'parc du radôme', 22560, '', 10);
-- 6 --
insert into pact.activite (titre, resume, "type", description, site_web, prix_min, accessibilite, date_creation, date_publication, duree, age_min, prestation_incluse, prestation_excluse, ville, numero_voie, voie, code_postal, complement, idC) values ('Archipel de Bréhat en kayak', 'Découvrer l''archipel de Bréhat, en Bretagne, offre des paysages magnifiques et des eaux cristallines, parfaits pour explorer en kayak.', 2, 'L''archipel de Bréhat, situé au large de la côte bretonne, est un véritable joyau naturel, composé de plusieurs îles et îlots aux paysages variés. En kayak, vous pourrez naviguer entre ses eaux cristallines et ses rochers granitiques, tout en découvrant des plages de sable fin et une riche biodiversité marine.', 'https://www.guingamp-paimpol.com/bienvenue-chez-nous/les-sites-incontournables/archipel-de-brehat', 9.50, 'Non', now(), now(), interval '1 hour 30 minutes', 13, 'Kayak, Pagaie, Gilet de sauvetage', 'Casque', 'Île-de-Bréhat', 1, 'place du centre', 22870, '', 11);
-- 7 --
insert into pact.visite (titre, resume, "type", description, site_web, prix_min, accessibilite, date_creation, date_publication, duree, langues, ville, numero_voie, voie, code_postal, complement, idC) values ('Parc et château de la Roche Jagu', 'Le Parc et Château de la Roche Jagu, situé en Bretagne, allie patrimoine historique et jardins paysagers.', 1, 'Le Parc et Château de la Roche Jagu est un lieu chargé d''histoire et de beauté naturelle. Datant du XVe siècle, le château, avec son architecture médiévale, surplombe la vallée du Trieux et invite à la découverte de ses salons et de son histoire fascinante.', 'https://www.larochejagu.fr', 3.00, 'Le public en situation de handicap est accueilli et pris en main', now(), now(), interval '3 hours', 'Français', 'Ploëzal', 1, 'parc de la roche-jagu', 22260, '', 8);

update _offre set en_ligne = true; 



-- TAGS DEFAULT --
insert into pact.tag_default (libelle) values ('Culturel');
insert into pact.tag_default (libelle) values ('Patrimoine');
insert into pact.tag_default (libelle) values ('Histoire');
insert into pact.tag_default (libelle) values ('Urbain');
insert into pact.tag_default (libelle) values ('Nature');
insert into pact.tag_default (libelle) values ('Pleine air');
insert into pact.tag_default (libelle) values ('Sport');
insert into pact.tag_default (libelle) values ('Nautique');
insert into pact.tag_default (libelle) values ('Gastronomie');
insert into pact.tag_default (libelle) values ('Musée');
insert into pact.tag_default (libelle) values ('Atelier');
insert into pact.tag_default (libelle) values ('Musique');
insert into pact.tag_default (libelle) values ('Famille');
insert into pact.tag_default (libelle) values ('Cinéma');
insert into pact.tag_default (libelle) values ('Cirque');
insert into pact.tag_default (libelle) values ('Son et lumière');
insert into pact.tag_default (libelle) values ('Humour');

-- TAGS RESTO --

insert into pact.tag_resto (libelle) values ('Français');
insert into pact.tag_resto (libelle) values ('Fruit de mer');
insert into pact.tag_resto (libelle) values ('Asiatique');
insert into pact.tag_resto (libelle) values ('Indienne');
insert into pact.tag_resto (libelle) values ('Italienne');
insert into pact.tag_resto (libelle) values ('Gastronomique');
insert into pact.tag_resto (libelle) values ('Restauration rapide');
insert into pact.tag_resto (libelle) values ('Crêperie');
insert into pact.tag_resto (libelle) values ('Végétarienne');
insert into pact.tag_resto (libelle) values ('Végétalienne');
insert into pact.tag_resto (libelle) values ('Kebab');

-- TAGS OFFRES --

-- spectacle
insert into pact._tags_spectacle(idO, idTag) values (1,5);
insert into pact._tags_spectacle(idO, idTag) values (1,6);

-- activite
insert into pact._tags_activite(idO, idTag) values (2,5);
insert into pact._tags_activite(idO, idTag) values (2,6);
insert into pact._tags_activite(idO, idTag) values (2,7);
insert into pact._tags_activite(idO, idTag) values (2,13);

insert into pact._tags_activite(idO, idTag) values (6,6);
insert into pact._tags_activite(idO, idTag) values (6,7);
insert into pact._tags_activite(idO, idTag) values (6,8);
insert into pact._tags_activite(idO, idTag) values (6,13);

-- visite
insert into pact._tags_visite(idO, idTag) values (3,5);
insert into pact._tags_visite(idO, idTag) values (3,6);
insert into pact._tags_visite(idO, idTag) values (3,8);

insert into _tags_visite(idO, idTag) values (7,1);
insert into _tags_visite(idO, idTag) values (7,2);
insert into _tags_visite(idO, idTag) values (7,3);

-- restauration
insert into pact._tags_restauration(idO, idTag) values (4,18);
insert into pact._tags_restauration(idO, idTag) values (4,25);

-- parc_d_attraction
insert into pact._tags_parc_d_attraction(idO, idTag) values (5,6);
insert into pact._tags_parc_d_attraction(idO, idTag) values (5,13);
insert into pact._tags_parc_d_attraction(idO, idTag) values (5,17);

-- IMAGES --

insert into pact.photo_offre (src_image, idO) values ('image_offre/1-1.png', 1);
insert into pact.photo_offre (src_image, idO) values ('image_offre/1-2.png', 1);
insert into pact.photo_offre (src_image, idO) values ('image_offre/1-3.png', 1);
insert into pact.photo_offre (src_image, idO) values ('image_offre/2-1.jpg', 2);
insert into pact.photo_offre (src_image, idO) values ('image_offre/3-1.png', 3);
insert into pact.photo_offre (src_image, idO) values ('image_offre/3-2.png', 3);
insert into pact.photo_offre (src_image, idO) values ('image_offre/3-3.png', 3);
insert into pact.photo_offre (src_image, idO) values ('image_offre/4-1.png', 4);
insert into pact.photo_offre (src_image, idO) values ('image_offre/4-2.png', 4);
insert into pact.photo_offre (src_image, idO) values ('image_offre/4-3.png', 4);
insert into pact.photo_offre (src_image, idO) values ('image_offre/5-1.webp', 5);
insert into pact.photo_offre (src_image, idO) values ('image_offre/6-1.jpg', 6);
insert into pact.photo_offre (src_image, idO) values ('image_offre/7-1.jpg', 7);
insert into pact.photo_offre (src_image, idO) values ('image_offre/7-2.jpg', 7);

-- AVIS --

insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (2, 1, 'Excellent mais', 4, 'Le spectacle était incroyable, que ce soit la partie musicale ou la projection sur les voiles la nuit, mais les réceptionneurs et assistants ont été peu fiables lorsque nous avons demandé de l''aide.', now(), 'Famille');
insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (3, 2, 'Balade très appaisante', 5, 'Les multiples routes sont très tranquilles et effectivement sans trop de montées et il n''y a pas un nombre énorme de personnes, rendant la visite plus agréable. J''espère que plus de routes seront disponibles dans le futur.', now(), 'Seul');
insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (6, 2, 'Relaxant', 4, 'Moi et mes enfants avons emprunté un des parcours cet après-midi et ils ont tous les deux adoré. Le parcours était néanmoins un peu long, peut-être faudrait-il ajouter un parcours moins long pour les enfants.', now(), 'Famille');
insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (4, 3, 'Rien a reprocher', 5, 'Nous avons repéré cette visite depuis un petit moment et avons enfin eu le temps de visiter les sept îles avec ma famille. La visite était incroyable, les îles étaient magnifiques et notre guide était très sympathique, nous recommandons fortement', now(), 'Famille');
insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (6, 3, 'Quelque peu déçu', 2, 'Je suis parti visiter les fameuses 7 îles, mais j''ai été assez déçu pour la réputation qu''elles possèdent.', now(), 'Famille');
insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (2, 4, 'Correcte', 3, 'Les plats proposés étaient corrects, mais trop chers à mon goût.', now(), 'Amis');
insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (5, 4, 'Délicieux', 5, 'Cela fait plusieurs fois que nous revenons ici avec mes amis, donc voici mon avis ; la sélection des entrées, plats principaux et desserts est variée et sont tous délicieux. Le service est aussi très rapide et les employés sont tous très amicaux. Je ne peux pas plus recommander ce restaurant.', now(), 'Amis');
insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (7, 4, 'Surprenant', 3, 'La Ville Blanche est un restaurant servant un menu très divers et unique qui possède son charme. Malheureusement ce n''est pas forcément ce que j''apprécie personnellement, d''où ma note de 3, mais je suis sûr que d''autre personnes trouveront leur bonheur ici.', now(), 'Seul');
insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (3, 5, 'Décu de la visite', 2, 'Je suis allé visiter le Village Gaulois après avoir entendu de multiples recommendations de mes proches, cependant il ne semble pas que j''ai eu la même expérience qu''eux puisque j''ai trouvé les attractions très médiocres.', now(), 'Seul');
insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (4, 5, 'Très agréable pour les enfants', 4, 'Mes enfants ont adoré les attractions proposées et se sont divertis pendant deux heures. Il manque peut-être quelques attractions pour les plus grands.', now(), 'Famille');
insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (6, 5, 'Parc d''attraction original', 4, 'Parc d''attraction très sympathique avec un thème unique et des attractions mémorables. J''aurai espéré voir plus d''attractions à sensation forte cependant.', now(), 'Famille');
insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (1, 6, 'Agréable', 4, 'Étant tous des kayakistes depuis plusieurs années, nous avons apprécié l''activité ainsi que ses paysages divers offerts.', now(), 'Amis');
insert into pact.experience (idC, idO, titre_avis, note, corps, date_visite, contexte) values (3, 6, 'Inacceptable', 1, 'Lorsque nous sommes arrivés sur place, il manquait de l''équipement qui était censé être mis à disposition, ce qui a énormément retardé notre départ.', now(), 'Famille');

-- FACTURE --
/*
insert into pact._facturation(numFacture, idO, date_facture) values (12345, 1, date_trunc('month', current_date));
insert into pact._facturation(numFacture, idO, date_facture) values (12121, 6, date_trunc('month', current_date));
insert into pact._facturation(numFacture, idO, date_facture) values (54321, 1, date_trunc('month', now() - interval '1 month'));
*/

--Dates--

update pact.spectacle set date_publication = (now() - interval '2 days') where idO = 1;
update pact.activite set date_publication = (now() - interval '3 days') where idO = 2;
update pact.visite set date_publication = (now() - interval '4 days') where idO = 3;
update pact.restauration set date_publication = (now() - interval '5 days') where idO = 4;
update pact.parc_d_attraction set date_publication = (now() - interval '6 days') where idO = 5;
update pact.activite set date_publication = (now() - interval '1 days') where idO = 6;
update pact.visite set date_publication = (now() - interval '8 days') where idO = 7;
update _option_offre set  date_lancement = now() - interval '1 day', duree_option = interval '7 day' where idO = 1;
