-- SELECT
select * from tb_post order by id_post desc;
select * from tb_post where title like '%egsw%'order by id_post desc;
select id_post, title, id_user, title, start_date, end_date from tb_post    order by id_post desc;
-- UPDATE
-- owner
update tb_post set id_user =2 where id_post=311; -- 2 MOI --2212/2227 David 1770 Quentin
update tb_post set fun= 1 where id_post =288; -- 1616
update tb_post set description =(select description from tb_post where id_post=230) where id_post=350;
update tb_post set status=0 where id_post=263;


-- DUPLICATE
CALL `u439050121_hobbies`.`duplicate_post`(339);
update tb_post set start_date =  DATE_ADD(start_date, INTERVAL -29 DAY), end_date = DATE_ADD(end_date, INTERVAL -29 DAY), total_view=230 where id_post in(351);
update tb_post set  total_comment = 0, total_view= 0, total_user=7, date_created=timestamp   where id_post=350;
-- update tb_post set address='3 rue de crussol', latitude='48.8638684,2.3667043' where id_post=301;


-- DELETE
delete from tb_post where id_post = 346;

-- update description
update tb_post set description ='👉 Dimanche 21 avril 2024  👈

Soirée  «Touze des Olympiades » 
🕘 18h - 🕒 23h

Soirée conviviale, FUN🔥SeXe en mode partouze 😈 
 
🤽🏼‍♂️ Joueurs : sexy, bon esprit & motivés !
🗺 Lieu : Paris 13ème - Olympiades (à 1min du métro, ligne 14) 
🎯 Âges : 20-40 ans
🧮 Nombre invités : 20-30 
Note: cela ne préjuge pas du nombre de présents

Tout est prévu pour passer un bon moment excitant, convivial, en détente et en surchauffe 😋

🕰18h00-23h : verre d\'accueil, apéro dinatoire, touze. 
💶 Partage des frais d’organisation : 10€

👻 Prep ou Capote (gel et capotes à dispo)
Note: si t\'es sous Prep, prends la doxy-pep (200 mg doxycycline) pour réduire le risque de MST
💊 Pas de substances illégales.

En cas de problème pour l\'inscription,  tu peux contacter @TZ_Paris



2️⃣ 🆗 inscris-toi quand tu es sûr ; ne « réserve » pas ; nb joueurs limité ! 
Pour toute annulation après le 19 avril la participation est due.
☝️🐰 ➤ 📜🏴 (lapins black-listés)

A toi de jouer ...! 
David & Cie ♥️♠️
Note: la suivante ne sera pas annoncée ici, uniquement sur PlayG' where id_post in(341);
