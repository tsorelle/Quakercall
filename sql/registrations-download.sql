

SELECT 
`submissionDate`,`meetingId`, r.`participant`,`religion`,`affiliation`,`location`,
`email`,`phone`,`organization`,`firstName`,`lastName`,`email`,
`address1`,`address2`,`city`,`state`,`postalcode`,`country`,`sortcode`,`subscribed`,`confirmed`

FROM qcall_registrations r
JOIN qcall_contacts c ON c.id = r.contactId
WHERE meetingid = 6



