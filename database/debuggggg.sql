 /* DEBUG FILE FOR EVERY PROCEDURE AND TRIGGER */
 /* RUN EACH PART OF THIS FILE ON ITS OWN, DO NOT RUN ALL AT ONCE */





-- test the grades_final procedure
-- WHAT IS SHOULD DO 
--      a) insert sth grammateia kainourgio row me teliko vathmo 
--      b) update diplwmatikes kathigiti kai foithth status oloklirwmenh
use project_web;
call grades_final(1, 2, 9, 7);
SELECT * FROM gramateia WHERE id_diplwmatikis = 1;
SELECT * FROM diplwmatiki_ka8igita WHERE id = 1;
SELECT * FROM diplwmatiki_foitita WHERE id_diplwmatikis = 1;




 -- test the insert_grammateia_pros_egkrisi procedure
 -- WHAT IS SHOULD DO 
use project_web;
INSERT INTO diplwmatiki_ka8igita (email, creation_date, topic, summary, status, start_date, exam_date, file_name)
VALUES('eleni@ceid.gr','2020-01-01 00:00:00', 'Ανάπτυξη εφαρμογής για την ανάλυση των αποτελεσμάτων των εκλογών', 'Ανάπτυξη εφαρμογής για την ανάλυση των αποτελεσμάτων των εκλογών', 'energi', '2020-01-01 00:00:00', '2020-01-01 00:00:00', 'file_name');
use project_web;
INSERT INTO JOKER (
    id_diplwmatikis, prof1, grade_crit_1, grade_crit_2, grade_crit_3, grade_crit_4, 
    prof2, grade_crit_1_2, grade_crit_2_2, grade_crit_3_2, grade_crit_4_2, 
    prof3, grade_crit_1_3, grade_crit_2_3, grade_crit_3_3, grade_crit_4_3, 
    status
) VALUES (
    2, 'eleni@ceid.gr', 8.5, 7.0, 9.0, 8.0, 
    'toxrusoftiari@funerals.gr', 7.5, 8.0, 7.0, 8.5, 
    'akomninos@ceid.upatras.gr', 9.0, 8.5, 7.5, 8.0, 
    'prosva8mologisi'
);



 -- VAGEEEEEEEEEEEEEEEEEEELLLLLLLLLLLLLLL testare ton joker
 -- WHAT IT SHOULD DO 
use project_web;
INSERT INTO diplwmatiki_ka8igita (
    email, creation_date, topic, summary, status, start_date, exam_date, file_name
) VALUES (
    'toxrusoftiari@funerals.gr', '2023-01-15 10:00:00', 'Nekro8aftiki', 'funerals 101', 'energi', '', '', 'ml_research.pdf'
);

INSERT INTO diplwmatiki_ka8igita (
    email, creation_date, topic, summary, status, start_date, exam_date, file_name
) VALUES (
    'paraskevas@kobres.ath', '2023-02-20 11:30:00', 'Data Science', 'Study on data science methodologies', 'prosana8esi', '2023-03-01 09:00:00', '2023-07-01 10:00:00', 'data_science_study.pdf'
);
 

INSERT INTO diplwmatiki_ka8igita (
    email, creation_date, topic, summary, status, start_date, exam_date, file_name
) VALUES (
    'anittamaxwynn@cashmoney.com', '2023-03-10 14:45:00', 'Anitta max win', 'Maxwin 101', 'exetasi', '2023-04-01 09:00:00', '2023-08-01 10:00:00', 'max_win_analysis.pdf'
);


INSERT INTO diplwmatiki_foitita (
    am_foititi, id_diplwmatikis, prof2, prof3, nemertes_link, status
) VALUES (
    10434000, 2, 'eleni@ceid.gr', 'anittamaxwynn@cashmoney.com', NULL, 'energi'
);

INSERT INTO diplwmatiki_foitita (
    am_foititi, id_diplwmatikis, prof2, prof3, nemertes_link, status
) VALUES (
    10434002, 1, 'toxrusoftiari@funerals.gr', 'abcdefg@example.com', NULL , 'prosana8esi'
);

INSERT INTO diplwmatiki_foitita (
    am_foititi, id_diplwmatikis, prof2, prof3, nemertes_link, status
) VALUES (
    10434004, 3, 'abcdefg@example.com', 'toxrusoftiari@funerals.gr', NULL , 'exetasi'
);



 -- den xerw akoma, alla logika tha prepei na kanei insert sth grammateia kai na allazei to status twn diplwmatikwn na sai kala re copilot
 -- WHAT IT SHOULD DO 
use project_web;




 --
 -- WHAT IT SHOULD DO 
use project_web;




 --
 -- WHAT IT SHOULD DO 
use project_web;




 --
 -- WHAT IT SHOULD DO 
use project_web;


