/****************************** PROCEDURES ******************************/



-- upologizei vathmo diplwmatikhs kai ton eisagei stin gramateia gia egkrish
    -- works
    USE project_web;
    DROP PROCEDURE IF EXISTS `grades_final`;
    DELIMITER //

    CREATE PROCEDURE `grades_final`(IN diplwmatiki_id INT,IN final_grade_prof1 INT,IN final_grade_prof2 INT,IN final_grade_prof3 INT)
    BEGIN
        DECLARE _final_grade INT;
        DECLARE am_foitita INT;
        DECLARE email_f VARCHAR(50);
        DECLARE nemerti_link VARCHAR(100);
        DECLARE profof1 VARCHAR(50);
        DECLARE profof2 VARCHAR(50);
        DECLARE profof3 VARCHAR(50);

        SET _final_grade = (final_grade_prof1 + final_grade_prof2 + final_grade_prof3) / 3;

        SELECT am_foititi 
        INTO am_foitita
        FROM diplwmatiki_foitita
        WHERE id_diplwmatikis = diplwmatiki_id; 

        SELECT email
        INTO email_f
        FROM Students
        WHERE am = am_foitita
        LIMIT 1;

        SELECT nemertes_link
        INTO nemerti_link
        FROM diplwmatiki_foitita
        WHERE id_diplwmatikis = diplwmatiki_id; 

        SELECT prof1
        INTO profof1
        FROM gramateia
        WHERE id_diplwmatikis = diplwmatiki_id;

        SELECT prof2
        INTO profof2
        FROM gramateia
        WHERE id_diplwmatikis = diplwmatiki_id;

        SELECT prof3
        INTO profof3
        FROM gramateia
        WHERE id_diplwmatikis = diplwmatiki_id;

        INSERT INTO gramateia (aitwn_email, am_foititi, id_diplwmatikis, final_grade, nemertes_link, prof1, prof2, prof3, aithsh_gia)
        VALUES (email_f, am_foitita, diplwmatiki_id, _final_grade, nemerti_link, profof1, profof2, profof3, 'pros_egrisi_oloklirwmenh');

        UPDATE vathmologio SET final_grade = _final_grade WHERE id_diplwmatikis = diplwmatiki_id;

    END;
    //

    DELIMITER ;



-- insert sth grammateia gia egkrish akurwshs
    --works
    USE project_web;
    DROP PROCEDURE IF EXISTS `insert_grammateia_pros_akurwsh`;
    DELIMITER //

    CREATE PROCEDURE `insert_grammateia_pros_akurwsh` (IN diplwmatiki_id INT, IN reason ENUM('kathigitis', 'foititis'))
    BEGIN
        DECLARE am_foitita INT;
        DECLARE email_f VARCHAR(50);
        DECLARE profof1 VARCHAR(50);
        DECLARE profof2 VARCHAR(50);
        DECLARE profof3 VARCHAR(50);

        SELECT am_foititi
        INTO am_foitita
        FROM diplwmatiki_foitita
        WHERE id_diplwmatikis = diplwmatiki_id
        LIMIT 1;

        IF am_foitita IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Η διπλωματική δεν βρέθηκε ή δεν είναι ενεργή.';
        END IF;

        SELECT email
        INTO email_f
        FROM Students
        WHERE am = am_foitita
        LIMIT 1;

        SELECT email
        INTO profof1
        FROM diplwmatiki_ka8igita
        WHERE id = diplwmatiki_id AND (status = 'energi' OR status = 'pros_egrisi')
        LIMIT 1;

        SELECT prof2, prof3
        INTO profof2, profof3
        FROM diplwmatiki_foitita
        WHERE id_diplwmatikis = diplwmatiki_id
        LIMIT 1;

        IF (reason = 'kathigitis') THEN
            INSERT INTO gramateia (
                aitwn_email, am_foititi, id_diplwmatikis, prof1, prof2, prof3, aithsh_gia, apanthsh
            ) VALUES (
                profof1, am_foitita, diplwmatiki_id, profof1, profof2, profof3, 'pros_egrisi_akurwshs', 'pending'
            );
        ELSEIF (reason = 'foititis') THEN
            INSERT INTO gramateia (
                aitwn_email, am_foititi, id_diplwmatikis, prof1, prof2, prof3, aithsh_gia, apanthsh
            ) VALUES (
                email_f, am_foitita, diplwmatiki_id, profof1, profof2, profof3, 'pros_egrisi_akurwshs', 'pending'
            );
        END IF;
    END;
    //
    DELIMITER ;



-- insert sth grammateia gia egkrish allaghs foithth
    --works
    USE project_web;
    DROP PROCEDURE IF EXISTS `insert_grammateia_pros_allagh`;
    DELIMITER //

    CREATE PROCEDURE `insert_grammateia_pros_allagh` (IN diplwmatiki_id INT, IN reason ENUM('kathigitis', 'foititis'))
    BEGIN
        DECLARE am_foitita INT;
        DECLARE email_f VARCHAR(50);
        DECLARE profof1 VARCHAR(50);
        DECLARE profof2 VARCHAR(50);
        DECLARE profof3 VARCHAR(50);

        SELECT am_foititi
        INTO am_foitita
        FROM diplwmatiki_foitita
        WHERE id_diplwmatikis = diplwmatiki_id
        LIMIT 1;

        IF am_foitita IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Η διπλωματική δεν βρέθηκε ή δεν είναι ενεργή.';
        END IF;

        SELECT email
        INTO email_f
        FROM Students
        WHERE am = am_foitita
        LIMIT 1;

        SELECT email
        INTO profof1
        FROM diplwmatiki_ka8igita
        WHERE id = diplwmatiki_id AND (status = 'energi' OR status = 'pros_egrisi')
        LIMIT 1;

        SELECT prof2, prof3
        INTO profof2, profof3
        FROM diplwmatiki_foitita
        WHERE id_diplwmatikis = diplwmatiki_id
        LIMIT 1;

        IF (reason = 'kathigitis') THEN
            INSERT INTO gramateia (
                aitwn_email, am_foititi, id_diplwmatikis, prof1, prof2, prof3, aithsh_gia, apanthsh
            ) VALUES (
                profof1, am_foitita, diplwmatiki_id, profof1, profof2, profof3, 'pros_egrisi_allagis_f', 'pending'
            );
        ELSEIF (reason = 'foititis') THEN
            INSERT INTO gramateia (
                aitwn_email, am_foititi, id_diplwmatikis, prof1, prof2, prof3, aithsh_gia, apanthsh
            ) VALUES (
                email_f, am_foitita, diplwmatiki_id, profof1, profof2, profof3, 'pros_egrisi_allagis_f', 'pending'
            );
        END IF;
    END;
    //
    DELIMITER ;




-- insert sth grammateia thn "aithsh" gia egkrisi enarkshs
    -- works
    USE project_web;
    DROP PROCEDURE IF EXISTS `insert_grammateia_pros_egkrisi`;
    DELIMITER //

    CREATE PROCEDURE `insert_grammateia_pros_egkrisi`(IN id_diplwmatikis_param INT)
    BEGIN
        DECLARE am_foitita INT;
        DECLARE email_f VARCHAR(50);
        DECLARE profof1 VARCHAR(50);
        DECLARE profof2 VARCHAR(50);
        DECLARE profof3 VARCHAR(50);

        SELECT am_foititi
        INTO am_foitita
        FROM diplwmatiki_foitita
        WHERE id_diplwmatikis = id_diplwmatikis_param
        LIMIT 1;

        SELECT email
        INTO email_f
        FROM Students
        WHERE am = am_foitita
        LIMIT 1;

        SELECT email
        INTO profof1
        FROM diplwmatiki_ka8igita
        WHERE id = id_diplwmatikis_param AND status = 'pros_egrisi'
        LIMIT 1;

        SELECT prof2, IFNULL(prof3, 'N/A')
        INTO profof2, profof3
        FROM diplwmatiki_foitita
        WHERE id_diplwmatikis = id_diplwmatikis_param
        LIMIT 1;

        INSERT INTO gramateia (aitwn_email, am_foititi, id_diplwmatikis, prof1, prof2, prof3, aithsh_gia, apanthsh)
        VALUES (email_f, am_foitita, id_diplwmatikis_param, profof1, profof2, profof3, 'pros_egrisi_energi', 'pending');
    END;
    //
    DELIMITER ;




-- an sumplirwthoun 2 sumbouloi, akurwnei ta upoloipa requests kai allazei status diplwmatikis se pros_egrisi, stelnei kai aithsh sth grammateia
    -- works
    USE project_web;
    drop procedure if exists update_diplwmatiki_procedure;
    DELIMITER //

    CREATE PROCEDURE update_diplwmatiki_procedure (IN p_id_diplwmatikis INT)
    BEGIN
        DECLARE v_prof2 VARCHAR(50);
        DECLARE v_prof3 VARCHAR(50);
        DECLARE v_status VARCHAR(50);
        SELECT prof2, prof3
        INTO v_prof2, v_prof3
        FROM diplwmatiki_foitita
        WHERE id_diplwmatikis = p_id_diplwmatikis;

        SELECT status
        INTO v_status
        FROM diplwmatiki_ka8igita
        WHERE id = p_id_diplwmatikis;

        IF (v_prof2 IS NOT NULL AND v_prof3 IS NOT NULL) THEN
            UPDATE diplwmatiki_ka8igita
            SET status = 'pros_egrisi'
            WHERE id = p_id_diplwmatikis;

            UPDATE epivlepontes_requests
            SET status = 'canceled',
                date_answered = NOW()
            WHERE id_diplomatikis = p_id_diplwmatikis
            AND status = 'pending';

            CALL insert_grammateia_pros_egkrisi(p_id_diplwmatikis);
        END IF;
    END;
    //
    DELIMITER ;



/****************************** TRIGGERS ******************************/



--insert ston vathmologio ta stoixeia twn prof kai status se anamoni otan diplwmatiki allazei se exetasi
    --works
    USE project_web;
    DROP TRIGGER IF EXISTS after_status_update_to_exetasi;
    DELIMITER //

    CREATE TRIGGER after_status_update_to_exetasi
    AFTER UPDATE ON diplwmatiki_ka8igita
    FOR EACH ROW
    BEGIN
        IF NEW.status = 'exetasi' AND OLD.status != 'exetasi' THEN
            INSERT INTO vathmologio (id_diplwmatikis,prof1,prof2,prof3,status)
            SELECT 
                NEW.id,
                gramateia.prof1,
                gramateia.prof2,
                gramateia.prof3,
                'anamoni'
            FROM gramateia
            WHERE gramateia.id_diplwmatikis = NEW.id;
        END IF;
    END;
    //
    DELIMITER ;



-- kalei thn grades final gia eisagwgh vathmou diplwmatikhs sth grammateia gia egkrish, requires api change status to anamoni_gia_egkrisi
    --works
    USE project_web;
    DROP TRIGGER IF EXISTS after_status_update_to_vathmologisi;
    DELIMITER //

    CREATE TRIGGER after_status_update_to_vathmologisi
    AFTER UPDATE ON vathmologio
    FOR EACH ROW
    BEGIN
        IF NEW.status = 'anamoni_gia_egkrisi' AND OLD.status != 'anamoni_gia_egkrisi' THEN
            CALL grades_final(
                NEW.id_diplwmatikis,
                NEW.prof1_final_grade,
                NEW.prof2_final_grade,
                NEW.prof3_final_grade
            );
        END IF;
    END;
    //
    DELIMITER ;
