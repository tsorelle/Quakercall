SELECT m.`meetingDate`, m.`theme`, COUNT(*) AS Registrations
FROM qcall_registrations r JOIN qcall_meetings m ON r.`meetingId` = m.`id`
GROUP BY m.id
ORDER BY m.`meetingDate`