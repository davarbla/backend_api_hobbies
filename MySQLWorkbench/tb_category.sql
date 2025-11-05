SELECT * FROM tb_category order by date_created desc;
update tb_category set id_category_up = 131, country = 'FR', latitude = '48.8599986581607,2.345808951703', lat='48.8599986581607', lng='2.345808951703' where id_category IN (182,183,184);
update tb_category set id_owner = 1770 where id_category = 170;

delete from tb_category where id_category = 3180;

SELECT * FROM u439050121_hobbies.tb_category WHERE title LIKE '%Leather%';
update tb_category set fun = 1 where id_category_up=131;
SELECT * FROM u439050121_hobbies.tb_category WHERE title like 'Kin%';


commit;
