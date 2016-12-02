Drop procedure if exists updatePimTestTime;


DELIMITER $$
CREATE PROCEDURE updatePimTestTime()
BEGIN
	declare bDone INTEGER default 0;
	DECLARE pim_ser_num_res INT(11) default 0;
	DECLARE test_time_res datetime default '1999-10-10';
		
	declare curs CURSOR FOR 
		select max(test_time) as test_time,pim_ser_num 
		from pim_ser_num_group group by pim_ser_num;
		
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET bDone = 1;
	
    open curs;
		repeat
			fetch curs into test_time_res, pim_ser_num_res;
				update pim_ser_num set test_time = test_time_res where id = pim_ser_num_res;
        until bDone end repeat;
    close curs;
    
-- OPEN curs;
-- 		get_result: loop
-- 			fetch curs into test_time, pim_ser_num;
-- 			if bDone = 1 then
-- 				leave get_result;
-- 			end if;
--             
--             select test_time, pim_ser_num;
--             
-- 		end loop get_result;
-- 	close curs;
			
END $$

DELIMITER ;