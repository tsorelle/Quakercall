<?php
$result = \Application\quakercall\services\JotFormManager::processEndorsement();
?>
<h3>Thank you for your endorsement.</h3>

<p>Please review the information you entered.  Use the 'Contact us' link below
to let us know if any corrections need to be made, or if you have any other questions or concerns.</p>


<table class="table">
    <tbody>
    <tr><td>First Name     </td><td><?php print ( 	$result->firstName     );?> </td></tr>
    <tr><td>Last Name      </td><td><?php print ( 	$result->lastName      );?> </td></tr>
    <tr><td>Email         </td><td><?php print (	$result->email         );?> </td></tr>
    <tr><td>Address         </td><td>
            <?php
            print($result->address1.'<br>');
            print($result->address2.'<br>');
            $hasCity = !empty($result->city);
            $hasState = !empty($result->state);
            $hasZip = !empty($result->postalcode);

            $location = $result->city;
            if ($hasState) {
                if ($hasCity) { $location .= ', '; }
                $location .= $result->state;
            }
            if ($hasZip) {
                if (!empty($location)) { $location .= ' '; }
                $location .= $result->postalcode;
            }
            print($location);
            if (!empty($result->country)) {
                if (!empty($location)) {
                    print('<br>');
                }
                print($result->country);
            }
            ?> </td></tr>

    <tr><td>Religious Affiliation   </td><td><?php print (  $result->religion     );?> </td></tr>
    <tr><td>Friends Meeting   </td><td><?php print (  $result->meeting  );?> </td></tr>
    <tr><td>How you found us</td> <td><?php print (  $result->found  );?> </td></tr>

    <tr><td>Comments</td><td><?php
            $lines = explode("\n", $result->comments);
            foreach ($lines as $line) {
                print($line.'<br>');
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