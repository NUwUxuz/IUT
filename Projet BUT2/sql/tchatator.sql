SET SCHEMA 'pact';

delete from pact._message;
ALTER SEQUENCE pact._message_idmessage_seq RESTART;
delete from pact._discussion;
ALTER SEQUENCE pact._discussion_iddiscussion_seq RESTART;


-- Discussion n°1 pour le test tchatator

-- Conversation entre le client "Jonnnie" et le Pro "Nicola Modem" de l'entreprise "LaVilleBlanche"
-- Conversation à propos du Restaurant la Ville Blanche

insert into _discussion(idClient, idPro) values (2, 13);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Bonjour, l''annonce du restaurant La Ville Blanche est-elle toujours valide ?', 1, 2, 13, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Bonjour Jonnie, oui, l''annonce est toujours valable. Que souhaitez-vous savoir ?', 1, 13, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Super ! Quels sont les horaires d''ouverture du restaurant ?', 1, 2, 13, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Le restaurant est ouvert du mardi au samedi, de 12h à 14h et de 19h à 22h.', 1, 13, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('D''accord, est-il possible de réserver une table en ligne ?', 1, 2, 13, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Oui, vous pouvez réserver via notre site ou par téléphone.', 1, 13, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Est-ce que le restaurant propose des options végétariennes ?', 1, 2, 13, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Bien sûr, nous avons plusieurs plats végétariens et vegan.', 1, 13, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Parfait ! Quels sont les moyens de paiement acceptés ?', 1, 2, 13, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Nous acceptons carte bancaire, espèces et tickets restaurant.', 1, 13, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Ok, proposez-vous un service de livraison ou à emporter ?', 1, 2, 13, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Oui, nous faisons de la vente à emporter, mais pas de livraison.', 1, 13, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('D''accord, y a-t-il un parking à proximité ?', 1, 13, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Oui, un parking gratuit est disponible à 50m du restaurant.', 1, 2, 13, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Le restaurant est-il accessible aux personnes à mobilité réduite ?', 1, 13, 2, true);
select pg_sleep(1);


insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Oui, nous disposons d''un accès PMR.', 1, 2, 13, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Merci pour ces infos !', 1, 13, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Avec plaisir !', 1, 2, 13, true);
select pg_sleep(1);



-- Discussion n°2 pour le test tchatator

-- Conversation entre le client "Jonnnie" et le Pro "Jean-Jacques Portimao" de l'entreprise "Youpi Land"
-- Conversation à propos du l'offre "Le village Gaulois"

insert into _discussion(idClient, idPro) values (2, 12);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Bonjour, j''ai vu votre offre sur le village gaulois, pouvez-vous m''en dire plus ?', 2, 2, 12, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Bonjour Jonnie ! Bien sûr, c''est une immersion dans un village gaulois reconstitué avec animations.', 2, 12, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Super ! Qu''est qu''il y a comme attractions le parc ?', 2, 2, 12, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Le parc a des spectacles et des ateliers artisanaux pour bien être immergé dans l''esprit gaulois.', 2, 12, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Faut-il réserver à l''avance ?', 2, 2, 12, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Ce n''est pas obligatoire, mais conseillé en haute saison.', 2, 12, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values (' Y a-t-il un parking sur place ?', 2, 2, 12, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Oui, un parking gratuit est disponible à l''entrée du parc.', 2, 12, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Ça a l''air génial ! Je vais réserver pour ce week-end.', 2, 2, 12, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Super ! Vous pouvez réserver en ligne ou à l''accueil.', 2, 12, 2, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Merci pour toutes les infos, à bientôt !.', 2, 2, 12, true);
select pg_sleep(1);

insert into _message(texte, idDiscussion, idEmmeteur, idReceveur, recu) 
values ('Avec plaisir, à bientôt au Village Gaulois !', 2, 12, 2, true);
select pg_sleep(1);