<?php
$result = \Application\quakercall\services\EndorsementsFormManager::processOrgEndorsement();
?>
<h3>Thank you for your endorsement.</h3>

<p>Please review the information you entered.  Use the 'Contact us' link below
to let us know if any corrections need to be made, or if you have any other questions or concerns.</p>

<table class="table">
    <tbody>
    <tr><td>Organization Name     </td><td><?php print ( 	$result->organizationName     );?> </td></tr>
    <tr><td>Organization Type   </td><td><?php print (  $result->organizationType );?> </td></tr>
    <tr><td>Organization Address         </td><td>
            <?php
            print($result->address1.'<br>');
            print($result->address2.'<br>');
            if (!empty($result->city)) print($result->city).', ';
            if (!empty($result->state)) print ($result->state).' ';
            if (!empty($result->postalcode)) print($result->postalcode).'<br>';
            if (!empty($result->country)) print($result->country);

            ?> </td></tr>
    <tr><td>Contact First Name     </td><td><?php print ( 	$result->firstName     );?> </td></tr>
    <tr><td>Contact Last Name      </td><td><?php print ( 	$result->lastName      );?> </td></tr>
    <tr><td>Email         </td><td><?php print (	$result->email         );?> </td></tr>
    <tr><td>Phone         </td><td><?php print (	$result->phone         );?> </td></tr>

    <tr><td>Attachments </td><td>
            <?php
            $attachments = explode(',', $result->attachments);
            foreach ($attachments as $attachment) {
                    print (	$attachment.'<br>'  );
            }
            ?>
        </td></tr>
    <tr><td>Submission Id</td><td><?php print ( $result->submissionId       );?> </td></tr>
    <?php
    if($result->testmode === 'yes'){   ?>
        <tr><td>Submission Date</td><td><?php print (	$result->submissionDate);?> </td></tr>
        <tr><td>Ip Address   </td><td><?php print ( 	$result->ipAddress     );?> </td></tr>
        <tr><td>FormId     	</td><td><?php print ( $result->formId             );?> </td></tr>
        <tr><td>Form key    </td><td><?php print ( $result->formKey       )?></td></tr>
    <?php }?>

    </tbody>
</table>

<div>
    <?php
    if  ($result->testmode === 'yes') {
        print("<p><strong>For debugging...</strong></p>\n");
        print_r($_POST);
    }
    ?>
</div>