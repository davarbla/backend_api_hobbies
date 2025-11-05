select id_user,public, fullname, ugly,image,image2 from tb_user where username like '%Touze%';
select * from tb_user where username like '%raa%';
-- DELETE PICS
CALL `u439050121_hobbies`.`reset_profile`(2419);
CALL `u439050121_hobbies`.`reset_image2_id`(2230);
CALL `u439050121_hobbies`.`reset_all_public`(2178);

CALL `u439050121_hobbies`.`reset_public`(760);
CALL `u439050121_hobbies`.`reset_all_public_id`(2178);

-- NOTAS
-- UGLY
CALL `u439050121_hobbies`.`ugly_boys`("1164");
CALL `u439050121_hobbies`.`sexy_id`(61,3);
CALL `u439050121_hobbies`.`sexy_id`(594,2);
-- HANDSOME
CALL `u439050121_hobbies`.`sexy_note`('parisboy16',7);
CALL `u439050121_hobbies`.`sexy_id`(1468,9);
CALL `u439050121_hobbies`.`sexy_id`(1437,8);
CALL `u439050121_hobbies`.`sexy_id`(1438,7);
CALL `u439050121_hobbies`.`sexy_id`(1078,6);
CALL `u439050121_hobbies`.`sexy_id`(833,5);
CALL `u439050121_hobbies`.`sexy_id`(846,4);

-- RELIABILITY
CALL `u439050121_hobbies`.`reliability_name_note`("Sbt972", 50);


CALL `u439050121_hobbies`.`before_appli_review`();




CALL `u439050121_hobbies`.`before_appli_review`();
CALL `u439050121_hobbies`.`after_appli_review`();



insert into tb_post(  title, description, id_category, id_user, latitude, location, image, image2, image3, subscribe_fcm, total_like, total_comment, total_user, total_download, total_view, total_report, timestamp, flag, status, date_created, date_updated, address_detail, address, bring, max_people, price, start_date, end_date, age_min, age_max, fun, lat, lng, country, cancell)
select   title, description, id_category, id_user, latitude, location, image, image2, image3, subscribe_fcm, total_like, total_comment, total_user, total_download, total_view, total_report, timestamp, flag, status, date_created, date_updated, address_detail, address, bring, max_people, price, start_date, end_date, age_min, age_max, fun, lat, lng, country, cancell from tb_post where id_post=94;

update tb_user set age = -1 where id_user=1960;
-- Update Message
update tb_user set message="PlayG te souhaite des vacances hot ! :-) \n Pour une meilleure expérience mets à jour l'application stp.**";
update tb_user set status = 0 where id_user = 1935;
