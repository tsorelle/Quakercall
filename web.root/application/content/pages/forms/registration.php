<?php
$result = \Application\quakercall\services\JotFormManager::processForm();
?>
<h2>Thanks for your registration</h2>
<p>On the day of the meeting, go to this address to sign in and receive the Zoom video conference link:<br>
    <a href="https://qcall.2quakers.net/meeting">https://qcall.2quakers.net/meeting</a>
</p>
<p>We'll send a reminder by email.</p>
<p>Use the contact link below if any of the information below is incorrect or if you have any other questions.  Please
    reference your email or the "SubmissionId" in the list below.</p>

<table class="table">
    <tbody>
        <tr><td>First Name     </td><td><?php print ( 	$result->firstName     );?> </td></tr>
        <tr><td>Last Name      </td><td><?php print ( 	$result->lastName      );?> </td></tr>
        <tr><td>Email         </td><td><?php print (	$result->email         );?> </td></tr>
        <tr><td>Phone  		</td><td><?php print ( $result->phone              );?> </td></tr>
        <tr><td>Location      </td><td><?php print ($result->location          );?> </td></tr>
        <tr><td>Affiliation   </td><td><?php print (  $result->affiliation     );?> </td></tr>
        <tr><td>Meeting or Organization   </td><td><?php print (  $result->meeting  );?> </td></tr>
        <?php
        if($result->testmode === 'yes'){   ?>
            <tr><td>Submission Date</td><td><?php print (	$result->submissionDate);?> </td></tr>
            <tr><td>SubmissionId</td><td><?php print ( $result->submissionId       );?> </td></tr>
            <tr><td>Ip Address   </td><td><?php print ( 	$result->ipAddress     );?> </td></tr>
            <tr><td>FormId     	</td><td><?php print ( $result->formId             );?> </td></tr>
            <tr><td>MeetingId    </td><td><?php print ( $result->meetingId       )?></td></tr>
        <?php }?>
    </tbody>
</table>

<div>
    <?php
    print_r($_POST)
    ?>
</div>