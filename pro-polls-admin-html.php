<?php


function pro_polls_show_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
	echo "<h1>Will show Pro Polls settings here</h1>";

    // Render the HTML for the Settings page or include a file that does
}

function pro_polls_show_questions_page() {
	$add_new_link ="";
	$notification  ="";
	
	//Allow users to acces this ONLY if they have edit posts capability.
    if (!current_user_can('edit_posts')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
	$poll_id = intval($_GET['poll_id']);
	if(!is_numeric($poll_id))
	{
        wp_die('The provided poll id is not correct.');
    }
	
	$objPoll = new Ppolls_polls($poll_id);
	//Check if this is a valid poll Id
	if($objPoll->isValid == false)
	{
        wp_die('The provided poll id is not correct.');
    }
	
	$current_qid = false;
	if(isset($_GET['qid']) && is_numeric($_GET['qid']))
	{		
		$current_qid = intval($_GET['qid']); 
		$question_details = get_post( $current_qid );
		//var_dump($question_details);
		if(!$question_details || $question_details->post_type != "ppolls_question" )
			wp_die("not valid propolls question id");
		
		if(isset($_GET['action']) && $_GET['action'] == 'delete')
		{ 
			//Call is for deleting the questions. So check deleting nonce		
			check_admin_referer( 'pro-poll-delete-question-'.$current_qid );
			wp_delete_post( $current_qid, true );
			//set current qid as false as it has been deleted.	
			$current_qid = false; 
		}
		else
		{
			//Call is for editing the questions. So check editing nonce
			check_admin_referer( 'pro-poll-edit-question-'.$current_qid );
			$add_new_url = 'edit.php?post_type='.PRO_POLL_POST_TYPE_SLUG.'&page=pro-polls-questions&poll_id='.$poll_id;
			//$add_new_url = wp_nonce_url( $add_new_url, 'pro-poll-add-question'.$poll_id );
			$add_new_link = '<a href="'.$add_new_url.'" class="add-new-h2">Add New</a>';
		} 

	}
	
	if(isset($_POST['btnSubmitQuestion']) && $_POST['btnSubmitQuestion'] != "")
	{
		$validated = true;
		$posted_question = sanitize_text_field($_POST['txtQuestion']);
		if(empty($posted_question))
		{ 
			$validated = false;
			$notification ='<div id="message" class="error below-h2"><p>Please enter question.</p></div>';
		}
		$answers = explode("\r\n", sanitize_textarea_field($_POST['txtAnswers']));
		$answers = array_map('trim', $answers);
		$clean_answers = array();
		foreach($answers as $answer)
		{
			if($answer != "")
				$clean_answers[] = $answer;	
		}
		if(count($clean_answers)<1)
		{ 
			$validated = false;
			$notification .='<div id="message" class="error below-h2"><p>Please enter at least one option.</p></div>';
		}
 		$post_content =  serialize($clean_answers);
		$post = array(
		  'ID'             => '',
		  'menu_order'     => '',
		  'post_author'    => $user_ID, 
		  'post_content'   => $post_content,
		  'post_parent'    => $poll_id,
		  'post_status'    => 'publish',
		  'post_title'     => sanitize_text_field($_POST['txtQuestion']),
		  'post_type'      => 'ppolls_question'
		);
		if($validated)
		{
			if($_POST['btnSubmitQuestion'] =='Update Question')
			{
				if(isset($_POST['qid']) && is_numeric($_POST['qid']))
				{
					
					$post['ID'] =  intval($_POST['qid']);
					$post_id = wp_update_post( $post, $wp_error );
					if($post_id >0)
						$notification ='<div id="message" class="updated below-h2"><p>Successfully updated the question.</p></div>';
					else
						$notification = '<div id="message" class="updated below-h2"><p>Failed updating the question</p></div>'; 
				}
				else
				{
					wp_die('You are trying to edit an non-existing question.');
				}
			}
			else
			{
				check_admin_referer( 'add-question-'.$poll_id);
				$post_id = wp_insert_post( $post, $wp_error );
				if($post_id >0)
					$notification ='<div id="message" class="updated below-h2"><p>Successfully added the question.</p></div>';
				else
					$notification = '<div id="message" class="updated below-h2"><p>Failed adding the question.</p></div>'; 
			}
		}
	}
	
 
	$poll_name = $objPoll->title;//$poll_details->post_title;
	echo '<div class="wrap">
<div id="icon-users" class="icon32"><br></div><h2>

Questions for the Poll: <strong>'.$poll_name.'</strong> '.$add_new_link.'</h2>';

echo "$notification";
?>
<div class="alignleft actions">
<div class="ppolls_add_form" style="float:left; width:100%;">
<form action="edit.php?post_type=<?php echo PRO_POLL_POST_TYPE_SLUG; ?>&page=pro-polls-questions&poll_id=<?php echo $poll_id;?>" method="post">  <?php

	$submitButtonText  = "Add Question";
if($current_qid)
{
	$question = $question_details->post_title;
	$answers = unserialize($question_details->post_content);
	if(!is_array($answers))
		$answers = array();
	$formatted_answers = implode("\n", $answers);
	$submitButtonText  = "Update Question";
	?>
	<input type="hidden" name="qid" value="<?php echo $current_qid;?>" />
	<?php
	wp_nonce_field( 'edit-question-'.$current_qid );
}
else
{
	wp_nonce_field( 'add-question-'.$poll_id );	
}
?>
<div><br /><label><strong>Question:</strong></label><br />
<input type="text" size="80" name="txtQuestion" value="<?php echo $question; ?>" /></div>
<div><label><strong>Answers (One option in each line):</strong></label><br />
<textarea name="txtAnswers" rows='5' cols="100"><?php echo $formatted_answers; ?></textarea></div>
<div><input type="submit" name="btnSubmitQuestion" value="<?php echo $submitButtonText;?>" /></div>
<?php ?>

</form>
</div></div> <?php
$objQuestionsTable = new Question_List_Table($poll_id);

$objQuestionsTable->prepare_items();

$objQuestionsTable->display();

 


echo '<br class="clear">
</div>';
	
    // Render the HTML for the Settings page or include a file that does
} 

function pro_polls_show_users($poll_id)
{
	global $wpdb;
	
	$allowed_users_objects = pro_polls_get_users_with_access($poll_id);
	$allowed_users = array();
	foreach($allowed_users_objects as $objUser)
		$allowed_users[$objUser->ID]= $objUser->user_login;
	
	$query = "SELECT user_id FROM ".PRO_POLL_RESULT_TABLE." WHERE poll_id='".$poll_id."' order by submitted_time ASC";
	//echo $query;
	$users_who_voted = $wpdb->get_col($query, 0);
	 
	$good_users = array();
	$bad_users = array();
	foreach($allowed_users as $id=>$name)
	{
		if(in_array($id, $users_who_voted))
			$good_users[$id] = $name;
		else
			$bad_users[$id] = $name;
	}
	// var_dump($good_users);
	 //var_dump($bad_users);
	 print_users("Users who have voted already", $good_users);
	 print_users("Users who have NOT voted yet", $bad_users);
}
function print_users($title, $users)
{?><table class="widefat" style="width:35%; margin-right:20px; float:left; clear:none;">
<thead>
    <tr>
        <th><?php echo $title; ?></th> 
    </tr>
</thead>
<tbody><?php foreach($users as $id=>$name)
	{
		echo "<tr><td><a href='/wp-admin/user-edit.php?user_id=$id' target='_blank'>$name</a></td></tr>";
	}?></tbody>
</table> <?php
}
 
function pro_polls_show_results_page() {
	
$add_new_link ="";
	
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
	$poll_id = $_GET['poll_id'];
	if(!is_numeric($poll_id)){
        wp_die('You do not have sufficient permissions and Poll id to access this page.');
    }	 
	
	
	$poll_details = get_post( $poll_id );
	//pro_polls_dump($poll_details);
	$poll_name = $poll_details->post_title;
	echo '<div class="wrap">
<div id="icon-users" class="icon32"><br></div>
<h2>Results for the Poll: <strong>'.$poll_name.'</strong>  </h2>';

$objQuestionsTable = new PpollsResults($poll_id);

$objQuestionsTable->prepare_items();

$objQuestionsTable->display();

 


echo '<br class="clear">
</div>';
pro_polls_show_users($poll_id);

	
    // Render the HTML for the Settings page or include a file that does

}
?>