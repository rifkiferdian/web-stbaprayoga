<?php require_once('header.php'); ?>

<?php
// After form submit checking everything for email sending
if(isset($_POST['form1']))
{
    $error_message = '';
    $success_message = '';
    $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
    foreach ($result as $row) 
    {
        $receive_email = $row['receive_email'];
    }

    $valid = 1;

    if(empty($_POST['subject']))
    {
        $valid = 0;
        $error_message .= 'Subject can not be empty<br>';
    }

    if(empty($_POST['message']))
    {
        $valid = 0;
        $error_message .= 'Message can not be empty<br>';
    }

    if($valid == 1)
    {
        
        // sending email
        $subject = $_POST['subject'];

        $content = '
<html><body>
<b>Message from Admin:</b><br>
'.$_POST['message'].'
</body></html>
';

        try {

            $mail->setFrom($receive_email, 'Admin');
            $mail->addReplyTo($receive_email, 'Admin');
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $content;

            $statement = $pdo->prepare("SELECT * FROM tbl_subscriber WHERE subs_active=1");
            $statement->execute();
            $result = $statement->fetchAll();                           
            foreach ($result as $row)
            {
                $mail2 = clone $mail;
                $mail2->addAddress($row['subs_email']);
                $mail2->send();
            }

            $success_message = 'Email is sent successfully to all subscribers.';    
        } catch (Exception $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }

    }
}
?>

<section class="content-header">
	<div class="content-header-left">
        <h1>Send Email to Subscriber</h1>
    </div>
    <div class="content-header-right">
        <a href="subscriber.php" class="btn btn-primary btn-sm">View All</a>
    </div>
</section>


<section class="content">

    <div class="row">
        <div class="col-md-12">

            <?php if($error_message): ?>
            <div class="callout callout-danger">
            
            <p>
            <?php echo $error_message; ?>
            </p>
            </div>
            <?php endif; ?>

            <?php if($success_message): ?>
            <div class="callout callout-success">
            
            <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>

            <form class="form-horizontal" action="" method="post">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Subject </label>
                            <div class="col-sm-6">
                                <input type="text" autocomplete="off" class="form-control" name="subject">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Message </label>
                            <div class="col-sm-9">
                                <textarea class="form-control editor" name="message"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Send Email</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</section>



<?php require_once('footer.php'); ?>