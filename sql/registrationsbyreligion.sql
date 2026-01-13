5d kc v-- update qcall_registrations set religion = trim(religion);
SELECT religion, COUNT(*)
FROM qcall_registrations
WHERE meetingid=6
GROUP BY religion;


-- update qcall_registrations set affiliation = trim(affiliation);
SELECT affiliation AS `Friends Meeting`, COUNT(*) AS COUNT
FROM qcall_registrations
WHERE meetingid=6
GROUP BY affiliation;


-- select * from qcall_registrations where religion like '%universalist'

SELECT COUNT(*) FROM qcall_registrations
WHERE meetingid=6 AND (religion LIKE 'quaker%' OR religion LIKE 'friend%')
